<?php
require_once '../includes/config.php';

$pageTitle = '编辑个人资料';

// 检查用户是否已登录
if (!isLoggedIn()) {
    // 记录当前页面URL，以便登录后重定向回来
    $_SESSION['redirect_after_login'] = getCurrentUrl();
    
    // 设置消息
    $_SESSION['message'] = '请先登录以编辑个人资料';
    $_SESSION['message_type'] = 'info';
    
    // 重定向到登录页面
    redirect('../login.php');
}

// 获取当前用户ID和角色
$userId = $_SESSION['user_id'];
$userRole = $_SESSION['user_role'] ?? 'student';
$db = Database::getInstance();

// 获取用户基本信息
$userInfo = [];
try {
    $stmt = $db->prepare("SELECT a.*, s.* FROM accounts a 
                         LEFT JOIN students s ON a.id = s.account_id 
                         WHERE a.id = ?");
    $stmt->execute([$userId]);
    $userInfo = $stmt->fetch();
} catch (PDOException $e) {
    error_log("用户信息查询错误: " . $e->getMessage());
}

// 处理表单提交
$profileUpdated = false;
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 获取表单数据
    $name = sanitize($_POST['name'] ?? '');
    $gender = sanitize($_POST['gender'] ?? '');
    $birthDate = sanitize($_POST['birth_date'] ?? '');
    $parentName = sanitize($_POST['parent_name'] ?? '');
    $parentPhone = sanitize($_POST['parent_phone'] ?? '');
    
    // 基本验证
    if (empty($name)) {
        $errorMessage = '姓名不能为空';
    } else {
        try {
            // 开始事务
            $db->beginTransaction();
            
            // 更新学生表信息（如果存在）
            $studentId = $userInfo['id'] ?? null;
            if ($studentId) {
                $stmt = $db->prepare("UPDATE students SET name = ?, gender = ?, birth_date = ?, 
                                     parent_name = ?, parent_phone = ? WHERE id = ?");
                $stmt->execute([$name, $gender, $birthDate, $parentName, $parentPhone, $studentId]);
            } else {
                // 如果学生信息不存在，则创建
                $stmt = $db->prepare("INSERT INTO students (account_id, name, gender, birth_date, parent_name, parent_phone) 
                                     VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$userId, $name, $gender, $birthDate, $parentName, $parentPhone]);
            }
            
            // 提交事务
            $db->commit();
            
            // 设置成功消息
            $profileUpdated = true;
            
            // 记录活动
            logActivity('更新个人资料', "用户 {$_SESSION['username']} 更新了个人资料");
            
        } catch (PDOException $e) {
            // 回滚事务
            $db->rollBack();
            
            error_log("个人资料更新错误: " . $e->getMessage());
            $errorMessage = '系统错误，请稍后重试';
        }
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
    
    .content-card {
        background-color: white;
        border-radius: var(--radius-lg);
        box-shadow: 0 5px 15px var(--shadow-color);
        padding: var(--space-md);
        margin-bottom: var(--space-lg);
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
        margin-bottom: var(--space-sm);
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
    
    .form-buttons {
        display: flex;
        justify-content: space-between;
        margin-top: var(--space-md);
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
    
    .avatar-upload {
        display: flex;
        flex-direction: column;
        align-items: center;
        margin-bottom: var(--space-md);
    }
    
    .avatar-preview {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        overflow: hidden;
        margin-bottom: var(--space-sm);
        background-color: var(--border-color);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 64px;
        color: var(--text-secondary);
    }
    
    .avatar-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .required-mark {
        color: var(--error);
        margin-left: 4px;
    }
    
    @media (max-width: 768px) {
        .form-row {
            flex-direction: column;
            gap: 0;
        }
    }
</style>
HTML;

include '../templates/header.php';
?>

<!-- 页面标题 -->
<div class="page-header">
    <div class="container">
        <h1>编辑个人资料</h1>
        <p>更新您的个人信息</p>
    </div>
</div>

<div class="container">
    <div class="content-card">
        <h2 class="content-title">个人资料</h2>
        
        <?php if ($profileUpdated): ?>
            <div class="success-message">个人资料已成功更新！</div>
        <?php endif; ?>
        
        <?php if (!empty($errorMessage)): ?>
            <div class="error-message"><?php echo $errorMessage; ?></div>
        <?php endif; ?>
        
        <form method="post" action="" enctype="multipart/form-data">
            <!-- 头像上传（暂不实现实际上传功能） -->
            <div class="avatar-upload">
                <div class="avatar-preview">
                    <?php if (isset($userInfo['avatar']) && !empty($userInfo['avatar'])): ?>
                        <img src="<?php echo $userInfo['avatar']; ?>" alt="用户头像">
                    <?php else: ?>
                        <span><?php echo mb_substr($userInfo['username'] ?? '用户', 0, 1, 'UTF-8'); ?></span>
                    <?php endif; ?>
                </div>
                <div>
                    <p>头像上传功能暂未实现</p>
                </div>
            </div>
            
            <!-- 基本信息 -->
            <div class="form-section">
                <h3>基本信息</h3>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="name" class="form-label">
                            姓名<span class="required-mark">*</span>
                        </label>
                        <input type="text" id="name" name="name" class="form-control" required 
                               value="<?php echo $userInfo['name'] ?? ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="gender" class="form-label">性别</label>
                        <select id="gender" name="gender" class="form-control form-select">
                            <option value="">请选择性别</option>
                            <option value="男" <?php echo ($userInfo['gender'] ?? '') === '男' ? 'selected' : ''; ?>>男</option>
                            <option value="女" <?php echo ($userInfo['gender'] ?? '') === '女' ? 'selected' : ''; ?>>女</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="birth_date" class="form-label">出生日期</label>
                    <input type="date" id="birth_date" name="birth_date" class="form-control"
                           value="<?php echo $userInfo['birth_date'] ?? ''; ?>">
                </div>
            </div>
            
            <!-- 家长信息（仅学生角色需要填写） -->
            <?php if ($userRole === 'student'): ?>
                <div class="form-section">
                    <h3>家长信息</h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="parent_name" class="form-label">家长姓名</label>
                            <input type="text" id="parent_name" name="parent_name" class="form-control"
                                   value="<?php echo $userInfo['parent_name'] ?? ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="parent_phone" class="form-label">家长电话</label>
                            <input type="tel" id="parent_phone" name="parent_phone" class="form-control"
                                   value="<?php echo $userInfo['parent_phone'] ?? ''; ?>">
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="form-buttons">
                <a href="profile.php" class="btn btn-secondary">取消</a>
                <button type="submit" class="btn btn-primary">保存更改</button>
            </div>
        </form>
    </div>
</div>

<?php
include '../templates/footer.php';
?> 