<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' . getSiteName() : getSiteName(); ?></title>
    <meta name="description" content="<?php echo getSiteDescription(); ?>">
    <?php if (getSiteIcon()): ?>
    <link rel="icon" href="<?php echo getSiteIcon(); ?>" type="image/x-icon">
    <link rel="shortcut icon" href="<?php echo getSiteIcon(); ?>" type="image/x-icon">
    <?php endif; ?>
    <!-- 添加jQuery和Bootstrap库 -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Font Awesome图标库 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="<?php echo getSiteUrl(); ?>/assets/css/style.css">
    <!-- 使用手写/可爱字体 -->
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;500;600;700&display=swap" rel="stylesheet">
    <?php if (isset($extraStyles)) echo $extraStyles; ?>
</head>
<body>
    <header class="navbar">
        <div class="container navbar-container">
            <div class="navbar-logo">
                <a href="<?php echo getSiteUrl(); ?>"><?php echo getSiteName(); ?></a>
            </div>
            <nav>
                <ul class="navbar-menu">
                    <li><a href="<?php echo getSiteUrl(); ?>">首页</a></li>
                    <li><a href="<?php echo getSiteUrl(); ?>/learning/honor_wall.php">荣誉墙</a></li>
                    <li><a href="<?php echo getSiteUrl(); ?>/learning/learning_path.php">学习路径</a></li>
                    <li><a href="<?php echo getSiteUrl(); ?>/plans/learning_plans.php">学习规划</a></li>
                    <?php if (isLoggedIn()): ?>
                        <?php if (hasRole('admin') || hasRole('teacher')): ?>
                            <li><a href="<?php echo getSiteUrl(); ?>/admin/">管理中心</a></li>
                        <?php endif; ?>
                        <li><a href="<?php echo getSiteUrl(); ?>/user/profile.php">个人中心</a></li>
                        <li><a href="<?php echo getSiteUrl(); ?>/auth/logout.php">退出</a></li>
                    <?php else: ?>
                        <li><a href="<?php echo getSiteUrl(); ?>/auth/login.php">登录</a></li>
                        <?php if (getSystemSetting('allow_registration', '1') == '1'): ?>
                            <li><a href="<?php echo getSiteUrl(); ?>/auth/register.php">注册</a></li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>
            </nav>
            
            <!-- 汉堡菜单按钮 -->
            <div class="hamburger-menu">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </header>
    
    <!-- 移动端导航 -->
    <div class="mobile-nav">
        <div class="mobile-nav-header">
            <div class="navbar-logo">
                <a href="<?php echo getSiteUrl(); ?>"><?php echo getSiteName(); ?></a>
            </div>
            <button class="mobile-nav-close">&times;</button>
        </div>
        <ul class="mobile-nav-menu">
            <li><a href="<?php echo getSiteUrl(); ?>">首页</a></li>
            <li><a href="<?php echo getSiteUrl(); ?>/learning/honor_wall.php">荣誉墙</a></li>
            <li><a href="<?php echo getSiteUrl(); ?>/learning/learning_path.php">学习路径</a></li>
            <li><a href="<?php echo getSiteUrl(); ?>/plans/learning_plans.php">学习规划</a></li>
            <?php if (isLoggedIn()): ?>
                <?php if (hasRole('admin') || hasRole('teacher')): ?>
                    <li><a href="<?php echo getSiteUrl(); ?>/admin/">管理中心</a></li>
                <?php endif; ?>
                <li><a href="<?php echo getSiteUrl(); ?>/user/profile.php">个人中心</a></li>
                <li><a href="<?php echo getSiteUrl(); ?>/auth/logout.php">退出</a></li>
            <?php else: ?>
                <li><a href="<?php echo getSiteUrl(); ?>/auth/login.php">登录</a></li>
                <?php if (getSystemSetting('allow_registration', '1') == '1'): ?>
                    <li><a href="<?php echo getSiteUrl(); ?>/auth/register.php">注册</a></li>
                <?php endif; ?>
            <?php endif; ?>
        </ul>
    </div>
    <div class="mobile-nav-overlay"></div>
    
    <main class="container" style="padding-top: var(--space-md); padding-bottom: var(--space-md);">
    <?php
        // 显示消息提示（如果有）
        if (isset($_SESSION['message'])) {
            echo showAlert($_SESSION['message'], $_SESSION['message_type'] ?? 'info');
            unset($_SESSION['message']);
            unset($_SESSION['message_type']);
        }
    ?> 