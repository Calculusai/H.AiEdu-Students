<?php
require_once '../includes/config.php';

$pageTitle = '用户注册';

// 处理注册表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $phone = sanitize($_POST['phone'] ?? '');
    $role = 'student'; // 默认角色为学生
    $error = '';
    
    // 验证输入
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = '所有带星号的字段都是必填的';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = '请输入有效的电子邮件地址';
    } elseif (strlen($password) < 6) {
        $error = '密码必须至少包含6个字符';
    } elseif ($password !== $confirm_password) {
        $error = '两次输入的密码不匹配';
    } else {
        // 连接数据库
        $db = Database::getInstance();
        
        // 检查用户名是否已存在
        $stmt = $db->prepare("SELECT COUNT(*) FROM accounts WHERE username = ?");
        $stmt->execute([$username]);
        $count = $stmt->fetchColumn();
        
        if ($count > 0) {
            $error = '用户名已被使用，请选择其他用户名';
        } else {
            // 检查邮箱是否已存在
            $stmt = $db->prepare("SELECT COUNT(*) FROM accounts WHERE email = ?");
            $stmt->execute([$email]);
            $count = $stmt->fetchColumn();
            
            if ($count > 0) {
                $error = '该邮箱已注册，请使用其他邮箱或尝试找回密码';
            } else {
                // 创建新用户
                $hashed_password = hashPassword($password);
                
                $stmt = $db->prepare("INSERT INTO accounts (username, password, email, phone, role) VALUES (?, ?, ?, ?, ?)");
                $result = $stmt->execute([$username, $hashed_password, $email, $phone, $role]);
                
                if ($result) {
                    // 获取新创建的用户ID
                    $user_id = $db->lastInsertId();
                    
                    // 自动登录
                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['username'] = $username;
                    $_SESSION['user_role'] = $role;
                    
                    // 记录活动
                    logActivity('用户注册', "新用户 {$username} 注册成功");
                    
                    // 设置成功消息
                    $_SESSION['message'] = '注册成功，欢迎加入我们！';
                    $_SESSION['message_type'] = 'success';
                    
                    // 重定向到首页
                    redirect('../index.php');
                } else {
                    $error = '注册失败，请稍后重试';
                }
            }
        }
    }
}

// 页面特定样式
$extraStyles = <<<HTML
<style>
    .auth-container {
        max-width: 600px;
        margin: 0 auto;
        padding: var(--space-md);
        background-color: white;
        border-radius: var(--radius-xl);
        box-shadow: 0 10px 30px var(--shadow-color);
    }
    
    .auth-header {
        text-align: center;
        margin-bottom: var(--space-md);
    }
    
    .auth-title {
        color: var(--primary);
        margin-bottom: var(--space-xs);
    }
    
    .auth-form {
        margin-bottom: var(--space-md);
    }
    
    .form-row {
        display: flex;
        gap: var(--space-md);
        margin-bottom: var(--space-md);
    }
    
    .form-row .form-group {
        flex: 1;
        margin-bottom: 0;
    }
    
    .auth-links {
        text-align: center;
        padding-top: var(--space-sm);
        border-top: 1px solid var(--border-color);
    }
    
    .error-message {
        background-color: rgba(255, 59, 48, 0.1);
        color: var(--error);
        padding: var(--space-sm);
        border-radius: var(--radius-md);
        margin-bottom: var(--space-md);
        border-left: 4px solid var(--error);
    }
    
    .required-mark {
        color: var(--error);
        margin-left: 4px;
    }
    
    .password-hint {
        font-size: var(--font-small);
        color: var(--text-secondary);
        margin-top: 5px;
    }
    
    @media (max-width: 768px) {
        .form-row {
            flex-direction: column;
            gap: 0;
        }
    }
</style>
HTML;

include TEMPLATES_PATH . '/header.php';
?>


<div class="auth-container">
    <div class="auth-header">
        <h2 class="auth-title">注册新账号</h2>
        <p>请填写以下信息完成注册</p>
    </div>
    
    <?php if (isset($error) && !empty($error)): ?>
        <div class="error-message">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>
    
    <form class="auth-form" method="post" action="">
        <div class="form-group">
            <label for="username" class="form-label">
                用户名<span class="required-mark">*</span>
            </label>
            <input type="text" id="username" name="username" class="form-control" required 
                   value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
        </div>
        
        <div class="form-group">
            <label for="email" class="form-label">
                电子邮箱<span class="required-mark">*</span>
            </label>
            <input type="email" id="email" name="email" class="form-control" required 
                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="password" class="form-label">
                    密码<span class="required-mark">*</span>
                </label>
                <input type="password" id="password" name="password" class="form-control" required>
                <div class="password-hint">密码至少需要6个字符</div>
            </div>
            
            <div class="form-group">
                <label for="confirm_password" class="form-label">
                    确认密码<span class="required-mark">*</span>
                </label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
            </div>
        </div>
        
        <div class="form-group">
            <label for="phone" class="form-label">手机号码</label>
            <input type="tel" id="phone" name="phone" class="form-control" 
                   value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
        </div>
        
        <div class="form-group" style="margin-bottom: var(--space-sm);">
            <label style="display: flex; align-items: start;">
                <input type="checkbox" name="agree" required style="margin-right: 8px; margin-top: 5px;">
                <span>我已阅读并同意<a href="#">服务条款</a>和<a href="#">隐私政策</a></span>
            </label>
        </div>
        
        <button type="submit" class="btn btn-primary" style="width: 100%;">注册</button>
    </form>
    
    <div class="auth-links">
        <p>已有账号？<a href="login.php">立即登录</a></p>
    </div>
</div>

<?php
include TEMPLATES_PATH . '/footer.php';
?> 