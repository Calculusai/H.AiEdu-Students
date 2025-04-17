<?php
// 包含必要的配置和函数
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// 检查用户是否已登录且是管理员
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    $_SESSION['error'] = "您没有访问管理中心的权限！";
    header("Location: ../auth/login.php");
    exit();
}

// 获取数据库连接
$db = Database::getInstance();

// 处理消息
$message = '';
$error = '';

// 上传图标函数
function uploadSiteIcon($file) {
    // 检查上传错误
    if ($file['error'] != UPLOAD_ERR_OK) {
        return [
            'success' => false,
            'message' => '上传过程中出现错误: ' . uploadErrorMessage($file['error'])
        ];
    }
    
    // 检查文件类型
    $allowedTypes = ['image/x-icon', 'image/png', 'image/jpeg', 'image/svg+xml'];
    $fileType = $file['type'];
    
    if (!in_array($fileType, $allowedTypes)) {
        return [
            'success' => false,
            'message' => '不支持的文件类型，请上传.ico、.png、.jpg或.svg格式的图片'
        ];
    }
    
    // 检查文件大小 (最大1MB)
    if ($file['size'] > 1048576) {
        return [
            'success' => false,
            'message' => '文件大小超过限制，最大允许1MB'
        ];
    }
    
    // 设置上传目录 - 使用绝对路径
    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/assets/images/';
    
    // 生成唯一文件名
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'favicon.' . $extension;
    $uploadPath = $uploadDir . $filename;
    
    // 先尝试测试目录是否可写
    if (!is_dir($uploadDir)) {
        error_log("目录不存在: " . $uploadDir);
        return [
            'success' => false,
            'message' => '上传目录不存在: ' . $uploadDir
        ];
    }
    
    if (!is_writable($uploadDir)) {
        error_log("目录不可写: " . $uploadDir);
        return [
            'success' => false,
            'message' => '上传目录没有写入权限: ' . $uploadDir
        ];
    }
    
    // 测试文件是否可以写入目录
    $testFile = $uploadDir . 'test_' . time() . '.txt';
    $testContent = 'test';
    if (@file_put_contents($testFile, $testContent) === false) {
        error_log("无法写入测试文件: " . $testFile);
        return [
            'success' => false,
            'message' => '无法在上传目录中创建文件，请检查服务器配置'
        ];
    } else {
        @unlink($testFile); // 删除测试文件
    }
    
    // 删除旧图标
    $oldIcons = glob($uploadDir . 'favicon.*');
    if ($oldIcons) {
        foreach ($oldIcons as $oldFile) {
            if (basename($oldFile) != $filename && is_file($oldFile)) {
                @unlink($oldFile);
            }
        }
    }
    
    // 上传文件
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        error_log("文件上传成功: " . $uploadPath);
        return [
            'success' => true,
            'path' => '/assets/images/' . $filename
        ];
    } else {
        error_log("文件上传失败: 从 " . $file['tmp_name'] . " 移动到 " . $uploadPath . " 失败");
        return [
            'success' => false,
            'message' => '文件上传失败。PHP临时文件: ' . $file['tmp_name'] . ', 目标路径: ' . $uploadPath
        ];
    }
}

// 上传错误消息
function uploadErrorMessage($code) {
    switch ($code) {
        case UPLOAD_ERR_INI_SIZE:
            return '上传的文件超过了php.ini中upload_max_filesize指令的限制';
        case UPLOAD_ERR_FORM_SIZE:
            return '上传的文件超过了HTML表单中MAX_FILE_SIZE指令的限制';
        case UPLOAD_ERR_PARTIAL:
            return '文件只有部分被上传';
        case UPLOAD_ERR_NO_FILE:
            return '没有文件被上传';
        case UPLOAD_ERR_NO_TMP_DIR:
            return '缺少临时文件夹';
        case UPLOAD_ERR_CANT_WRITE:
            return '无法写入磁盘';
        case UPLOAD_ERR_EXTENSION:
            return '文件上传被扩展停止';
        default:
            return '未知上传错误';
    }
}

// 获取当前系统配置
function getSystemSettings($db) {
    try {
        $stmt = $db->query("SELECT * FROM system_settings");
        $settings = [];
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        
        return $settings;
    } catch (PDOException $e) {
        // 如果表不存在，尝试创建表
        if ($e->getCode() == '42S02') { // MySQL的"表不存在"错误码
            createSettingsTable($db);
            return []; // 返回空设置
        }
        
        error_log("获取系统设置错误: " . $e->getMessage());
        return [];
    }
}

// 创建设置表（如果不存在）
function createSettingsTable($db) {
    try {
        $db->exec("
            CREATE TABLE IF NOT EXISTS `system_settings` (
                `id` INT NOT NULL AUTO_INCREMENT,
                `setting_key` VARCHAR(100) NOT NULL,
                `setting_value` TEXT NULL,
                `setting_group` VARCHAR(50) NOT NULL DEFAULT 'general',
                `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE INDEX `setting_key_UNIQUE` (`setting_key` ASC)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // 插入默认设置
        $defaultSettings = [
            ['site_name', SITE_NAME, 'general'],
            ['site_description', SITE_DESCRIPTION, 'general'],
            ['site_url', SITE_URL, 'general'],
            ['site_icon', '', 'general'],
            ['admin_email', 'admin@example.com', 'contact'],
            ['contact_phone', '123-456-7890', 'contact'],
            ['smtp_host', '', 'mail'],
            ['smtp_port', '587', 'mail'],
            ['smtp_username', '', 'mail'],
            ['smtp_password', '', 'mail'],
            ['smtp_encryption', 'tls', 'mail'],
            ['mail_from_name', SITE_NAME, 'mail'],
            ['allow_registration', '1', 'system'],
            ['items_per_page', ITEMS_PER_PAGE, 'system'],
            ['system_version', SYSTEM_VERSION, 'system']
        ];
        
        $stmt = $db->prepare("INSERT INTO system_settings (setting_key, setting_value, setting_group) VALUES (?, ?, ?)");
        
        foreach ($defaultSettings as $setting) {
            $stmt->execute($setting);
        }
        
        return true;
    } catch (PDOException $e) {
        error_log("创建设置表错误: " . $e->getMessage());
        return false;
    }
}

// 更新系统设置
function updateSettings($db, $settings, $group) {
    try {
        $stmt = $db->prepare("INSERT INTO system_settings (setting_key, setting_value, setting_group) 
                              VALUES (?, ?, ?) 
                              ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        
        foreach ($settings as $key => $value) {
            $stmt->execute([$key, $value, $group]);
        }
        
        return true;
    } catch (PDOException $e) {
        error_log("更新系统设置错误: " . $e->getMessage());
        return false;
    }
}

// 获取当前设置
$currentSettings = getSystemSettings($db);

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $settingGroup = '';
    $successMessage = '';
    
    // 基本设置
    if (isset($_POST['update_general'])) {
        $settingGroup = 'general';
        $generalSettings = [
            'site_name' => trim($_POST['site_name']),
            'site_description' => trim($_POST['site_description']),
            'site_url' => trim($_POST['site_url'])
        ];
        
        // 处理图标上传
        if (isset($_FILES['site_icon']) && $_FILES['site_icon']['size'] > 0) {
            $uploadResult = uploadSiteIcon($_FILES['site_icon']);
            
            if ($uploadResult['success']) {
                $generalSettings['site_icon'] = $uploadResult['path'];
            } else {
                $error = $uploadResult['message'];
            }
        }
        
        if (empty($generalSettings['site_name'])) {
            $error = "网站名称不能为空！";
        } elseif (empty($error)) {
            if (updateSettings($db, $generalSettings, $settingGroup)) {
                $message = "基本设置已成功更新！";
                $currentSettings = array_merge($currentSettings, $generalSettings);
            } else {
                $error = "更新基本设置时出错！";
            }
        }
    }
    
    // 联系信息设置
    if (isset($_POST['update_contact'])) {
        $settingGroup = 'contact';
        $contactSettings = [
            'admin_email' => trim($_POST['admin_email']),
            'contact_phone' => trim($_POST['contact_phone'])
        ];
        
        if (empty($contactSettings['admin_email']) || !filter_var($contactSettings['admin_email'], FILTER_VALIDATE_EMAIL)) {
            $error = "请输入有效的管理员邮箱地址！";
        } else {
            if (updateSettings($db, $contactSettings, $settingGroup)) {
                $message = "联系信息已成功更新！";
                $currentSettings = array_merge($currentSettings, $contactSettings);
            } else {
                $error = "更新联系信息时出错！";
            }
        }
    }
    
    // 邮件设置
    if (isset($_POST['update_mail'])) {
        $settingGroup = 'mail';
        $mailSettings = [
            'smtp_host' => trim($_POST['smtp_host']),
            'smtp_port' => trim($_POST['smtp_port']),
            'smtp_username' => trim($_POST['smtp_username']),
            'smtp_password' => trim($_POST['smtp_password']),
            'smtp_encryption' => $_POST['smtp_encryption'],
            'mail_from_name' => trim($_POST['mail_from_name'])
        ];
        
        if (updateSettings($db, $mailSettings, $settingGroup)) {
            $message = "邮件设置已成功更新！";
            $currentSettings = array_merge($currentSettings, $mailSettings);
        } else {
            $error = "更新邮件设置时出错！";
        }
    }
    
    // 系统设置
    if (isset($_POST['update_system'])) {
        $settingGroup = 'system';
        $systemSettings = [
            'allow_registration' => isset($_POST['allow_registration']) ? '1' : '0',
            'items_per_page' => trim($_POST['items_per_page'])
        ];
        
        if (!is_numeric($systemSettings['items_per_page']) || $systemSettings['items_per_page'] < 1) {
            $error = "每页显示项目数必须是大于0的数字！";
        } else {
            if (updateSettings($db, $systemSettings, $settingGroup)) {
                $message = "系统设置已成功更新！";
                $currentSettings = array_merge($currentSettings, $systemSettings);
            } else {
                $error = "更新系统设置时出错！";
            }
        }
    }
}

// 页面标题
$page_title = "系统设置";
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - 儿童编程教育平台</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin-style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        .settings-tabs {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .settings-tab {
            padding: 10px 20px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
            border-bottom: 2px solid transparent;
        }
        
        .settings-tab:hover {
            color: var(--primary);
        }
        
        .settings-tab.active {
            color: var(--primary);
            border-bottom-color: var(--primary);
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .settings-form .form-group {
            margin-bottom: 20px;
        }
        
        .settings-form label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        .settings-form input[type="text"],
        .settings-form input[type="email"],
        .settings-form input[type="password"],
        .settings-form input[type="number"],
        .settings-form select,
        .settings-form textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            font-size: 14px;
        }
        
        .settings-description {
            font-size: 13px;
            color: var(--text-light);
            margin-top: 5px;
        }
        
        .settings-group {
            margin-bottom: 30px;
        }
        
        .settings-group h3 {
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .system-info {
            background-color: rgba(62, 198, 255, 0.1);
            padding: 15px;
            border-radius: var(--radius-md);
            margin-bottom: 20px;
        }
        
        .system-info p {
            margin: 8px 0;
        }
        
        .toggle-control {
            display: block;
            position: relative;
            padding-left: 50px;
            margin-bottom: 12px;
            cursor: pointer;
            font-size: 15px;
            user-select: none;
        }

        .toggle-control input {
            position: absolute;
            opacity: 0;
            cursor: pointer;
        }

        .toggle-control input:checked ~ .control {
            background-color: var(--success);
        }

        .toggle-control .control {
            position: absolute;
            top: 0;
            left: 0;
            height: 24px;
            width: 48px;
            border-radius: 12px;
            background-color: var(--border-color);
            transition: background-color 0.3s ease;
        }

        .toggle-control .control:after {
            content: "";
            position: absolute;
            left: 4px;
            top: 4px;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: white;
            transition: left 0.3s ease;
        }

        .toggle-control input:checked ~ .control:after {
            left: 28px;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include 'admin_sidebar.php'; ?>
        
        <div class="admin-content">
            <div class="admin-content-header">
                <h1>系统设置</h1>
            </div>
            
            <?php if (!empty($message)): ?>
                <div class="message success"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <?php if (!empty($error)): ?>
                <div class="message error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <!-- 设置选项卡 -->
            <div class="settings-tabs">
                <div class="settings-tab active" data-tab="general"><i class="fas fa-cog"></i> 基本设置</div>
                <div class="settings-tab" data-tab="contact"><i class="fas fa-address-book"></i> 联系信息</div>
                <div class="settings-tab" data-tab="mail"><i class="fas fa-envelope"></i> 邮件设置</div>
                <div class="settings-tab" data-tab="system"><i class="fas fa-wrench"></i> 系统设置</div>
            </div>
            
            <div class="admin-card">
                <!-- 基本设置 -->
                <div class="tab-content active" id="general">
                    <form method="post" action="settings.php" class="settings-form" enctype="multipart/form-data">
                        <div class="settings-group">
                            <h3>网站信息</h3>
                            
                            <div class="form-group">
                                <label for="site_name">网站名称</label>
                                <input type="text" id="site_name" name="site_name" value="<?php echo htmlspecialchars($currentSettings['site_name'] ?? SITE_NAME); ?>" required>
                                <div class="settings-description">网站名称将显示在浏览器标题栏和各种页面</div>
                            </div>
                            
                            <div class="form-group">
                                <label for="site_description">网站描述</label>
                                <textarea id="site_description" name="site_description" rows="3"><?php echo htmlspecialchars($currentSettings['site_description'] ?? SITE_DESCRIPTION); ?></textarea>
                                <div class="settings-description">简短描述您的网站，用于SEO和页面描述</div>
                            </div>
                            
                            <div class="form-group">
                                <label for="site_url">网站地址</label>
                                <input type="text" id="site_url" name="site_url" value="<?php echo htmlspecialchars($currentSettings['site_url'] ?? SITE_URL); ?>">
                                <div class="settings-description">网站的完整URL地址，包括http://或https://</div>
                            </div>
                            
                            <div class="form-group">
                                <label for="site_icon">网站图标</label>
                                <?php if (!empty($currentSettings['site_icon'])): ?>
                                    <div class="current-icon" style="margin-bottom: 10px;">
                                        <img src="<?php echo htmlspecialchars($currentSettings['site_icon']); ?>" alt="当前网站图标" style="max-width: 64px; max-height: 64px; border: 1px solid #ddd; padding: 5px; border-radius: 4px;">
                                        <div class="settings-description">当前图标</div>
                                    </div>
                                <?php endif; ?>
                                <input type="file" id="site_icon" name="site_icon" accept="image/x-icon,image/png,image/jpeg,image/svg+xml">
                                <div class="settings-description">上传网站图标（favicon），推荐尺寸32x32或64x64像素，支持.ico、.png、.jpg或.svg格式</div>
                            </div>
                        </div>
                        
                        <div class="form-buttons">
                            <button type="submit" name="update_general" class="admin-btn admin-btn-primary">保存设置</button>
                        </div>
                    </form>
                </div>
                
                <!-- 联系信息 -->
                <div class="tab-content" id="contact">
                    <form method="post" action="settings.php" class="settings-form">
                        <div class="settings-group">
                            <h3>联系方式</h3>
                            
                            <div class="form-group">
                                <label for="admin_email">管理员邮箱</label>
                                <input type="email" id="admin_email" name="admin_email" value="<?php echo htmlspecialchars($currentSettings['admin_email'] ?? ''); ?>" required>
                                <div class="settings-description">用于接收系统通知和联系表单消息</div>
                            </div>
                            
                            <div class="form-group">
                                <label for="contact_phone">联系电话</label>
                                <input type="text" id="contact_phone" name="contact_phone" value="<?php echo htmlspecialchars($currentSettings['contact_phone'] ?? ''); ?>">
                                <div class="settings-description">可选的联系电话</div>
                            </div>
                        </div>
                        
                        <div class="form-buttons">
                            <button type="submit" name="update_contact" class="admin-btn admin-btn-primary">保存设置</button>
                        </div>
                    </form>
                </div>
                
                <!-- 邮件设置 -->
                <div class="tab-content" id="mail">
                    <form method="post" action="settings.php" class="settings-form">
                        <div class="settings-group">
                            <h3>SMTP配置</h3>
                            
                            <div class="form-group">
                                <label for="smtp_host">SMTP服务器</label>
                                <input type="text" id="smtp_host" name="smtp_host" value="<?php echo htmlspecialchars($currentSettings['smtp_host'] ?? ''); ?>">
                                <div class="settings-description">例如: smtp.qq.com、smtp.163.com</div>
                            </div>
                            
                            <div class="form-group">
                                <label for="smtp_port">SMTP端口</label>
                                <input type="text" id="smtp_port" name="smtp_port" value="<?php echo htmlspecialchars($currentSettings['smtp_port'] ?? '587'); ?>">
                                <div class="settings-description">常用端口: 25, 465, 587</div>
                            </div>
                            
                            <div class="form-group">
                                <label for="smtp_username">SMTP用户名</label>
                                <input type="text" id="smtp_username" name="smtp_username" value="<?php echo htmlspecialchars($currentSettings['smtp_username'] ?? ''); ?>">
                                <div class="settings-description">通常是您的完整邮箱地址</div>
                            </div>
                            
                            <div class="form-group">
                                <label for="smtp_password">SMTP密码</label>
                                <input type="password" id="smtp_password" name="smtp_password" value="<?php echo htmlspecialchars($currentSettings['smtp_password'] ?? ''); ?>">
                                <div class="settings-description">如果不修改，请留空</div>
                            </div>
                            
                            <div class="form-group">
                                <label for="smtp_encryption">加密方式</label>
                                <select id="smtp_encryption" name="smtp_encryption">
                                    <option value="tls" <?php echo (($currentSettings['smtp_encryption'] ?? 'tls') == 'tls') ? 'selected' : ''; ?>>TLS</option>
                                    <option value="ssl" <?php echo (($currentSettings['smtp_encryption'] ?? 'tls') == 'ssl') ? 'selected' : ''; ?>>SSL</option>
                                    <option value="none" <?php echo (($currentSettings['smtp_encryption'] ?? 'tls') == 'none') ? 'selected' : ''; ?>>无加密</option>
                                </select>
                                <div class="settings-description">大多数服务器使用TLS加密</div>
                            </div>
                            
                            <div class="form-group">
                                <label for="mail_from_name">发件人名称</label>
                                <input type="text" id="mail_from_name" name="mail_from_name" value="<?php echo htmlspecialchars($currentSettings['mail_from_name'] ?? ($currentSettings['site_name'] ?? SITE_NAME)); ?>">
                                <div class="settings-description">发送邮件时显示的发件人名称</div>
                            </div>
                        </div>
                        
                        <div class="form-buttons">
                            <button type="submit" name="update_mail" class="admin-btn admin-btn-primary">保存设置</button>
                        </div>
                    </form>
                </div>
                
                <!-- 系统设置 -->
                <div class="tab-content" id="system">
                    <div class="system-info">
                        <p><strong>系统版本:</strong> <?php echo htmlspecialchars($currentSettings['system_version'] ?? SYSTEM_VERSION); ?></p>
                        <p><strong>PHP版本:</strong> <?php echo phpversion(); ?></p>
                        <p><strong>MySQL版本:</strong> <?php echo $db->query('select version()')->fetchColumn(); ?></p>
                        <p><strong>服务器信息:</strong> <?php echo htmlspecialchars($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'); ?></p>
                    </div>
                    
                    <form method="post" action="settings.php" class="settings-form">
                        <div class="settings-group">
                            <h3>系统配置</h3>
                            
                            <div class="form-group">
                                <label class="toggle-control">
                                    允许用户注册
                                    <input type="checkbox" name="allow_registration" <?php echo (($currentSettings['allow_registration'] ?? '1') == '1') ? 'checked' : ''; ?>>
                                    <div class="control"></div>
                                </label>
                                <div class="settings-description">启用/禁用新用户注册功能</div>
                            </div>
                            
                            <div class="form-group">
                                <label for="items_per_page">每页显示项目数</label>
                                <input type="number" id="items_per_page" name="items_per_page" value="<?php echo htmlspecialchars($currentSettings['items_per_page'] ?? ITEMS_PER_PAGE); ?>" min="1" max="100">
                                <div class="settings-description">列表页面每页显示的最大项目数</div>
                            </div>
                        </div>
                        
                        <div class="form-buttons">
                            <button type="submit" name="update_system" class="admin-btn admin-btn-primary">保存设置</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // 选项卡切换
        const tabs = document.querySelectorAll('.settings-tab');
        const tabContents = document.querySelectorAll('.tab-content');
        
        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                const tabId = tab.getAttribute('data-tab');
                
                // 移除所有活动状态
                tabs.forEach(t => t.classList.remove('active'));
                tabContents.forEach(tc => tc.classList.remove('active'));
                
                // 设置当前选项卡为活动状态
                tab.classList.add('active');
                document.getElementById(tabId).classList.add('active');
            });
        });
        
        // 从URL解析选项卡参数
        const urlParams = new URLSearchParams(window.location.search);
        const tabParam = urlParams.get('tab');
        
        if (tabParam) {
            const tabElement = document.querySelector(`.settings-tab[data-tab="${tabParam}"]`);
            if (tabElement) {
                tabElement.click();
            }
        }
    });
    </script>
</body>
</html> 