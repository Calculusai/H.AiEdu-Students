<?php
require_once '../includes/config.php';

$pageTitle = '发生错误';

// 获取错误信息
$error_message = $_SESSION['error_message'] ?? '发生了未知错误';
$error_code = $_SESSION['error_code'] ?? 500;

// 清除会话中的错误信息
unset($_SESSION['error_message']);
unset($_SESSION['error_code']);

// 设置HTTP状态码
http_response_code($error_code);

// 页面特定样式
$extraStyles = <<<HTML
<style>
    .error-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 60px 20px;
        max-width: 800px;
        margin: 0 auto;
    }
    
    .error-code {
        font-size: 120px;
        font-weight: 700;
        margin: 0;
        line-height: 1;
        background: linear-gradient(135deg, var(--red), var(--orange));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    
    .error-title {
        font-size: 32px;
        margin: 20px 0;
        color: var(--text-primary);
    }
    
    .error-message {
        font-size: 18px;
        margin-bottom: 30px;
        color: var(--text-secondary);
        max-width: 600px;
    }
    
    .error-actions {
        display: flex;
        gap: 20px;
        margin-top: 20px;
    }
    
    .error-image {
        max-width: 300px;
        margin-bottom: 30px;
    }
    
    @media (max-width: 768px) {
        .error-code {
            font-size: 80px;
        }
        
        .error-title {
            font-size: 24px;
        }
        
        .error-message {
            font-size: 16px;
        }
        
        .error-actions {
            flex-direction: column;
            gap: 10px;
        }
    }
</style>
HTML;

include TEMPLATES_PATH . '/header.php';
?>

<div class="error-container">
    <h1 class="error-code"><?php echo $error_code; ?></h1>
    <h2 class="error-title">发生错误</h2>
    <p class="error-message">
        <?php echo htmlspecialchars($error_message); ?>
    </p>
    <img src="../assets/images/error-robot.png" alt="错误图示" class="error-image">
    <div class="error-actions">
        <a href="../index.php" class="btn btn-primary">返回首页</a>
        <a href="javascript:history.back();" class="btn btn-secondary">返回上一页</a>
    </div>
</div>

<?php
include TEMPLATES_PATH . '/footer.php';
?> 