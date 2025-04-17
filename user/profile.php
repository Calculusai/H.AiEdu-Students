<?php
require_once '../includes/config.php';

$pageTitle = '个人中心';

// 检查用户是否已登录
if (!isLoggedIn()) {
    // 记录当前页面URL，以便登录后重定向回来
    $_SESSION['redirect_after_login'] = getCurrentUrl();
    
    // 设置消息
    $_SESSION['message'] = '请先登录以访问个人中心';
    $_SESSION['message_type'] = 'info';
    
    // 重定向到首页而不是登录页面
    redirect('../auth/login.php');
}

// 获取当前用户ID
$userId = $_SESSION['user_id'];
$db = Database::getInstance();

// 获取用户基本信息
$userInfo = [];
try {
    $stmt = $db->prepare("SELECT a.*, s.* FROM accounts a 
                        LEFT JOIN students s ON a.id = s.account_id 
                        WHERE a.id = ?");
    $stmt->execute([$userId]);
    $userInfo = $stmt->fetch();
} catch (PDOException $e) {
    error_log("用户信息查询错误: " . $e->getMessage());
}

// 获取学生ID
$studentId = null;
if (isset($userInfo['id'])) {
    // 尝试获取学生ID
    try {
        $stmt = $db->prepare("SELECT id FROM students WHERE account_id = ?");
        $stmt->execute([$userId]);
        $studentRow = $stmt->fetch();
        if ($studentRow) {
            $studentId = $studentRow['id'];
        }
    } catch (PDOException $e) {
        error_log("学生ID查询错误: " . $e->getMessage());
    }
}

// 获取用户荣誉记录
$honors = [];
try {
    // 如果有学生ID，使用学生ID查询荣誉记录，否则使用账号ID
    if ($studentId) {
        $stmt = $db->prepare("SELECT h.*, 
                             DATE_FORMAT(h.honor_date, '%Y-%m-%d') as formatted_date 
                             FROM honors h 
                             WHERE h.student_id = ? 
                             ORDER BY h.honor_date DESC");
        $stmt->execute([$studentId]);
    } else {
        $stmt = $db->prepare("SELECT h.*, 
                             DATE_FORMAT(h.honor_date, '%Y-%m-%d') as formatted_date 
                             FROM honors h 
                             JOIN students s ON h.student_id = s.id 
                             WHERE s.account_id = ? 
                             ORDER BY h.honor_date DESC");
    $stmt->execute([$userId]);
    }
    $honors = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("荣誉记录查询错误: " . $e->getMessage());
}

// 获取用户学习进度
$progresses = [];
try {
    // 如果有学生ID，使用学生ID查询学习进度，否则使用账号ID
    if ($studentId) {
        $stmt = $db->prepare("SELECT lp.*, 
                             DATE_FORMAT(lp.last_studied, '%Y-%m-%d %H:%i') as formatted_date,
                             c.title as course_title
                             FROM learning_progress lp
                             LEFT JOIN courses c ON lp.resource_id = c.id
                             WHERE lp.student_id = ? 
                             ORDER BY lp.last_studied DESC");
        $stmt->execute([$studentId]);
    } else {
        // 使用账号ID查询关联的学生ID，然后查询学习进度
        $stmt = $db->prepare("SELECT lp.*, 
                             DATE_FORMAT(lp.last_studied, '%Y-%m-%d %H:%i') as formatted_date,
                             c.title as course_title
                             FROM learning_progress lp
                             LEFT JOIN courses c ON lp.resource_id = c.id
                             JOIN students s ON lp.student_id = s.id
                             WHERE s.account_id = ? 
                             ORDER BY lp.last_studied DESC");
    $stmt->execute([$userId]);
    }
    $progresses = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("学习进度查询错误: " . $e->getMessage());
}

// 获取用户考级成绩
$scores = [];
try {
    // 如果有学生ID，使用学生ID查询成绩，否则使用账号ID
    if ($studentId) {
        $stmt = $db->prepare("SELECT es.*, el.level_name, ec.category_name, 
                             DATE_FORMAT(es.exam_date, '%Y-%m-%d') as formatted_date 
                             FROM exam_scores es 
                             JOIN exam_levels el ON es.exam_level_id = el.id
                             JOIN exam_categories ec ON el.category_id = ec.id
                             WHERE es.student_id = ? 
                             ORDER BY es.exam_date DESC");
        $stmt->execute([$studentId]);
    } else {
        $stmt = $db->prepare("SELECT es.*, el.level_name, ec.category_name, 
                             DATE_FORMAT(es.exam_date, '%Y-%m-%d') as formatted_date 
                             FROM exam_scores es 
                             JOIN exam_levels el ON es.exam_level_id = el.id
                             JOIN exam_categories ec ON el.category_id = ec.id
                             JOIN students st ON es.student_id = st.id
                             WHERE st.account_id = ? 
                             ORDER BY es.exam_date DESC");
    $stmt->execute([$userId]);
    }
    $scores = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("成绩记录查询错误: " . $e->getMessage());
}

// 获取考试成绩
$examScores = [];
try {
    $stmt = $db->prepare("SELECT es.*, el.level_name, ec.category_name 
                          FROM exam_scores es
                          JOIN exam_levels el ON es.exam_level_id = el.id
                          JOIN exam_categories ec ON el.category_id = ec.id
                          WHERE es.student_id = ?");
    $stmt->execute([$userId]);
    $examScores = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("考试成绩查询错误: " . $e->getMessage());
}

// 获取用户学习规划
$learningPlans = [];
try {
    // 如果有学生ID，使用学生ID查询学习规划
    if ($studentId) {
        $stmt = $db->prepare("SELECT lp.*, 
                             DATE_FORMAT(lp.created_at, '%Y-%m-%d') as formatted_date,
                             DATE_FORMAT(lp.start_date, '%Y-%m-%d') as formatted_start_date,
                             DATE_FORMAT(lp.end_date, '%Y-%m-%d') as formatted_end_date
                             FROM learning_plans lp
                             WHERE lp.student_id = ? 
                             ORDER BY lp.created_at DESC");
        $stmt->execute([$studentId]);
    } else {
        $stmt = $db->prepare("SELECT lp.*, 
                             DATE_FORMAT(lp.created_at, '%Y-%m-%d') as formatted_date,
                             DATE_FORMAT(lp.start_date, '%Y-%m-%d') as formatted_start_date,
                             DATE_FORMAT(lp.end_date, '%Y-%m-%d') as formatted_end_date
                             FROM learning_plans lp
                             JOIN students s ON lp.student_id = s.id
                             WHERE s.account_id = ? 
                             ORDER BY lp.created_at DESC");
        $stmt->execute([$userId]);
    }
    $learningPlans = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("学习规划查询错误: " . $e->getMessage());
}

// 获取学习规划数量
$plansCount = count($learningPlans);

// 获取总的荣誉数量和成绩数量
$honorsCount = count($honors);
$scoresCount = count($scores);

// 获取用户最近的课程进度统计
$overallProgress = 0;
$coursesCount = 0;
$completedCourses = 0;

try {
    if ($studentId) {
        $stmt = $db->prepare("SELECT AVG(progress) as avg_progress, COUNT(*) as count,
                             SUM(CASE WHEN progress = 100 THEN 1 ELSE 0 END) as completed
                             FROM user_courses 
                             WHERE user_id = ?");
        $stmt->execute([$userId]);
    } else {
        $stmt = $db->prepare("SELECT AVG(progress) as avg_progress, COUNT(*) as count,
                             SUM(CASE WHEN progress = 100 THEN 1 ELSE 0 END) as completed
                             FROM user_courses 
                             WHERE user_id = ?");
        $stmt->execute([$userId]);
    }
    $progressStats = $stmt->fetch();
    if ($progressStats) {
        $overallProgress = round($progressStats['avg_progress'] ?? 0);
        $coursesCount = $progressStats['count'] ?? 0;
        $completedCourses = $progressStats['completed'] ?? 0;
    }
} catch (PDOException $e) {
    error_log("课程进度统计查询错误: " . $e->getMessage());
}

// 获取学习活跃度数据
$lastActivity = null;
$activeDays = 0;

try {
    if ($studentId) {
        $stmt = $db->prepare("SELECT MAX(last_studied) as last_activity, 
                             COUNT(DISTINCT DATE(last_studied)) as active_days
                             FROM learning_progress 
                             WHERE student_id = ? AND last_studied >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
        $stmt->execute([$studentId]);
    } else {
        $stmt = $db->prepare("SELECT MAX(last_studied) as last_activity, 
                             COUNT(DISTINCT DATE(last_studied)) as active_days
                             FROM learning_progress 
                             JOIN students s ON learning_progress.student_id = s.id
                             WHERE s.account_id = ? AND last_studied >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
        $stmt->execute([$userId]);
    }
    $activityStats = $stmt->fetch();
    if ($activityStats) {
        $lastActivity = $activityStats['last_activity'];
        $activeDays = $activityStats['active_days'];
    }
} catch (PDOException $e) {
    error_log("学习活跃度查询错误: " . $e->getMessage());
}

// 获取用户学习路径
$learningPaths = [];
try {
    // 如果有学生ID，使用学生ID查询学习路径
    if ($studentId) {
        $stmt = $db->prepare("SELECT lp.*, 
                             DATE_FORMAT(lp.created_at, '%Y-%m-%d') as formatted_date,
                             COUNT(lpc.id) as course_count
                             FROM learning_paths lp
                             LEFT JOIN learning_path_courses lpc ON lp.id = lpc.path_id
                             WHERE lp.student_id = ? 
                             GROUP BY lp.id
                             ORDER BY lp.created_at DESC");
        $stmt->execute([$studentId]);
        $learningPaths = $stmt->fetchAll();
        
        // 获取每个学习路径的课程详情
        foreach ($learningPaths as &$path) {
            $stmt = $db->prepare("SELECT lpc.*, c.title as course_title, c.category, c.difficulty 
                                 FROM learning_path_courses lpc
                                 JOIN courses c ON lpc.course_id = c.id
                                 WHERE lpc.path_id = ?
                                 ORDER BY lpc.`order`");
            $stmt->execute([$path['id']]);
            $path['courses'] = $stmt->fetchAll();
        }
    } else {
        // 使用账号ID查询关联的学生ID，然后查询学习路径
        $stmt = $db->prepare("SELECT lp.*, 
                             DATE_FORMAT(lp.created_at, '%Y-%m-%d') as formatted_date,
                             COUNT(lpc.id) as course_count
                             FROM learning_paths lp
                             LEFT JOIN learning_path_courses lpc ON lp.id = lpc.path_id
                             JOIN students s ON lp.student_id = s.id
                             WHERE s.account_id = ? 
                             GROUP BY lp.id
                             ORDER BY lp.created_at DESC");
        $stmt->execute([$userId]);
        $learningPaths = $stmt->fetchAll();
        
        // 获取每个学习路径的课程详情
        foreach ($learningPaths as &$path) {
            $stmt = $db->prepare("SELECT lpc.*, c.title as course_title, c.category, c.difficulty 
                                 FROM learning_path_courses lpc
                                 JOIN courses c ON lpc.course_id = c.id
                                 WHERE lpc.path_id = ?
                                 ORDER BY lpc.`order`");
            $stmt->execute([$path['id']]);
            $path['courses'] = $stmt->fetchAll();
        }
    }
} catch (PDOException $e) {
    error_log("学习路径查询错误: " . $e->getMessage());
}

// 页面特定样式，移除页内样式将其改为引用外部文件
$extraStyles = <<<HTML
<link rel="stylesheet" href="../assets/css/profile.css">
HTML;

include '../templates/header.php';
?>

<!-- 页面标题 -->
<div class="page-header">
    <div class="container">
        <h1>个人中心</h1>
        <p>查看和管理您的个人信息、学习进度和成绩记录</p>
    </div>
</div>


<div class="profile-container">
    <!-- 侧边栏 -->
    <div class="profile-sidebar">
        <div class="profile-avatar">
            <?php if (isset($userInfo['avatar']) && !empty($userInfo['avatar'])): ?>
                <img src="<?php echo $userInfo['avatar']; ?>" alt="用户头像">
            <?php else: ?>
                <span><?php echo mb_substr($userInfo['username'] ?? '用户', 0, 1, 'UTF-8'); ?></span>
            <?php endif; ?>
        </div>
        
        <h2 class="profile-name"><?php echo $userInfo['name'] ?? $userInfo['username'] ?? '用户'; ?></h2>
        <div class="profile-role">
            <span class="badge badge-primary">
                <?php 
                $role = $userInfo['role'] ?? 'student';
                $roleName = [
                    'student' => '学生',
                    'parent' => '家长',
                    'teacher' => '教师',
                    'admin' => '管理员'
                ];
                echo $roleName[$role] ?? '学生';
                ?>
            </span>
        </div>
        
        <div class="profile-stats">
            <div class="stat-card">
                <div class="stat-value"><?php echo $honorsCount; ?></div>
                <div class="stat-label">荣誉证书</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $completedCourses; ?></div>
                <div class="stat-label">完成课程</div>
            </div>
        </div>
        
        <div class="profile-stats">
            <div class="stat-card">
                <div class="stat-value"><?php echo $scoresCount; ?></div>
                <div class="stat-label">考级成绩</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $activeDays; ?></div>
                <div class="stat-label">活跃天数</div>
            </div>
        </div>
        
        <ul class="profile-menu">
            <li><a href="#basic-info" class="active">基本信息</a></li>
            <li><a href="#learning-paths">学习路径</a></li>
            <li><a href="#learning-plans">学习规划</a></li>
            <li><a href="#honors">荣誉证书</a></li>
            <li><a href="#scores">考级成绩</a></li>
            <li><a href="settings.php">账号设置</a></li>
            <li><a href="../auth/logout.php">退出登录</a></li>
        </ul>
    </div>
    
    <!-- 主要内容 -->
    <div class="profile-main">
        <!-- 基本信息 -->
        <div class="content-card" id="basic-info">
            <h3 class="content-title">基本信息</h3>
            
            <div class="info-summary">
                <div class="info-activity">
                    <div class="activity-label">学习活跃度</div>
                    <div class="activity-status">
                        <?php if ($lastActivity): ?>
                            <span class="activity-indicator active"></span>
                            <span>最近活动: <?php echo date('Y-m-d', strtotime($lastActivity)); ?></span>
                        <?php else: ?>
                            <span class="activity-indicator"></span>
                            <span>暂无活动记录</span>
                        <?php endif; ?>
                    </div>
                    <div class="activity-days">
                        <div class="days-label">30天内活跃天数</div>
                        <div class="days-value"><?php echo $activeDays; ?>/30</div>
                        <div class="days-bar">
                            <div class="days-fill" style="width: <?php echo ($activeDays / 30) * 100; ?>%"></div>
                        </div>
                    </div>
                </div>
                
                <div class="info-status">
                    <div class="status-item">
                        <i class="fas fa-book-open"></i>
                        <div class="status-content">
                            <div class="status-value"><?php echo $coursesCount; ?></div>
                            <div class="status-label">在学课程</div>
                        </div>
                    </div>
                    <div class="status-item">
                        <i class="fas fa-graduation-cap"></i>
                        <div class="status-content">
                            <div class="status-value"><?php echo $completedCourses; ?></div>
                            <div class="status-label">已完成</div>
                        </div>
                    </div>
                    <div class="status-item">
                        <i class="fas fa-trophy"></i>
                        <div class="status-content">
                            <div class="status-value"><?php echo $honorsCount; ?></div>
                            <div class="status-label">总荣誉</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="basic-info">
                <div>
                    <div class="info-group">
                        <div class="info-label">姓名</div>
                        <div class="info-value"><?php echo $userInfo['name'] ?? '未设置'; ?></div>
                    </div>
                    
                    <div class="info-group">
                        <div class="info-label">性别</div>
                        <div class="info-value"><?php echo $userInfo['gender'] ?? '未设置'; ?></div>
                    </div>
                    
                    <div class="info-group">
                        <div class="info-label">出生日期</div>
                        <div class="info-value"><?php echo $userInfo['birth_date'] ?? '未设置'; ?></div>
                    </div>
                </div>
                
                <div>
                    <div class="info-group">
                        <div class="info-label">用户名</div>
                        <div class="info-value"><?php echo $userInfo['username'] ?? '未设置'; ?></div>
                    </div>
                    
                    <div class="info-group">
                        <div class="info-label">电子邮箱</div>
                        <div class="info-value"><?php echo $userInfo['email'] ?? '未设置'; ?></div>
                    </div>
                    
                    <div class="info-group">
                        <div class="info-label">手机号码</div>
                        <div class="info-value"><?php echo $userInfo['phone'] ?? '未设置'; ?></div>
                    </div>
                </div>
            </div>
            
            <div style="text-align: right; margin-top: var(--space-md);">
                <a href="edit_profile.php" class="btn btn-primary"><i class="fas fa-user-edit"></i> 编辑信息</a>
            </div>
        </div>
        
        <!-- 学习路径 -->
        <div class="content-card" id="learning-paths">
            <h3 class="content-title">
                学习路径
                <span class="badge badge-primary"><?php echo count($learningPaths); ?> 个路径</span>
            </h3>
            
            <?php if (empty($learningPaths)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> 暂无学习路径记录。开始学习路径，提高学习效率。
                </div>
            <?php else: ?>
                <div class="plan-summary">
                    <div class="stats-row">
                        <div class="stat-block">
                            <div class="stat-value"><?php echo count($learningPaths); ?></div>
                            <div class="stat-label">总路径数</div>
                        </div>
                        <div class="stat-block">
                            <div class="stat-value"><?php echo array_sum(array_column($learningPaths, 'course_count')); ?></div>
                            <div class="stat-label">包含课程</div>
                        </div>
                    </div>
                </div>
                
                <h4 style="margin-top: var(--space-md); margin-bottom: var(--space-sm); color: var(--text-secondary);">路径详情</h4>
                <?php foreach ($learningPaths as $path): ?>
                    <div class="plan-card">
                        <div class="plan-header">
                            <div class="plan-title"><?php echo htmlspecialchars($path['path_name']); ?></div>
                            <div class="plan-date"><i class="far fa-calendar-alt"></i> <?php echo $path['formatted_date']; ?></div>
                        </div>
                        
                        <?php if (!empty($path['description'])): ?>
                            <div class="plan-content">
                                <div class="plan-label">路径描述</div>
                                <div class="plan-text"><?php echo htmlspecialchars($path['description']); ?></div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($path['courses'])): ?>
                            <div class="plan-content">
                                <div class="plan-label">包含课程（<?php echo count($path['courses']); ?>）</div>
                                <div class="path-courses">
                                    <?php foreach ($path['courses'] as $index => $course): ?>
                                        <div class="path-course-item">
                                            <span class="course-number"><?php echo $index + 1; ?></span>
                                            <span class="course-title"><?php echo htmlspecialchars($course['course_title']); ?></span>
                                            <span class="course-category"><?php echo htmlspecialchars($course['category']); ?></span>
                                            <span class="course-difficulty"><?php echo htmlspecialchars($course['difficulty']); ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <div style="text-align: center; margin-top: var(--space-md);">
                <a href="../plans/learning_paths.php" class="btn btn-primary"><i class="fas fa-map"></i> 查看所有学习路径</a>
            </div>
        </div>
        
        <!-- 学习规划 -->
        <div class="content-card" id="learning-plans">
            <h3 class="content-title">
                学习规划
                <span class="badge badge-primary"><?php echo $plansCount; ?> 个规划</span>
            </h3>
            
            <?php if (empty($learningPlans)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> 暂无学习规划记录。制定学习规划，提高学习效率。
                </div>
            <?php else: ?>
                <div class="plan-summary">
                    <div class="stats-row">
                        <div class="stat-block">
                            <div class="stat-value"><?php echo $plansCount; ?></div>
                            <div class="stat-label">总规划数</div>
                        </div>
                        <div class="stat-block">
                            <?php 
                                $completedPlans = 0;
                                foreach ($learningPlans as $plan) {
                                    if ($plan['status'] === '已完成') $completedPlans++;
                                }
                            ?>
                            <div class="stat-value"><?php echo $completedPlans; ?></div>
                            <div class="stat-label">已完成</div>
                        </div>
                    </div>
                </div>
                
                <h4 style="margin-top: var(--space-md); margin-bottom: var(--space-sm); color: var(--text-secondary);">规划详情</h4>
                <?php foreach ($learningPlans as $plan): ?>
                    <div class="plan-card">
                        <div class="plan-header">
                            <div class="plan-title"><?php echo htmlspecialchars($plan['plan_title']); ?></div>
                            <div class="plan-date">
                                <span class="status-badge <?php 
                                    switch($plan['status']) {
                                        case '已完成': echo 'badge-success'; break;
                                        case '进行中': echo 'badge-primary'; break;
                                        case '未开始': echo 'badge-info'; break;
                                        case '已逾期': echo 'badge-danger'; break;
                                        default: echo 'badge-secondary';
                                    }
                                ?>">
                                    <?php echo $plan['status']; ?>
                                </span>
                                <span class="date-info">
                                    <i class="far fa-calendar-alt"></i> 
                                    <?php if (!empty($plan['formatted_start_date']) && !empty($plan['formatted_end_date'])): ?>
                                        <?php echo $plan['formatted_start_date']; ?> 至 <?php echo $plan['formatted_end_date']; ?>
                                    <?php else: ?>
                                        <?php echo $plan['formatted_date']; ?>
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
                        
                        <?php if (!empty($plan['goal'])): ?>
                            <div class="plan-content">
                                <div class="plan-label">阶段目标</div>
                                <div class="plan-text"><?php echo htmlspecialchars($plan['goal']); ?></div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="plan-content">
                            <div class="progress-container">
                                <div class="progress-info">
                                    <span>完成进度</span>
                                    <span><?php echo $plan['progress']; ?>%</span>
                                </div>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?php echo $plan['progress']; ?>%"></div>
                                </div>
                            </div>
                        </div>
                        
                        <?php if (!empty($plan['result'])): ?>
                            <div class="plan-content">
                                <div class="plan-label">阶段成果</div>
                                <div class="plan-text"><?php echo htmlspecialchars($plan['result']); ?></div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <div style="text-align: center; margin-top: var(--space-md);">
                <a href="../plans/learning_plans.php" class="btn btn-primary"><i class="fas fa-tasks"></i> 查看所有学习规划</a>
                <button type="button" class="btn btn-outline" data-toggle="modal" data-target="#addPlanModal"><i class="fas fa-plus"></i> 添加规划</button>
            </div>
        </div>
        
        <!-- 荣誉证书 -->
        <div class="content-card" id="honors">
            <h3 class="content-title">
                荣誉证书
                <span class="badge badge-primary"><?php echo $honorsCount; ?> 个荣誉</span>
            </h3>
            
            <?php if (empty($honors)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> 暂无荣誉证书记录。完成课程和考级，获得荣誉将会显示在这里。
                </div>
            <?php else: ?>
                <div class="honor-grid">
                    <?php foreach ($honors as $honor): ?>
                        <div class="honor-card">
                            <div class="honor-title"><?php echo $honor['honor_title']; ?></div>
                            <div class="honor-type"><?php echo $honor['honor_type'] ?? '证书'; ?></div>
                            <div class="honor-date"><i class="far fa-calendar-alt"></i> <?php echo $honor['formatted_date']; ?></div>
                            <?php if (!empty($honor['description'])): ?>
                                <div class="honor-description"><?php echo $honor['description']; ?></div>
                            <?php endif; ?>
                            </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <div style="text-align: center; margin-top: var(--space-md);">
                <a href="../learning/honor_wall.php" class="btn btn-primary"><i class="fas fa-trophy"></i> 查看荣誉墙</a>
                <button type="button" class="btn btn-outline" data-toggle="modal" data-target="#addHonorModal"><i class="fas fa-plus"></i> 添加荣誉</button>
            </div>
        </div>
        
        <!-- 考级成绩 -->
        <div class="content-card" id="scores">
            <h3 class="content-title">
                考级成绩
                <span class="badge badge-primary"><?php echo $scoresCount; ?> 个成绩</span>
            </h3>
            
            <?php if (empty($scores)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> 暂无考级成绩记录。参加考试，成绩将会显示在这里。
                </div>
            <?php else: ?>
                <div class="score-table-wrapper">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>考试名称</th>
                                <th>分数</th>
                                <th>等级</th>
                                <th>日期</th>
                            </tr>
                        </thead>
                        <tbody>
                    <?php foreach ($scores as $score): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo $score['category_name'] . ' ' . $score['level_name']; ?></strong>
                                        <?php if (!empty($score['comment'])): ?>
                                            <div class="small text-muted"><?php echo mb_substr($score['comment'], 0, 30); ?><?php echo (mb_strlen($score['comment']) > 30) ? '...' : ''; ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo ($score['score'] >= 90) ? 'badge-success' : (($score['score'] >= 60) ? 'badge-primary' : 'badge-danger'); ?>">
                                            <?php echo $score['score']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $score['level_name']; ?></td>
                                    <td><?php echo $score['formatted_date']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="score-summary">
                    <div class="score-stats">
                        <?php 
                            $avgScore = array_sum(array_column($scores, 'score')) / count($scores);
                            $highestScore = max(array_column($scores, 'score'));
                        ?>
                        <div class="stat-item">
                            <div class="stat-label">平均分</div>
                            <div class="stat-value"><?php echo number_format($avgScore, 1); ?></div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-label">最高分</div>
                            <div class="stat-value"><?php echo $highestScore; ?></div>
                        </div>
                    </div>
                            </div>
            <?php endif; ?>
            
            <div style="text-align: center; margin-top: var(--space-md);">
                <a href="../exam_scores/index.php" class="btn btn-primary"><i class="fas fa-file-alt"></i> 查看考试成绩</a>
                <button type="button" class="btn btn-outline" data-toggle="modal" data-target="#addScoreModal"><i class="fas fa-plus"></i> 添加成绩</button>
            </div>
        </div>
    </div>
</div>

<?php
// 页面特定脚本
$extraScripts = <<<HTML
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // 侧边菜单滚动定位
        const menuLinks = document.querySelectorAll('.profile-menu a');
        
        menuLinks.forEach(function(link) {
            link.addEventListener('click', function(e) {
                // 如果是链接到其他页面，不处理
                if (this.getAttribute('href').indexOf('.php') > -1) {
                    return;
                }
                
                e.preventDefault();
                
                // 移除所有活动状态
                menuLinks.forEach(a => a.classList.remove('active'));
                
                // 添加当前活动状态
                this.classList.add('active');
                
                // 滚动到对应区域
                const targetId = this.getAttribute('href').substring(1);
                const targetElement = document.getElementById(targetId);
                
                if (targetElement) {
                    window.scrollTo({
                        top: targetElement.offsetTop - 20,
                        behavior: 'smooth'
                    });
                }
            });
        });
        
        // 监听滚动事件
        function updateActiveMenuOnScroll() {
            const scrollPosition = window.scrollY;
            
            // 获取所有内容卡片区域
            const contentSections = document.querySelectorAll('.content-card');
            
            contentSections.forEach(section => {
                const sectionTop = section.offsetTop - 100;
                const sectionBottom = sectionTop + section.offsetHeight;
                
                if (scrollPosition >= sectionTop && scrollPosition < sectionBottom) {
                    const id = section.getAttribute('id');
                    
                    // 移除所有活动状态
                    menuLinks.forEach(a => a.classList.remove('active'));
                    
                    // 激活对应菜单项
                    const activeLink = document.querySelector(\`.profile-menu a[href="#\${id}"]\`);
                    if (activeLink) {
                        activeLink.classList.add('active');
                    }
                }
            });
        }
        
        // 初始化时调用一次
        updateActiveMenuOnScroll();
        
        // 监听滚动事件
        window.addEventListener('scroll', updateActiveMenuOnScroll);
    });
</script>
HTML;

include '../templates/footer.php';

// 引入添加考试成绩模态框
include '../exam_scores/modal_add_score.php';

// 引入添加荣誉证书模态框
include '../honors/modal_add_honor.php';

// 学习规划模态框
?>
<!-- 添加学习规划模态框 -->
<div class="modal fade" id="addPlanModal" tabindex="-1" role="dialog" aria-labelledby="addPlanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addPlanModalLabel">添加学习规划</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="../plans/add_plan.php" method="post">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="plan_title">规划标题</label>
                        <input type="text" class="form-control" id="plan_title" name="plan_title" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="goal">阶段目标</label>
                        <textarea class="form-control" id="goal" name="goal" rows="3" required></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="start_date">开始日期</label>
                            <input type="date" class="form-control" id="start_date" name="start_date">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="end_date">结束日期</label>
                            <input type="date" class="form-control" id="end_date" name="end_date">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="progress">完成进度</label>
                        <input type="range" class="form-control-range" id="progress" name="progress" min="0" max="100" value="0">
                        <div class="d-flex justify-content-between">
                            <small>0%</small>
                            <small id="progressValue">0%</small>
                            <small>100%</small>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="checkbox-container">
                            <input type="checkbox" id="auto_progress" name="auto_progress" class="styled-checkbox">
                            <label for="auto_progress" class="checkbox-label">按照时间计算进度百分比</label>
                            <div class="checkbox-info">勾选后，系统将根据开始日期和结束日期自动计算进度百分比</div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="status">状态</label>
                        <select class="form-control" id="status" name="status">
                            <option value="未开始">未开始</option>
                            <option value="进行中">进行中</option>
                            <option value="已完成">已完成</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="result">阶段成果</label>
                        <textarea class="form-control" id="result" name="result" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary" name="save_plan">保存规划</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 进度条值显示
    const progressRange = document.getElementById('progress');
    const progressValue = document.getElementById('progressValue');
    const autoProgress = document.getElementById('auto_progress');
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    
    if(progressRange && progressValue) {
        progressRange.addEventListener('input', function() {
            progressValue.textContent = this.value + '%';
        });
    }
    
    if(autoProgress && progressRange) {
        // 复选框状态变化时更新进度条状态
        autoProgress.addEventListener('change', function() {
            progressRange.disabled = this.checked;
            if(this.checked) {
                // 如果选中了自动计算，并且有开始和结束日期，计算当前进度
                updateAutoProgress();
            }
        });
        
        // 日期变化时，如果选中了自动计算，更新进度
        if(startDateInput && endDateInput) {
            startDateInput.addEventListener('change', function() {
                if(autoProgress.checked) {
                    updateAutoProgress();
                }
            });
            
            endDateInput.addEventListener('change', function() {
                if(autoProgress.checked) {
                    updateAutoProgress();
                }
            });
        }
    }
    
    // 自动计算进度函数
    function updateAutoProgress() {
        if(!startDateInput || !endDateInput || !progressRange || !progressValue) return;
        
        const startDate = startDateInput.value;
        const endDate = endDateInput.value;
        
        if(startDate && endDate) {
            const start = new Date(startDate).getTime();
            const end = new Date(endDate).getTime();
            const now = new Date().getTime();
            
            let calculatedProgress = 0;
            
            if(now <= start) {
                calculatedProgress = 0; // 还未开始
            } else if(now >= end) {
                calculatedProgress = 100; // 已经结束
            } else {
                const totalDuration = end - start;
                const elapsedDuration = now - start;
                calculatedProgress = Math.min(100, Math.round((elapsedDuration / totalDuration) * 100));
            }
            
            // 更新UI显示
            progressRange.value = calculatedProgress;
            progressValue.textContent = calculatedProgress + '%';
            
            // 根据进度自动选择状态
            const statusSelect = document.getElementById('status');
            if(statusSelect) {
                if(calculatedProgress === 0) {
                    statusSelect.value = '未开始';
                } else if(calculatedProgress === 100) {
                    statusSelect.value = '已完成';
                } else {
                    statusSelect.value = '进行中';
                }
            }
        }
    }
});
</script>

<style>
/* 更简单直接的复选框样式 */
.checkbox-container {
    margin-top: 15px;
    padding: 12px 15px;
    border: 1px solid #ddd;
    border-radius: 5px;
    background-color: #f9f9f9;
}

.styled-checkbox {
    margin-right: 8px;
    transform: scale(1.2); /* 稍微放大复选框 */
}

.checkbox-label {
    font-weight: 500;
    color: #333;
    cursor: pointer;
    vertical-align: middle;
}

.checkbox-info {
    margin-top: 5px;
    margin-left: 24px;
    font-size: 0.85rem;
    color: #666;
}

/* 调整学习规划卡片中的状态标签和日期显示 */
.plan-date {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
    color: var(--text-secondary);
    font-size: 0.9rem;
}

.status-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 15px;
    font-size: 0.75rem;
    color: white;
    font-weight: 500;
    margin-right: 5px;
}

.date-info {
    display: inline-flex;
    align-items: center;
    background-color: #f5f5f5;
    padding: 4px 10px;
    border-radius: 15px;
    font-size: 0.75rem;
}

.date-info i {
    margin-right: 5px;
    color: var(--primary);
}

/* 美化基本信息区域 */
.basic-info {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
    margin-top: 15px;
    padding: 15px;
    background-color: #f9f9f9;
    border-radius: 10px;
}

.info-group {
    margin-bottom: 15px;
    padding: 10px;
    background-color: white;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    transition: all 0.3s ease;
}

.info-group:hover {
    box-shadow: 0 3px 6px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.info-label {
    font-size: 0.85rem;
    color: var(--text-secondary);
    margin-bottom: 5px;
    font-weight: 500;
    display: flex;
    align-items: center;
}

.info-label::before {
    content: "";
    display: inline-block;
    width: 4px;
    height: 4px;
    background-color: var(--primary);
    border-radius: 50%;
    margin-right: 6px;
}

.info-value {
    font-size: 1rem;
    color: var(--text-primary);
    font-weight: 500;
    padding-left: 10px;
}

.info-value:empty::after {
    content: "未设置";
    color: #aaa;
    font-style: italic;
    font-weight: normal;
}
</style>
?> 