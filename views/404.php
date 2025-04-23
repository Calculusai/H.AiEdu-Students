<?php
$page_title = '页面未找到';
include_once VIEW_PATH . '/header.php';
?>

<!-- 导入用户个人中心样式 -->
<link rel="stylesheet" href="<?php echo site_url('assets/css/user-profile.css'); ?>">

<div class="up-container py-5 my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <!-- 404错误卡片 -->
            <div class="up-profile-header up-animate-float text-center">
                <div class="up-avatar-container">
                    <div class="up-avatar-wrapper">
                        <div class="up-avatar">
                            <i class="bi bi-search-heart"></i>
                        </div>
                    </div>
                    <h1 class="up-username">404</h1>
                    <div class="up-role-badge">
                        页面未找到
                    </div>
                    <p class="text-white mt-3 mb-4">您请求的页面不存在或已被移动。</p>
                    
                    <div class="d-flex justify-content-center gap-3 flex-wrap">
                        <a href="javascript:history.back();" class="up-btn-achievements">
                            <i class="bi bi-arrow-left"></i> 返回上一页
                        </a>
                        <a href="<?php echo site_url(); ?>" class="up-btn up-btn-primary up-btn-shine">
                            <i class="bi bi-house-door"></i> 返回首页
                        </a>
                    </div>
                </div>
            </div>

            <!-- 建议导航卡片 -->
            <div class="up-card">
                <div class="up-card-header">
                    <h5 class="up-info-title">
                        <i class="bi bi-map"></i> 您可能想要访问
                    </h5>
                </div>
                <div class="up-card-body">
                    <div class="up-info-item">
                        <div class="up-info-content">
                            <p class="up-info-label">首页</p>
                            <p class="up-info-value">返回网站首页浏览内容</p>
                        </div>
                        <a href="<?php echo site_url(); ?>" class="up-info-icon">
                            <i class="bi bi-house-door"></i>
                        </a>
                    </div>
                    
                    <div class="up-info-item">
                        <div class="up-info-content">
                            <p class="up-info-label">成就展示</p>
                            <p class="up-info-value">查看学生的编程成就</p>
                        </div>
                        <a href="<?php echo site_url('achievements'); ?>" class="up-info-icon info">
                            <i class="bi bi-trophy"></i>
                        </a>
                    </div>
                    
                    <?php if (is_logged_in()): ?>
                    <div class="up-info-item">
                        <div class="up-info-content">
                            <p class="up-info-label">个人中心</p>
                            <p class="up-info-value">查看或修改您的个人资料</p>
                        </div>
                        <a href="<?php echo site_url('profile'); ?>" class="up-info-icon success">
                            <i class="bi bi-person-circle"></i>
                        </a>
                    </div>
                    <?php else: ?>
                    <div class="up-info-item">
                        <div class="up-info-content">
                            <p class="up-info-label">登录</p>
                            <p class="up-info-value">登录您的账号</p>
                        </div>
                        <a href="<?php echo site_url('login'); ?>" class="up-info-icon success">
                            <i class="bi bi-box-arrow-in-right"></i>
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once VIEW_PATH . '/footer.php'; ?> 