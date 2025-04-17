<?php
require_once 'includes/config.php';
require_once 'includes/db.php';

$pageTitle = '首页';

// 获取数据库连接
$db = Database::getInstance();

// 获取最新的荣誉记录
try {
    $stmt = $db->prepare("
        SELECT h.honor_title, h.honor_type, h.honor_date, s.name as student_name 
        FROM honors h
        JOIN students s ON h.student_id = s.id
        ORDER BY h.honor_date DESC
        LIMIT 3
    ");
    $stmt->execute();
    $recentHonors = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $recentHonors = [];
    error_log("首页荣誉查询错误: " . $e->getMessage());
}

// 获取热门课程
try {
    $stmt = $db->prepare("
        SELECT c.id, c.title, c.category, c.difficulty, 
               (SELECT COUNT(*) FROM course_stats WHERE course_id = c.id) as student_count
        FROM courses c
        ORDER BY student_count DESC, c.created_at DESC
        LIMIT 3
    ");
    $stmt->execute();
    $popularCourses = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $popularCourses = [];
    error_log("首页热门课程查询错误: " . $e->getMessage());
}

// 页面特定样式
$extraStyles = <<<HTML
<style>
    /* 通用样式保留 */
    .hero {
        background: linear-gradient(135deg, var(--primary), var(--purple));
        color: white;
        padding: var(--space-lg) 0;
        margin-bottom: var(--space-lg);
        border-radius: var(--radius-xl);
        text-align: center;
    }
    
    .hero-title {
        font-size: 36px;
        margin-bottom: var(--space-sm);
    }
    
    .hero-subtitle {
        font-size: 20px;
        margin-bottom: var(--space-md);
        opacity: 0.9;
    }
    
    .feature-section {
        margin-bottom: var(--space-lg);
    }
    
    .feature-title {
        text-align: center;
        margin-bottom: var(--space-md);
        color: var(--primary);
    }
    
    .feature-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: var(--space-md);
    }
    
    .intro-section {
        display: flex;
        align-items: center;
        margin-bottom: var(--space-lg);
    }
    
    .intro-image {
        flex: 1;
        text-align: center;
    }
    
    .intro-content {
        flex: 1;
        padding: var(--space-md);
    }
    
    .popular-courses {
        margin-bottom: var(--space-lg);
    }
    
    .course-card {
        position: relative;
        border-radius: 24px;
        padding: var(--space-md);
        transition: all 0.3s ease;
        background: white;
        overflow: hidden;
        box-shadow: 0 8px 20px rgba(255, 92, 138, 0.15);
        border: none;
    }
    
    .course-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background: linear-gradient(to right, var(--orange), var(--primary), var(--purple));
    }
    
    .course-card:hover {
        transform: translateY(-8px) scale(1.02);
        box-shadow: 0 15px 30px rgba(255, 92, 138, 0.25);
    }
    
    .course-category {
        display: inline-block;
        padding: 6px 12px;
        background: linear-gradient(to right, rgba(255, 179, 0, 0.1), rgba(255, 92, 138, 0.1));
        color: var(--orange);
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 12px;
        border: 1px solid rgba(255, 179, 0, 0.2);
    }
    
    .course-stats {
        margin-top: 15px;
        font-size: 14px;
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }
    
    .course-stats .badge {
        background: linear-gradient(to right, var(--blue), var(--purple));
        color: white;
        font-weight: 600;
        padding: 5px 10px;
        border-radius: 15px;
        font-size: 12px;
        box-shadow: 0 3px 6px rgba(178, 102, 255, 0.2);
    }
    
    .course-stats span:not(.badge) {
        color: var(--primary);
        font-weight: 500;
        display: flex;
        align-items: center;
    }
    
    .course-stats span:not(.badge)::before {
        content: '👨‍💻';
        margin-right: 5px;
        font-size: 16px;
    }
    
    .course-card h3 {
        font-size: 18px;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 10px;
        line-height: 1.4;
    }
    
    .course-action {
        margin-top: 20px;
        text-align: center;
    }
    
    .course-action .btn {
        background: linear-gradient(to right, var(--primary), var(--purple));
        color: white;
        font-weight: 700;
        padding: 8px 18px;
        border-radius: 20px;
        display: inline-block;
        transition: all 0.3s ease;
        text-decoration: none;
        box-shadow: 0 4px 10px rgba(255, 92, 138, 0.2);
        border: none;
    }
    
    .course-action .btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 15px rgba(255, 92, 138, 0.3);
    }
    
    /* 首页荣誉墙特定样式 */
    .home-honor-container {
        background-color: #FFF8F0;
        border-radius: var(--radius-xl);
        padding: var(--space-md);
        margin-bottom: var(--space-lg);
    }
    
    .home-honor-title {
        color: var(--primary);
        text-align: center;
        margin-bottom: var(--space-sm);
        font-size: var(--font-h2);
    }
    
    .home-honor-subtitle {
        text-align: center;
        color: var(--text-secondary);
        margin-bottom: var(--space-md);
    }
    
    .home-honor-more {
        text-align: center;
        margin-top: var(--space-md);
    }
    
    .home-honor-btn {
        background: linear-gradient(to right, var(--primary), var(--orange));
        color: white;
        font-weight: 700;
        padding: 10px 30px;
        border-radius: 30px;
        display: inline-block;
        transition: all 0.3s ease;
        text-decoration: none;
        box-shadow: 0 4px 10px rgba(255, 92, 138, 0.2);
    }
    
    .home-honor-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 15px rgba(255, 92, 138, 0.3);
        text-decoration: none;
    }
    
    /* 荣誉卡片日期样式 - 多巴胺风格 */
    .home-honor-date {
        position: absolute;
        top: 10px;
        right: 10px;
        font-size: 14px;
        font-weight: 500;
        color: var(--orange);
        background: linear-gradient(to right, rgba(255, 179, 0, 0.1), rgba(255, 92, 138, 0.1));
        padding: 4px 10px;
        border-radius: 20px;
        border: 1px solid rgba(255, 179, 0, 0.2);
    }
    
    /* 调整荣誉标题位置，防止被日期遮挡 */
    .home-honor-title-wrapper {
        margin-top: 10px;
        padding-top: 15px;
        text-align: center;
    }
    
    /* 自定义多彩渐变分隔线 */
    .home-honor-divider {
        height: 4px;
        width: 100%;
        position: absolute;
        top: 0;
        left: 0;
        background: linear-gradient(to right, var(--orange), var(--primary), var(--purple));
        border-radius: 4px 4px 0 0;
    }
    
    .no-data {
        padding: 20px;
        text-align: center;
        color: var(--text-secondary);
        font-style: italic;
        background: var(--light);
        border-radius: var(--radius-md);
        width: 100%;
    }
    
    @media (max-width: 768px) {
        .intro-section {
            flex-direction: column;
        }
        
        .intro-image, .intro-content {
            flex: 0 0 100%;
        }
    }
</style>
HTML;

include TEMPLATES_PATH . '/header.php';
?>

<!-- 英雄区域 -->
<section class="hero">
    <div class="container">
        <h1 class="hero-title">少儿编程考级与学习规划系统</h1>
        <p class="hero-subtitle">为青少年提供专业的编程考级指导与个性化学习路径设计</p>
        <div>
            <a href="learning/learning_path.php" class="btn btn-primary">探索学习路径</a>
            <a href="learning/honor_wall.php" class="btn btn-secondary" style="margin-left: 10px;">查看荣誉墙</a>
        </div>
    </div>
</section>

<!-- 简介部分 -->
<section class="intro-section">
    <div class="intro-image">
        <img src="assets/images/favicon.png" alt="少儿编程" style="max-width: 50%; border-radius: var(--radius-lg);">
    </div>
    <div class="intro-content">
        <h2>关于我们</h2>
        <p>少儿编程考级与学习规划系统旨在帮助孩子们系统学习编程知识，参与编程考级，并规划个性化的学习路径。</p>
        <p>我们的平台特点：</p>
        <ul>
            <li>科学的编程考级体系，阶段性评估学习成果</li>
            <li>个性化的学习路径规划，根据兴趣和能力定制</li>
        </ul>
    </div>
</section>

<!-- 热门课程 -->
<section class="feature-section popular-courses">
    <h2 class="feature-title">热门课程</h2>
    <div class="feature-cards">
        <?php if (count($popularCourses) > 0): ?>
            <?php foreach ($popularCourses as $course): ?>
                <div class="course-card">
                    <span class="course-category"><?php echo htmlspecialchars($course['category']); ?></span>
                    <h3><?php echo htmlspecialchars($course['title']); ?></h3>
                    <div class="course-stats">
                        <?php 
                        $difficulty_text = '';
                        switch ($course['difficulty']) {
                            case 'beginner':
                                $difficulty_text = '初级';
                                break;
                            case 'intermediate':
                                $difficulty_text = '中级';
                                break;
                            case 'advanced':
                                $difficulty_text = '高级';
                                break;
                            default:
                                if (strpos($course['difficulty'], 'electronicsGrade') === 0) {
                                    $grade = substr($course['difficulty'], -1);
                                    $difficulty_text = '图形化' . $grade . '级';
                                } elseif (strpos($course['difficulty'], 'pythonGrade') === 0) {
                                    $grade = substr($course['difficulty'], -1);
                                    $difficulty_text = 'Python' . $grade . '级';
                                } elseif (strpos($course['difficulty'], 'cppGrade') === 0) {
                                    $grade = substr($course['difficulty'], -1);
                                    $difficulty_text = 'C++' . $grade . '级';
                                } else {
                                    $difficulty_text = $course['difficulty'];
                                }
                        }
                        ?>
                        <span class="badge"><?php echo $difficulty_text; ?></span>
                        <span><?php echo $course['student_count']; ?> 名学生学习</span>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-data">暂无课程数据</div>
        <?php endif; ?>
    </div>
</section>

<!-- 特色功能区 -->
<section class="feature-section">
    <h2 class="feature-title">我们的特色功能</h2>
    <div class="feature-cards">
        <div class="card card-exam">
            <h3 class="card-title">编程考级</h3>
            <p>提供专业的少儿编程等级考试，包括Scratch、Python、C++等多种语言的考级服务，获得权威认证。</p>
            <a href="#" class="btn btn-primary" style="margin-top: 15px;">了解更多</a>
        </div>
        
        <div class="card card-competition">
            <h3 class="card-title">编程比赛</h3>
            <p>推荐适合不同年龄段和能力水平的编程比赛，帮助学生积累实战经验，提升竞争力。</p>
            <a href="#" class="btn btn-competition" style="margin-top: 15px;">查看比赛</a>
        </div>
        
        <div class="card card-learning">
            <h3 class="card-title">学习规划</h3>
            <p>根据学生兴趣和能力，制定个性化学习路径，设定阶段性目标，追踪学习进度。</p>
            <a href="plans/learning_plans.php" class="btn btn-learning" style="margin-top: 15px;">开始规划</a>
        </div>
    </div>
</section>

<!-- 荣誉墙预览 -->
<section class="home-honor-container">
    <h2 class="home-honor-title">学生荣誉墙</h2>
    <p class="home-honor-subtitle">展示我们优秀学生的成就和荣誉</p>
    
    <div class="honor-wall">
        <?php if (count($recentHonors) > 0): ?>
            <?php foreach ($recentHonors as $honor): ?>
                <div class="honor-card">
                    <div class="home-honor-divider"></div>
                    <span class="home-honor-date"><?php echo date('Y-m-d', strtotime($honor['honor_date'])); ?></span>
                    <div class="home-honor-title-wrapper">
                        <h3 class="honor-title"><?php echo htmlspecialchars($honor['honor_title']); ?></h3>
                    </div>
                    <div class="honor-meta">
                        <span class="badge badge-primary">获得者: <?php echo htmlspecialchars($honor['student_name']); ?></span>
                        <?php if (!empty($honor['honor_type'])): ?>
                            <span class="badge badge-blue"><?php echo htmlspecialchars($honor['honor_type']); ?></span>
                        <?php else: ?>
                            <span class="badge badge-blue">证书</span>
                        <?php endif; ?>
                    </div>
                    <p>恭喜获得此项荣誉！继续保持，再接再厉！</p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-data">暂无荣誉数据</div>
        <?php endif; ?>
    </div>
    
    <div class="home-honor-more">
        <a href="learning/honor_wall.php" class="home-honor-btn">查看更多荣誉</a>
    </div>
</section>

<?php
// 页面特定脚本
$extraScripts = <<<HTML
<script>
    // 如果有需要的JavaScript，可以在这里添加
</script>
HTML;

include TEMPLATES_PATH . '/footer.php';
?> 