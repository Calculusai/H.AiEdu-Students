<?php
// 设置配置包含标记，避免在sidebar中重复包含
$config_included = true;

// 包含必要的配置文件
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

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

// 获取统计数据
try {
    // 学生数量
    $stmt = $db->prepare("SELECT COUNT(*) FROM accounts WHERE user_role = 'student'");
    $stmt->execute();
    $total_students = $stmt->fetchColumn();
    
    // 课程数量
    $stmt = $db->prepare("SELECT COUNT(*) FROM courses");
    $stmt->execute();
    $total_courses = $stmt->fetchColumn();
    
    // 完成率
    $stmt = $db->prepare("
        SELECT ROUND(
            (SELECT COUNT(*) FROM user_course_progress WHERE status = 'completed') / 
            (SELECT COUNT(*) FROM user_course_progress) * 100
        ) as completion_rate
    ");
    $stmt->execute();
    $completion_rate = $stmt->fetchColumn() ?: 0;
    
    // 新注册学生
    $stmt = $db->prepare("
        SELECT COUNT(*) FROM accounts 
        WHERE user_role = 'student' 
        AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    ");
    $stmt->execute();
    $new_students = $stmt->fetchColumn();
    
} catch (Exception $e) {
    error_log("Error fetching admin statistics: " . $e->getMessage());
    // 继续执行，使用默认值
    $total_students = 0;
    $total_courses = 0;
    $completion_rate = 0;
    $new_students = 0;
}

// 页面标题
$page_title = "管理中心 - 儿童编程教育平台";
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* 管理后台布局 */
        .admin-container {
            display: flex;
            min-height: 100vh;
        }
        
        .admin-content {
            flex: 1;
            padding: 20px;
            background-color: #f5f7fb;
        }
        
        /* 仪表盘卡片样式 */
        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .dashboard-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.05);
            padding: 20px;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.1);
        }
        
        .dashboard-card-title {
            font-size: 14px;
            color: #666;
            margin-bottom: 5px;
        }
        
        .dashboard-card-value {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #333;
        }
        
        .dashboard-card-icon {
            float: right;
            font-size: 32px;
            color: rgba(0,123,255,0.2);
        }
        
        .dashboard-card-footer {
            font-size: 12px;
            color: #999;
            margin-top: 10px;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
        
        /* 最近活动样式 */
        .recent-activity {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.05);
            padding: 20px;
        }
        
        .recent-activity h3 {
            margin-top: 0;
            margin-bottom: 20px;
            font-size: 18px;
            color: #333;
        }
        
        .activity-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .activity-item {
            padding: 15px 0;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
        }
        
        .activity-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 15px;
        }
        
        .activity-content {
            flex: 1;
        }
        
        .activity-title {
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 5px;
        }
        
        .activity-meta {
            font-size: 12px;
            color: #999;
        }
        
        .admin-welcome {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.05);
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .admin-welcome h2 {
            margin-top: 0;
            color: #333;
        }
        
        .admin-welcome p {
            color: #666;
            line-height: 1.5;
        }
        
        /* 移动设备适配 */
        @media (max-width: 768px) {
            .admin-container {
                flex-direction: column;
            }
            
            .dashboard-cards {
                grid-template-columns: 1fr;
            }
            
            .admin-content {
                padding: 15px;
            }
            
            .admin-mobile-header {
                display: flex;
                align-items: center;
                padding: 15px;
                background: white;
                border-radius: 10px;
                box-shadow: 0 4px 8px rgba(0,0,0,0.05);
                margin-bottom: 20px;
            }
            
            .admin-sidebar-toggle {
                background: none;
                border: none;
                font-size: 24px;
                margin-right: 15px;
                cursor: pointer;
            }
            
            .admin-sidebar-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.5);
                z-index: 5;
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include 'admin_sidebar.php'; ?>
        
        <div class="admin-content">
            <div class="admin-welcome">
                <h2>欢迎回来，管理员</h2>
                <p>今天是 <?php echo date('Y年m月d日'); ?>，这是您的管理控制台概览。</p>
            </div>
            
            <!-- 统计卡片 -->
            <div class="dashboard-cards">
                <!-- 学生数量卡片 -->
                <div class="dashboard-card">
                    <div class="dashboard-card-icon"><i class="fas fa-users"></i></div>
                    <div class="dashboard-card-title">学生总数</div>
                    <div class="dashboard-card-value"><?php echo $total_students; ?></div>
                    <div class="dashboard-card-footer">
                        <span class="text-success"><i class="fas fa-arrow-up"></i> <?php echo $new_students; ?></span> 新增学生（近7天）
                    </div>
                </div>
                
                <!-- 课程数量卡片 -->
                <div class="dashboard-card">
                    <div class="dashboard-card-icon"><i class="fas fa-book"></i></div>
                    <div class="dashboard-card-title">课程总数</div>
                    <div class="dashboard-card-value"><?php echo $total_courses; ?></div>
                    <div class="dashboard-card-footer">
                        点击查看 <a href="courses.php">全部课程</a>
                    </div>
                </div>
                
                <!-- 完成率卡片 -->
                <div class="dashboard-card">
                    <div class="dashboard-card-icon"><i class="fas fa-chart-line"></i></div>
                    <div class="dashboard-card-title">课程完成率</div>
                    <div class="dashboard-card-value"><?php echo $completion_rate; ?>%</div>
                    <div class="dashboard-card-footer">
                        平台课程整体完成百分比
                    </div>
                </div>
                
                <!-- 系统状态卡片 -->
                <div class="dashboard-card">
                    <div class="dashboard-card-icon"><i class="fas fa-server"></i></div>
                    <div class="dashboard-card-title">系统状态</div>
                    <div class="dashboard-card-value">正常</div>
                    <div class="dashboard-card-footer">
                        上次检查: <?php echo date('H:i'); ?>
                    </div>
                </div>
            </div>
            
            <div class="recent-activity">
                <h3>最近活动</h3>
                <ul class="activity-list">
                    <li class="activity-item">
                        <img src="../assets/images/default-avatar.png" alt="用户头像" class="activity-avatar">
                        <div class="activity-content">
                            <div class="activity-title">张小明完成了Python基础课程</div>
                            <div class="activity-meta">2小时前</div>
                        </div>
                    </li>
                    
                    <li class="activity-item">
                        <img src="../assets/images/default-avatar.png" alt="用户头像" class="activity-avatar">
                        <div class="activity-content">
                            <div class="activity-title">李华获得了"编程大师"徽章</div>
                            <div class="activity-meta">昨天</div>
                        </div>
                    </li>
                    
                    <li class="activity-item">
                        <img src="../assets/images/default-avatar.png" alt="用户头像" class="activity-avatar">
                        <div class="activity-content">
                            <div class="activity-title">王芳注册了新账号</div>
                            <div class="activity-meta">2天前</div>
                        </div>
                    </li>
                    
                    <li class="activity-item">
                        <img src="../assets/images/default-avatar.png" alt="用户头像" class="activity-avatar">
                        <div class="activity-content">
                            <div class="activity-title">管理员添加了新课程"Scratch进阶"</div>
                            <div class="activity-meta">3天前</div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/script.js"></script>
</body>
</html> 