<?php
$page_title = '首页';
$active_page = 'home';
include_once VIEW_PATH . '/header.php';
?>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4 dopamine-card">
            <div class="card-body">
                <h1 class="mb-4">欢迎来到少儿编程成就展示系统</h1>
                <p class="lead">记录和展示少儿编程学习的成就与荣誉</p>
                <p>本系统旨在为6-16岁青少年提供编程学习成就展示平台，激励学生持续进步，记录学习历程。</p>
                <div class="mt-4">
                    <a href="<?php echo site_url('achievements'); ?>" class="btn btn-primary dopamine-button me-2">
                        <i class="bi bi-trophy me-2"></i>查看成就展示
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
        <h2 class="mb-3">最新成就</h2>
        <div class="row">
            <?php foreach ($achievements as $achievement): ?>
            <div class="col-md-6 mb-4">
                <div class="card h-100 achievement-card">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($achievement['title']); ?></h5>
                        <h6 class="card-subtitle mb-2 text-muted">
                            <?php echo htmlspecialchars($achievement['student_name']); ?>
                        </h6>
                        <p class="card-text">
                            <?php echo strlen($achievement['description']) > 100 ? substr(htmlspecialchars($achievement['description']), 0, 100) . '...' : htmlspecialchars($achievement['description']); ?>
                        </p>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <span class="badge bg-primary"><?php echo htmlspecialchars($achievement['achievement_type']); ?></span>
                            <small class="text-muted"><?php echo date('Y-m-d', strtotime($achievement['achieved_date'])); ?></small>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center mt-4">
            <a href="<?php echo site_url('achievements'); ?>" class="btn btn-outline-primary">
                查看更多成就 <i class="bi bi-arrow-right"></i>
            </a>
        </div>
        <?php endif; ?>
    </div>
    
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">为什么记录成就？</h5>
            </div>
            <div class="card-body">
                <ul class="list-unstyled">
                    <li class="mb-3">
                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                        <strong>激励学习</strong> - 记录成就能提高学习积极性
                    </li>
                    <li class="mb-3">
                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                        <strong>树立信心</strong> - 可视化进步过程增强自信心
                    </li>
                    <li class="mb-3">
                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                        <strong>展示能力</strong> - 向他人展示自己的编程能力
                    </li>
                    <li class="mb-3">
                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                        <strong>记录成长</strong> - 为未来保存学习历程记录
                    </li>
                </ul>
            </div>
        </div>
        
        <div class="card achievement-types-card">
            <div class="card-header">
                <h5 class="card-title mb-0">编程成就类型</h5>
            </div>
            <div class="card-body">
                <div class="achievement-type mb-3">
                    <div class="d-flex align-items-center">
                        <div class="badge-icon me-3">
                            <i class="bi bi-file-earmark-code text-primary fs-3"></i>
                        </div>
                        <div>
                            <h6 class="mb-1">编程考试成绩</h6>
                            <p class="small text-muted mb-0">各类编程考试和评测的成绩记录</p>
                        </div>
                    </div>
                </div>
                
                <div class="achievement-type mb-3">
                    <div class="d-flex align-items-center">
                        <div class="badge-icon me-3">
                            <i class="bi bi-award text-warning fs-3"></i>
                        </div>
                        <div>
                            <h6 class="mb-1">编程证书</h6>
                            <p class="small text-muted mb-0">官方认证的编程能力证书</p>
                        </div>
                    </div>
                </div>
                
                <div class="achievement-type mb-3">
                    <div class="d-flex align-items-center">
                        <div class="badge-icon me-3">
                            <i class="bi bi-trophy text-success fs-3"></i>
                        </div>
                        <div>
                            <h6 class="mb-1">竞赛获奖</h6>
                            <p class="small text-muted mb-0">编程比赛和竞赛中获得的奖项</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once VIEW_PATH . '/footer.php'; ?> 