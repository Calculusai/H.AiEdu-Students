<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="少儿编程成就展示系统 - 记录和展示少儿编程学习成就">
    <meta name="csrf-token" content="<?php echo isset($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : ''; ?>">
    <title><?php echo isset($page_title) ? $page_title . ' - ' . SITE_NAME : SITE_NAME; ?></title>
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css">
    
    <!-- 基础样式 -->
    <link rel="stylesheet" href="<?php echo asset_url('css/style.css'); ?>">
    
    <!-- 导航栏样式 -->
    <link rel="stylesheet" href="<?php echo asset_url('css/navbar.css'); ?>">
    
    <!-- Bootstrap图标 -->
    <link rel="stylesheet" href="https://cdn.bootcdn.net/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css">
    
    <!-- Font Awesome图标 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Bootstrap JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
    
    <!-- 主脚本 -->
    <script src="<?php echo asset_url('js/main.js'); ?>" defer></script>
    
    <!-- 导航栏交互脚本 -->
    <script src="<?php echo asset_url('js/navbar.js'); ?>" defer></script>
    
    <?php if (isset($extra_css)): ?>
    <!-- 额外CSS -->
    <?php echo $extra_css; ?>
    <?php endif; ?>
</head>
<body<?php echo is_logged_in() ? ' data-logged-in="true"' : ''; ?>>
    <header>
        <nav class="navbar navbar-expand-lg">
            <div class="container">
                <a href="<?php echo site_url(); ?>" class="navbar-brand">
                    <i class="bi bi-trophy-fill"></i>
                    <?php echo SITE_NAME; ?>
                </a>
                
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="切换导航">
                    <span class="navbar-toggler-icon"></span>
                </button>
                
                <div class="collapse navbar-collapse" id="navbarContent">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a href="<?php echo site_url(); ?>" class="nav-link<?php echo isset($active_page) && $active_page === 'home' ? ' active' : ''; ?>">
                                <i class="bi bi-house-door me-1"></i>首页
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo site_url('achievements'); ?>" class="nav-link<?php echo isset($active_page) && $active_page === 'achievements' ? ' active' : ''; ?>">
                                <i class="bi bi-award me-1"></i>成就展示
                            </a>
                        </li>
                        
                        <?php if (is_admin()): ?>
                        <li class="nav-item">
                            <a href="<?php echo site_url('admin/dashboard'); ?>" class="nav-link<?php echo isset($active_page) && $active_page === 'admin' ? ' active' : ''; ?>">
                                <i class="bi bi-speedometer me-1"></i>管理后台
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <?php if (is_logged_in()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-circle me-1"></i> <?php echo htmlspecialchars($_SESSION['username']); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <?php if (is_admin()): ?>
                                <li><a class="dropdown-item" href="<?php echo site_url('admin/dashboard'); ?>"><i class="bi bi-speedometer2 me-2"></i>管理控制台</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <?php endif; ?>
                                <li><a class="dropdown-item" href="<?php echo site_url('profile'); ?>"><i class="bi bi-person me-2"></i>个人资料</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?php echo site_url('logout'); ?>"><i class="bi bi-box-arrow-right me-2"></i>退出登录</a></li>
                            </ul>
                        </li>
                        <?php else: ?>
                        <li class="nav-item">
                            <a href="<?php echo site_url('login'); ?>" class="nav-link<?php echo isset($active_page) && $active_page === 'login' ? ' active' : ''; ?>">
                                <i class="bi bi-box-arrow-in-right me-1"></i>登录
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
    </header>
    
    <main class="container mt-4">
        <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="alert alert-<?php echo $_SESSION['flash_type'] ?: 'info'; ?> alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['flash_message']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="关闭"></button>
        </div>
        <?php 
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
        endif; ?> 