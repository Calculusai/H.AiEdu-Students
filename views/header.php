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
    
    <!-- Bootstrap图标 -->
    <link rel="stylesheet" href="https://cdn.bootcdn.net/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css">
    
    <!-- Font Awesome图标 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Bootstrap JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js" defer></script>
    
    <?php if (isset($extra_css)): ?>
    <!-- 额外CSS -->
    <?php echo $extra_css; ?>
    <?php endif; ?>
</head>
<body<?php echo is_logged_in() ? ' data-logged-in="true"' : ''; ?>>
    <header class="navbar">
        <div class="container">
            <a href="<?php echo site_url(); ?>" class="navbar-brand">
                <i class="bi bi-trophy-fill me-2"></i>
                <?php echo SITE_NAME; ?>
            </a>
            
            <nav>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a href="<?php echo site_url(); ?>" class="nav-link<?php echo isset($active_page) && $active_page === 'home' ? ' active' : ''; ?>">首页</a>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo site_url('achievements'); ?>" class="nav-link<?php echo isset($active_page) && $active_page === 'achievements' ? ' active' : ''; ?>">成就展示</a>
                    </li>
                    
                    <?php if (is_admin()): ?>
                    <li class="nav-item">
                        <a href="<?php echo site_url('admin/dashboard'); ?>" class="nav-link<?php echo isset($active_page) && $active_page === 'admin' ? ' active' : ''; ?>">管理后台</a>
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
                        <a href="<?php echo site_url('login'); ?>" class="nav-link<?php echo isset($active_page) && $active_page === 'login' ? ' active' : ''; ?>">登录</a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
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