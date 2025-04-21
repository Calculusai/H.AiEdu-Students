<?php
$page_title = '首页';
$active_page = 'home';
include_once VIEW_PATH . '/header.php';
?>

<div class="container py-5">
    <!-- 主要内容区域 -->
    <div class="row g-4">
        <div class="col-lg-8">
            <!-- 欢迎卡片 -->
            <div class="card mb-4 glass animate-float">
                <div class="card-body p-4">
                    <h1 class="gradient-text mb-3 animate-pulse">探索编程的奇妙世界</h1>
                    <p class="lead mb-4">记录和展示少儿编程学习的成就与荣誉</p>
                    <p class="mb-4">本平台为6-16岁青少年提供编程学习成就展示空间，激励持续进步，记录每一步成长。</p>
                    <div class="d-flex gap-3 mt-4">
                        <a href="<?php echo site_url('achievements'); ?>" class="btn btn-primary btn-shine">
                            <i class="bi bi-trophy me-2"></i>探索成就展示
                    </a>
                    <?php if (!is_logged_in()): ?>
                    <a href="<?php echo site_url('login'); ?>" class="btn btn-outline-primary">
                        <i class="bi bi-box-arrow-in-right me-2"></i>用户登录
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <?php
        // 获取最新成就
        global $db;
        $achievements = [];
        
        if (SYSTEM_INSTALLED) {
            $sql = "SELECT a.*, s.name as student_name FROM " . TABLE_PREFIX . "achievements a 
                    JOIN " . TABLE_PREFIX . "students s ON a.student_id = s.id
                    ORDER BY a.created_at DESC LIMIT 6";
            $achievements = $db->queryAll($sql);
        }
        ?>
        
        <?php if (!empty($achievements)): ?>
            <h2 class="gradient-text mb-4">近期成就亮点</h2>
            <div class="row g-4">
            <?php foreach ($achievements as $achievement): ?>
                <div class="col-md-6">
                    <div class="card h-100 card-gradient">
                        <div class="card-body p-4">
                            <span class="badge badge-primary mb-2"><?php echo htmlspecialchars($achievement['achievement_type']); ?></span>
                        <h5 class="card-title"><?php echo htmlspecialchars($achievement['title']); ?></h5>
                            <h6 class="card-subtitle mb-2 text-muted d-flex align-items-center">
                                <i class="bi bi-person-circle me-2"></i><?php echo htmlspecialchars($achievement['student_name']); ?>
                        </h6>
                        <p class="card-text">
                                <?php echo strlen($achievement['description']) > 80 ? substr(htmlspecialchars($achievement['description']), 0, 80) . '...' : htmlspecialchars($achievement['description']); ?>
                        </p>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                                <small class="text-muted"><i class="bi bi-calendar3 me-1"></i><?php echo date('Y-m-d', strtotime($achievement['achieved_date'])); ?></small>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
            <div class="text-center mt-5">
                <a href="<?php echo site_url('achievements'); ?>" class="btn btn-outline-primary btn-shine">
                    探索更多精彩成就 <i class="bi bi-arrow-right ms-2"></i>
            </a>
        </div>
        <?php endif; ?>
    </div>
    
        <div class="col-lg-4">
            <!-- 为什么记录成就 -->
            <div class="card mb-4 glass">
                <div class="card-body p-4">
                    <h5 class="gradient-text mb-3">为什么记录成就？</h5>
                    <ul class="list-unstyled">
                        <li class="d-flex align-items-center mb-3">
                            <div class="badge-icon me-3 bg-gradient-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width:40px;height:40px">
                                <i class="bi bi-lightning-charge-fill"></i>
                            </div>
                            <div>
                                <strong>激励学习</strong>
                                <p class="small text-muted mb-0">可视化的进步提高学习积极性</p>
            </div>
                    </li>
                        <li class="d-flex align-items-center mb-3">
                            <div class="badge-icon me-3 bg-gradient-secondary text-white rounded-circle d-flex align-items-center justify-content-center" style="width:40px;height:40px">
                                <i class="bi bi-award-fill"></i>
                            </div>
                            <div>
                                <strong>树立信心</strong>
                                <p class="small text-muted mb-0">成就记录增强自信心与自我认同</p>
                            </div>
                    </li>
                        <li class="d-flex align-items-center mb-3">
                            <div class="badge-icon me-3 bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width:40px;height:40px">
                                <i class="bi bi-graph-up"></i>
                            </div>
                            <div>
                                <strong>展示能力</strong>
                                <p class="small text-muted mb-0">向他人展示自己的编程能力</p>
                            </div>
                    </li>
                        <li class="d-flex align-items-center">
                            <div class="badge-icon me-3 bg-info text-white rounded-circle d-flex align-items-center justify-content-center" style="width:40px;height:40px">
                                <i class="bi bi-hourglass-split"></i>
                            </div>
                            <div>
                                <strong>记录成长</strong>
                                <p class="small text-muted mb-0">为未来保存学习历程和成长轨迹</p>
                            </div>
                    </li>
                </ul>
            </div>
        </div>
        
            <!-- 编程成就类型 -->
            <div class="card glass">
                <div class="card-body p-4">
                    <h5 class="gradient-text mb-4">编程成就类型</h5>
                    
                    <div class="achievement-types-container">
                        <!-- 编程考试成绩 -->
                        <div class="achievement-type-card mb-4 d-flex align-items-center">
                            <div class="achievement-icon-wrapper rounded-circle bg-primary d-flex align-items-center justify-content-center me-3">
                                <i class="bi bi-file-earmark-text text-white"></i>
                            </div>
                            <div class="achievement-info">
                                <h6 class="achievement-title fw-bold mb-1">编程考试成绩</h6>
                                <p class="achievement-desc text-muted mb-0">各类编程考试和评测的成绩记录</p>
                            </div>
                        </div>
                        
                        <!-- 编程证书 -->
                        <div class="achievement-type-card mb-4 d-flex align-items-center">
                            <div class="achievement-icon-wrapper rounded-circle bg-danger d-flex align-items-center justify-content-center me-3">
                                <i class="bi bi-award text-white"></i>
                            </div>
                            <div class="achievement-info">
                                <h6 class="achievement-title fw-bold mb-1">编程证书</h6>
                                <p class="achievement-desc text-muted mb-0">官方认证的编程能力证书</p>
                            </div>
                        </div>
                        
                        <!-- 竞赛获奖 -->
                        <div class="achievement-type-card d-flex align-items-center">
                            <div class="achievement-icon-wrapper rounded-circle bg-warning d-flex align-items-center justify-content-center me-3">
                                <i class="bi bi-trophy text-white"></i>
                            </div>
                            <div class="achievement-info">
                                <h6 class="achievement-title fw-bold mb-1">竞赛获奖</h6>
                                <p class="achievement-desc text-muted mb-0">编程比赛和竞赛中获得的奖项</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* 编程成就类型卡片样式 */
.achievement-types-container {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.achievement-type-card {
    transition: all 0.3s ease;
    padding: 0.75rem;
    border-radius: 1rem;
}

.achievement-type-card:hover {
    background-color: rgba(255, 255, 255, 0.1);
    transform: translateX(5px);
}

.achievement-icon-wrapper {
    width: 50px;
    height: 50px;
    font-size: 1.5rem;
    background-image: var(--primary-gradient);
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

.achievement-type-card:nth-child(1) .achievement-icon-wrapper {
    background-image: linear-gradient(135deg, #6366f1, #8b5cf6);
}

.achievement-type-card:nth-child(2) .achievement-icon-wrapper {
    background-image: linear-gradient(135deg, #ec4899, #f472b6);
}

.achievement-type-card:nth-child(3) .achievement-icon-wrapper {
    background-image: linear-gradient(135deg, #f59e0b, #fbbf24);
}

.achievement-info {
    flex: 1;
}

.achievement-title {
    font-size: 1.05rem;
    color: var(--heading-color);
}

.achievement-desc {
    font-size: 0.875rem;
    color: var(--muted-color);
}
</style>

<?php include_once VIEW_PATH . '/footer.php'; ?> 