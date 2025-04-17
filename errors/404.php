<?php
require_once '../includes/config.php';

$pageTitle = '页面未找到';

// 设置HTTP状态码
http_response_code(404);

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
        color: var(--primary);
        margin: 0;
        line-height: 1;
        background: linear-gradient(135deg, var(--blue), var(--green));
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
    <h1 class="error-code">404</h1>
    <h2 class="error-title">页面未找到</h2>
    <p class="error-message">
        抱歉，您访问的页面不存在或已被移动。
        这可能是因为输入的URL有误，或者该页面已经被删除。
    </p>
    <img src="../assets/images/404-robot.png" alt="404错误图示" class="error-image">
    <div class="error-actions">
        <a href="../index.php" class="btn btn-primary">返回首页</a>
        <a href="javascript:history.back();" class="btn btn-secondary">返回上一页</a>
    </div>
</div>

<?php
include TEMPLATES_PATH . '/footer.php';
?> 