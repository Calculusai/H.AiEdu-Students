<?php
require_once '../includes/config.php';

$pageTitle = '用户登录';

// 处理登录表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $error = '';
    
    if (empty($username) || empty($password)) {
        $error = '用户名和密码不能为空';
    } else {
        // 连接数据库
        $db = Database::getInstance();
        
        // 查询用户
        $stmt = $db->prepare("SELECT * FROM accounts WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        // 验证密码
        if ($user && verifyPassword($password, $user['password'])) {
            // 登录成功，设置会话
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_role'] = $user['role'];
            
            // 记录登录活动
            logActivity('用户登录', "用户 {$user['username']} 登录系统");
            
            // 设置成功消息
            $_SESSION['message'] = '登录成功，欢迎回来！';
            $_SESSION['message_type'] = 'success';
            
            // 重定向到首页或之前的页面
            $redirect = $_SESSION['redirect_after_login'] ?? '../index.php';
            unset($_SESSION['redirect_after_login']);
            
            redirect($redirect);
        } else {
            $error = '用户名或密码不正确';
        }
    }
}

// 页面特定样式
$extraStyles = <<<HTML
<style>
    .auth-container {
        max-width: 500px;
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
</style>
HTML;

include TEMPLATES_PATH . '/header.php';
?>


<div class="auth-container">
    <div class="auth-header">
        <h2 class="auth-title">欢迎回来</h2>
        <p>请输入您的账号信息登录系统</p>
    </div>
    
    <?php if (isset($error) && !empty($error)): ?>
        <div class="error-message">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>
    
    <form class="auth-form" method="post" action="">
        <div class="form-group">
            <label for="username" class="form-label">用户名</label>
            <input type="text" id="username" name="username" class="form-control" required value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
        </div>
        
        <div class="form-group">
            <label for="password" class="form-label">密码</label>
            <input type="password" id="password" name="password" class="form-control" required>
        </div>
        
        <div class="form-group" style="margin-bottom: var(--space-sm);">
            <label style="display: flex; align-items: center;">
                <input type="checkbox" name="remember" style="margin-right: 8px;"> 记住我
            </label>
        </div>
        
        <button type="submit" class="btn btn-primary" style="width: 100%;">登录</button>
    </form>
    
    <div class="auth-links">
        <p>还没有账号？<a href="register.php">立即注册</a></p>
        <p><a href="forgot_password.php">忘记密码？</a></p>
    </div>
</div>

<?php
include TEMPLATES_PATH . '/footer.php';
?> 