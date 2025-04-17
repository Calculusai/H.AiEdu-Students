<?php
require_once '../includes/config.php';

$pageTitle = '账号设置';

// 检查用户是否已登录
if (!isLoggedIn()) {
    // 记录当前页面URL，以便登录后重定向回来
    $_SESSION['redirect_after_login'] = getCurrentUrl();
    
    // 设置消息
    $_SESSION['message'] = '请先登录以访问账号设置';
    $_SESSION['message_type'] = 'info';
    
    // 重定向到登录页面
    redirect('../login.php');
}

// 获取当前用户信息
$userId = $_SESSION['user_id'];
$db = Database::getInstance();
$userInfo = [];

try {
    $stmt = $db->prepare("SELECT * FROM accounts WHERE id = ?");
    $stmt->execute([$userId]);
    $userInfo = $stmt->fetch();
} catch (PDOException $e) {
    error_log("用户信息查询错误: " . $e->getMessage());
}

// 处理密码修改
$passwordChanged = false;
$passwordError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // 验证当前密码
    if (empty($currentPassword) || !verifyPassword($currentPassword, $userInfo['password'])) {
        $passwordError = '当前密码不正确';
    } elseif (empty($newPassword) || strlen($newPassword) < 6) {
        $passwordError = '新密码必须至少包含6个字符';
    } elseif ($newPassword !== $confirmPassword) {
        $passwordError = '两次输入的新密码不匹配';
    } else {
        // 更新密码
        $hashedPassword = hashPassword($newPassword);
        
        try {
            $stmt = $db->prepare("UPDATE accounts SET password = ? WHERE id = ?");
            $result = $stmt->execute([$hashedPassword, $userId]);
            
            if ($result) {
                $passwordChanged = true;
                
                // 记录活动
                logActivity('修改密码', "用户 {$_SESSION['username']} 修改了密码");
            } else {
                $passwordError = '密码更新失败，请重试';
            }
        } catch (PDOException $e) {
            error_log("密码更新错误: " . $e->getMessage());
            $passwordError = '系统错误，请稍后重试';
        }
    }
}

// 处理邮箱和手机号更新
$profileUpdated = false;
$profileError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $profileError = '请输入有效的电子邮件地址';
    } else {
        // 更新个人信息
        try {
            $stmt = $db->prepare("UPDATE accounts SET email = ?, phone = ? WHERE id = ?");
            $result = $stmt->execute([$email, $phone, $userId]);
            
            if ($result) {
                $profileUpdated = true;
                
                // 更新用户信息变量，以便在页面上显示更新后的信息
                $userInfo['email'] = $email;
                $userInfo['phone'] = $phone;
                
                // 记录活动
                logActivity('更新联系信息', "用户 {$_SESSION['username']} 更新了联系信息");
            } else {
                $profileError = '信息更新失败，请重试';
            }
        } catch (PDOException $e) {
            error_log("个人信息更新错误: " . $e->getMessage());
            $profileError = '系统错误，请稍后重试';
        }
    }
}

// 处理通知设置更新
$notificationsUpdated = false;
$notificationsError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_notifications'])) {
    $emailNotifications = isset($_POST['email_notifications']) ? 1 : 0;
    $smsNotifications = isset($_POST['sms_notifications']) ? 1 : 0;
    
    try {
        $stmt = $db->prepare("UPDATE accounts SET email_notifications = ?, sms_notifications = ? WHERE id = ?");
        $result = $stmt->execute([$emailNotifications, $smsNotifications, $userId]);
        
        if ($result) {
            $notificationsUpdated = true;
            
            // 更新用户信息变量
            $userInfo['email_notifications'] = $emailNotifications;
            $userInfo['sms_notifications'] = $smsNotifications;
            
            // 记录活动
            logActivity('更新通知设置', "用户 {$_SESSION['username']} 更新了通知设置");
        } else {
            $notificationsError = '设置更新失败，请重试';
        }
    } catch (PDOException $e) {
        error_log("通知设置更新错误: " . $e->getMessage());
        $notificationsError = '系统错误，请稍后重试';
    }
}

// 页面特定样式
$extraStyles = <<<HTML
<style>
    .page-header {
        background: linear-gradient(135deg, var(--primary), var(--purple));
        color: white;
        padding: var(--space-md) 0;
        margin-bottom: var(--space-lg);
        border-radius: var(--radius-xl);
        text-align: center;
    }
    
    .settings-container {
        display: grid;
        grid-template-columns: 250px 1fr;
        gap: var(--space-md);
        margin-bottom: var(--space-lg);
    }
    
    .settings-sidebar {
        background-color: white;
        border-radius: var(--radius-lg);
        box-shadow: 0 5px 15px var(--shadow-color);
        padding: var(--space-md);
    }
    
    .settings-main {
        display: flex;
        flex-direction: column;
        gap: var(--space-md);
    }
    
    .settings-menu {
        list-style: none;
    }
    
    .settings-menu li {
        margin-bottom: var(--space-xs);
    }
    
    .settings-menu a {
        display: block;
        padding: var(--space-sm);
        color: var(--text-primary);
        text-decoration: none;
        border-radius: var(--radius-md);
        transition: all 0.3s ease;
    }
    
    .settings-menu a:hover, .settings-menu a.active {
        background-color: rgba(62, 198, 255, 0.1);
        color: var(--primary);
    }
    
    .content-card {
        background-color: white;
        border-radius: var(--radius-lg);
        box-shadow: 0 5px 15px var(--shadow-color);
        padding: var(--space-md);
    }
    
    .content-title {
        color: var(--primary);
        margin-bottom: var(--space-sm);
        padding-bottom: var(--space-xs);
        border-bottom: 1px solid var(--border-color);
    }
    
    .form-section {
        margin-bottom: var(--space-md);
    }
    
    .form-row {
        display: flex;
        gap: var(--space-md);
        margin-bottom: var(--space-sm);
    }
    
    .form-group {
        flex: 1;
    }
    
    .form-label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: var(--text-primary);
    }
    
    .form-hint {
        font-size: var(--font-small);
        color: var(--text-secondary);
        margin-top: 4px;
    }
    
    .notification-option {
        display: flex;
        align-items: center;
        padding: var(--space-sm);
        border-bottom: 1px solid var(--border-color);
    }
    
    .notification-option:last-child {
        border-bottom: none;
    }
    
    .notification-info {
        flex: 1;
        margin-left: var(--space-sm);
    }
    
    .notification-title {
        font-weight: 600;
        color: var(--text-primary);
    }
    
    .notification-desc {
        font-size: var(--font-small);
        color: var(--text-secondary);
    }
    
    .success-message {
        background-color: rgba(0, 224, 158, 0.1);
        color: var(--green);
        padding: var(--space-sm);
        border-radius: var(--radius-md);
        margin-bottom: var(--space-md);
        border-left: 4px solid var(--green);
    }
    
    .error-message {
        background-color: rgba(255, 59, 48, 0.1);
        color: var(--error);
        padding: var(--space-sm);
        border-radius: var(--radius-md);
        margin-bottom: var(--space-md);
        border-left: 4px solid var(--error);
    }
    
    .danger-zone {
        background-color: rgba(255, 59, 48, 0.05);
        border-radius: var(--radius-lg);
        padding: var(--space-md);
        margin-top: var(--space-md);
    }
    
    .danger-zone-title {
        color: var(--error);
        margin-bottom: var(--space-sm);
    }
    
    .btn-danger {
        background-color: var(--error);
        color: white;
    }
    
    .btn-danger:hover {
        background-color: #d92b2b;
    }
    
    @media (max-width: 992px) {
        .settings-container {
            grid-template-columns: 1fr;
        }
        
        .form-row {
            flex-direction: column;
            gap: var(--space-sm);
        }
    }
</style>
HTML;

include '../templates/header.php';
?>

<!-- 页面标题 -->
<div class="page-header">
    <div class="container">
        <h1>账号设置</h1>
        <p>管理您的账号安全和个人偏好设置</p>
    </div>
</div>

<div class="settings-container">
    <!-- 侧边栏 -->
    <div class="settings-sidebar">
        <ul class="settings-menu">
            <li><a href="#password" class="active">密码设置</a></li>
            <li><a href="#contact">联系方式</a></li>
            <li><a href="#notifications">通知设置</a></li>
            <li><a href="#danger-zone">危险操作</a></li>
            <li><a href="profile.php">返回个人中心</a></li>
        </ul>
    </div>
    
    <!-- 主要内容 -->
    <div class="settings-main">
        <!-- 密码设置 -->
        <div class="content-card" id="password">
            <h3 class="content-title">密码设置</h3>
            
            <?php if ($passwordChanged): ?>
                <div class="success-message">密码已成功更新！</div>
            <?php endif; ?>
            
            <?php if (!empty($passwordError)): ?>
                <div class="error-message"><?php echo $passwordError; ?></div>
            <?php endif; ?>
            
            <form method="post" action="">
                <div class="form-section">
                    <div class="form-group">
                        <label for="current_password" class="form-label">当前密码</label>
                        <input type="password" id="current_password" name="current_password" class="form-control" required>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="new_password" class="form-label">新密码</label>
                            <input type="password" id="new_password" name="new_password" class="form-control" required>
                            <div class="form-hint">密码至少需要6个字符</div>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password" class="form-label">确认新密码</label>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                        </div>
                    </div>
                </div>
                
                <button type="submit" name="change_password" class="btn btn-primary">更新密码</button>
            </form>
        </div>
        
        <!-- 联系方式 -->
        <div class="content-card" id="contact">
            <h3 class="content-title">联系方式</h3>
            
            <?php if ($profileUpdated): ?>
                <div class="success-message">联系信息已成功更新！</div>
            <?php endif; ?>
            
            <?php if (!empty($profileError)): ?>
                <div class="error-message"><?php echo $profileError; ?></div>
            <?php endif; ?>
            
            <form method="post" action="">
                <div class="form-section">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="email" class="form-label">电子邮箱</label>
                            <input type="email" id="email" name="email" class="form-control" value="<?php echo $userInfo['email'] ?? ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="phone" class="form-label">手机号码</label>
                            <input type="tel" id="phone" name="phone" class="form-control" value="<?php echo $userInfo['phone'] ?? ''; ?>">
                        </div>
                    </div>
                </div>
                
                <button type="submit" name="update_profile" class="btn btn-primary">更新联系方式</button>
            </form>
        </div>
        
        <!-- 通知设置 -->
        <div class="content-card" id="notifications">
            <h3 class="content-title">通知设置</h3>
            
            <?php if ($notificationsUpdated): ?>
                <div class="success-message">通知设置已成功更新！</div>
            <?php endif; ?>
            
            <?php if (!empty($notificationsError)): ?>
                <div class="error-message"><?php echo $notificationsError; ?></div>
            <?php endif; ?>
            
            <form method="post" action="">
                <div class="form-section">
                    <div class="notification-option">
                        <input type="checkbox" id="email_notifications" name="email_notifications" 
                               <?php echo (isset($userInfo['email_notifications']) && $userInfo['email_notifications'] == 1) ? 'checked' : ''; ?>>
                        <div class="notification-info">
                            <div class="notification-title">电子邮件通知</div>
                            <div class="notification-desc">接收有关考级、比赛和学习资源更新的电子邮件通知</div>
                        </div>
                    </div>
                    
                    <div class="notification-option">
                        <input type="checkbox" id="sms_notifications" name="sms_notifications"
                               <?php echo (isset($userInfo['sms_notifications']) && $userInfo['sms_notifications'] == 1) ? 'checked' : ''; ?>>
                        <div class="notification-info">
                            <div class="notification-title">短信通知</div>
                            <div class="notification-desc">接收有关考级和重要事件的短信提醒</div>
                        </div>
                    </div>
                </div>
                
                <button type="submit" name="update_notifications" class="btn btn-primary">保存设置</button>
            </form>
        </div>
        
        <!-- 危险操作 -->
        <div class="content-card" id="danger-zone">
            <h3 class="content-title">危险操作</h3>
            
            <div class="danger-zone">
                <h4 class="danger-zone-title">注销账号</h4>
                <p>注销账号后，您的所有个人数据将被永久删除，且无法恢复。</p>
                <button class="btn btn-danger" onclick="confirmDeactivate()">注销我的账号</button>
            </div>
        </div>
    </div>
</div>

<?php
// 页面特定脚本
$extraScripts = <<<HTML
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // 侧边菜单滚动定位
        const menuLinks = document.querySelectorAll('.settings-menu a');
        
        menuLinks.forEach(function(link) {
            link.addEventListener('click', function(e) {
                // 如果是链接到其他页面，不处理
                if (this.getAttribute('href').indexOf('.php') > -1) {
                    return;
                }
                
                e.preventDefault();
                
                // 移除所有活动状态
                menuLinks.forEach(a => a.classList.remove('active'));
                
                // 添加当前活动状态
                this.classList.add('active');
                
                // 滚动到对应区域
                const targetId = this.getAttribute('href').substring(1);
                const targetElement = document.getElementById(targetId);
                
                if (targetElement) {
                    window.scrollTo({
                        top: targetElement.offsetTop - 20,
                        behavior: 'smooth'
                    });
                }
            });
        });
        
        // 注销账号确认
        window.confirmDeactivate = function() {
            if (confirm('您确定要注销账号吗？此操作不可逆，所有数据将被永久删除。')) {
                alert('账号注销功能暂未实现。请联系管理员进行账号注销操作。');
            }
        };
    });
</script>
HTML;

include '../templates/footer.php';
?> 