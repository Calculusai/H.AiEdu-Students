<?php
// 包含必要的配置和函数，如果尚未包含
if (!isset($config_included)) {
    require_once '../includes/config.php';
    require_once '../includes/db.php';
    require_once '../includes/functions.php';
}

// 检查用户是否已登录且是管理员
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    $_SESSION['error'] = "您没有访问管理中心的权限！";
    header("Location: ../auth/login.php");
    exit();
}

// 获取当前用户信息
$user_id = $_SESSION['user_id'];
$db = Database::getInstance();
$stmt = $db->prepare("SELECT * FROM accounts WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// 获取当前页面路径，用于高亮当前菜单项
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- 引入外部CSS文件 -->
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="../assets/css/admin-style.css">
<?php if (getSiteIcon()): ?>
<link rel="icon" href="<?php echo getSiteIcon(); ?>" type="image/x-icon">
<link rel="shortcut icon" href="<?php echo getSiteIcon(); ?>" type="image/x-icon">
<?php endif; ?>

<div class="admin-sidebar">
    <h2>管理中心</h2>
    <ul>
        <li><a href="index.php" class="<?php echo ($current_page == 'index.php') ? 'active' : ''; ?>"><i class="fas fa-tachometer-alt"></i> 仪表盘</a></li>
        <li><a href="users.php" class="<?php echo ($current_page == 'users.php') ? 'active' : ''; ?>"><i class="fas fa-user-graduate"></i> 学生管理</a></li>
        <li><a href="courses.php" class="<?php echo ($current_page == 'courses.php') ? 'active' : ''; ?>"><i class="fas fa-book-open"></i> 课程管理</a></li>
        <li><a href="learning_paths.php" class="<?php echo ($current_page == 'learning_paths.php') ? 'active' : ''; ?>"><i class="fas fa-road"></i> 学习路径</a></li>
        <li><a href="learning_plans.php" class="<?php echo ($current_page == 'learning_plans.php') ? 'active' : ''; ?>"><i class="fas fa-tasks"></i> 学习规划</a></li>
        <li><a href="honors.php" class="<?php echo ($current_page == 'honors.php') ? 'active' : ''; ?>"><i class="fas fa-medal"></i> 荣誉证书</a></li>
        <li><a href="exam_scores.php" class="<?php echo ($current_page == 'exam_scores.php') ? 'active' : ''; ?>"><i class="fas fa-graduation-cap"></i> 成绩管理</a></li>
        <li><a href="settings.php" class="<?php echo ($current_page == 'settings.php') ? 'active' : ''; ?>"><i class="fas fa-cog"></i> 系统设置</a></li>
    </ul>
    
    <div class="admin-sidebar-footer">
        <p>欢迎您，<?php echo htmlspecialchars($user['username']); ?></p>
        <p><a href="<?php echo getSiteUrl(); ?>" style="color: rgba(255,255,255,0.8);"><i class="fas fa-home"></i> 返回前台</a></p>
        <p class="version-info">系统版本：<?php echo getSystemSetting('system_version', SYSTEM_VERSION); ?></p>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 添加移动设备菜单切换功能
    const adminContent = document.querySelector('.admin-content');
    
    // 如果是移动设备，创建汉堡菜单按钮
    if (window.innerWidth <= 768 && adminContent) {
        const mobileHeader = document.createElement('div');
        mobileHeader.className = 'admin-mobile-header';
        
        const menuToggle = document.createElement('button');
        menuToggle.className = 'admin-sidebar-toggle';
        menuToggle.innerHTML = '<i class="fas fa-bars"></i>';
        
        menuToggle.addEventListener('click', function() {
            document.querySelector('.admin-sidebar').classList.toggle('show');
            
            // 创建遮罩层
            let overlay = document.querySelector('.admin-sidebar-overlay');
            if (!overlay) {
                overlay = document.createElement('div');
                overlay.className = 'admin-sidebar-overlay';
                document.body.appendChild(overlay);
                
                overlay.addEventListener('click', function() {
                    document.querySelector('.admin-sidebar').classList.remove('show');
                    overlay.style.display = 'none';
                });
            }
            
            overlay.style.display = 'block';
        });
        
        mobileHeader.appendChild(menuToggle);
        mobileHeader.appendChild(document.createElement('div')).innerHTML = '<h1>' + document.title.split(' - ')[0] + '</h1>';
        
        adminContent.insertBefore(mobileHeader, adminContent.firstChild);
    }
});
</script> 