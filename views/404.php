<?php
$page_title = '页面未找到';
include_once VIEW_PATH . '/header.php';
?>

<div class="text-center py-5">
    <h1 class="display-1 text-muted">404</h1>
    <h2 class="mb-4">页面未找到</h2>
    <p class="lead mb-5">您请求的页面不存在或已被移动。</p>
    <a href="<?php echo site_url(); ?>" class="btn btn-primary dopamine-button">
        <i class="bi bi-house-door me-2"></i>返回首页
    </a>
</div>

<?php include_once VIEW_PATH . '/footer.php'; ?> 