<?php
require_once '../includes/config.php';

$pageTitle = '学习规划';

// 检查用户是否已登录
if (!isLoggedIn()) {
    // 记录当前页面URL，以便登录后重定向回来
    $_SESSION['redirect_after_login'] = getCurrentUrl();
    
    // 设置消息
    $_SESSION['message'] = '请先登录以查看您的学习规划';
    $_SESSION['message_type'] = 'info';
    
    // 重定向到登录页面
    redirect('/auth/login.php');
}

// 从数据库获取当前用户的学习规划
$userId = $_SESSION['user_id'];
$db = Database::getInstance();

$plans = [];
try {
    // 获取学生ID
    $stmt = $db->prepare("SELECT id FROM students WHERE account_id = ?");
    $stmt->execute([$userId]);
    $student = $stmt->fetch();
    
    if (!$student) {
        // 显示空记录即可，不需要抛出错误
        $plans = [];
    } else {
        $studentId = $student['id'];
        
        // 查询学习规划
        $stmt = $db->prepare("
            SELECT lp.*, s.name as student_name 
            FROM learning_plans lp
            JOIN students s ON lp.student_id = s.id
            WHERE lp.student_id = ? 
            ORDER BY lp.created_at DESC
        ");
        $stmt->execute([$studentId]);
        $plans = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    // 如果有错误，记录日志，但可以正常显示页面
    error_log("学习规划查询错误: " . $e->getMessage());
}

// 自动更新学习规划状态（基于时间的计算）
function updatePlanStatus() {
    global $db;
    $today = date('Y-m-d');
    
    try {
        // 更新已逾期的计划
        $db->exec("UPDATE learning_plans SET status = '已逾期' 
                 WHERE end_date < '$today' AND progress < 100 AND status != '已完成'");
                 
        // 更新进行中的计划
        $db->exec("UPDATE learning_plans SET status = '进行中' 
                 WHERE start_date <= '$today' AND (end_date >= '$today' OR end_date IS NULL) 
                 AND status = '未开始'");
                 
        // 如果进度为100%，自动设置为已完成
        $db->exec("UPDATE learning_plans SET status = '已完成' 
                 WHERE progress = 100 AND status != '已完成'");
    } catch (PDOException $e) {
        error_log("自动更新学习规划状态错误: " . $e->getMessage());
    }
}

// 自动计算时间进度百分比
function calculateProgressByDate($startDate, $endDate) {
    if (empty($startDate) || empty($endDate)) {
        return null; // 无法计算
    }
    
    $start = strtotime($startDate);
    $end = strtotime($endDate);
    $now = time();
    
    if ($now <= $start) return 0; // 还未开始
    if ($now >= $end) return 100; // 已经结束
    
    $totalDuration = $end - $start;
    $elapsedDuration = $now - $start;
    
    return min(100, round(($elapsedDuration / $totalDuration) * 100));
}

// 获取状态对应的样式类
function getStatusClass($status) {
    switch ($status) {
        case '已完成':
            return 'success';
        case '进行中':
            return 'primary';
        case '未开始':
            return 'info';
        case '已逾期':
            return 'danger';
        default:
            return 'secondary';
    }
}

// 执行自动状态更新
updatePlanStatus();

// 页面特定样式
$extraStyles = <<<HTML
<style>
    .page-header {
        background: linear-gradient(135deg, var(--green), var(--blue));
        color: white;
        padding: var(--space-md) 0;
        margin-bottom: var(--space-lg);
        border-radius: var(--radius-xl);
        text-align: center;
    }
    
    .plan-container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: var(--space-md);
        margin-bottom: var(--space-lg);
    }
    
    .plan-card {
        background-color: white;
        border-radius: var(--radius-xl);
        box-shadow: 0 10px 20px var(--shadow-color);
        overflow: hidden;
        transition: all 0.3s ease;
        border-top: 5px solid var(--green);
    }
    
    .plan-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px var(--shadow-color);
    }
    
    .plan-header {
        padding: var(--space-md);
        border-bottom: 1px solid var(--border-color);
    }
    
    .plan-title {
        color: var(--green);
        margin-bottom: var(--space-xs);
        font-weight: 700;
    }
    
    .plan-meta {
        color: var(--text-secondary);
        font-size: var(--font-small);
    }
    
    .plan-body {
        padding: var(--space-md);
    }
    
    .plan-goal {
        margin-bottom: var(--space-sm);
    }
    
    .plan-goal strong {
        color: var(--text-primary);
    }
    
    .progress-bar {
        height: 12px;
        background-color: rgba(0, 224, 158, 0.1);
        border-radius: 6px;
        margin: var(--space-sm) 0;
        overflow: hidden;
    }
    
    .progress-fill {
        height: 100%;
        background: linear-gradient(to right, var(--green), var(--blue));
        border-radius: 6px;
        transition: width 0.5s ease;
    }
    
    .progress-info {
        display: flex;
        justify-content: space-between;
        color: var(--text-secondary);
        font-size: var(--font-small);
    }
    
    .plan-result {
        margin-top: var(--space-md);
        padding-top: var(--space-sm);
        border-top: 1px dashed var(--border-color);
    }
    
    .plan-result strong {
        color: var(--text-primary);
    }
    
    .plan-footer {
        padding: var(--space-sm) var(--space-md);
        background-color: rgba(0, 224, 158, 0.05);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .plan-date {
        color: var(--text-secondary);
        font-size: var(--font-small);
    }
    
    .new-plan-btn {
        text-align: center;
        margin-bottom: var(--space-lg);
    }
    
    .empty-state {
        text-align: center;
        padding: var(--space-lg);
        background-color: white;
        border-radius: var(--radius-lg);
        box-shadow: 0 5px 15px var(--shadow-color);
        margin-bottom: var(--space-lg);
    }
    
    .empty-state h3 {
        color: var(--green);
        margin-bottom: var(--space-sm);
    }
    
    .date-info {
        display: flex;
        align-items: center;
        margin-top: var(--space-sm);
        font-size: var(--font-small);
        color: var(--text-secondary);
    }
    
    .date-info i {
        margin-right: 5px;
        color: var(--text-secondary);
    }
    
    .status-badge {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 500;
    }
    
    .status-badge.success {
        background-color: #E3F9E5;
        color: #31A24C;
    }
    
    .status-badge.primary {
        background-color: #E1F0FF;
        color: #1976D2;
    }
    
    .status-badge.info {
        background-color: #E8F4FD;
        color: #0288D1;
    }
    
    .status-badge.danger {
        background-color: #FFEFEF;
        color: #F44336;
    }
    
    .status-badge.secondary {
        background-color: #F5F5F5;
        color: #757575;
    }
</style>
HTML;

include TEMPLATES_PATH . '/header.php';
?>

<!-- 页面标题 -->
<div class="page-header">
    <div class="container">
        <h1>我的学习规划</h1>
        <p>查看和管理您的学习目标和进度</p>
    </div>
</div>

<!-- 新建规划按钮 -->
<div class="new-plan-btn">
    <a href="../plans/add_plan.php" class="btn btn-learning">创建新的学习规划</a>
</div>

<!-- 学习规划列表 -->
<?php if (empty($plans)): ?>
    <div class="empty-state">
        <h3>暂无学习规划</h3>
        <p>您还没有创建任何学习规划，点击上方按钮开始创建您的第一个学习规划吧！</p>
    </div>
<?php else: ?>
    <div class="plan-container">
        <?php foreach ($plans as $plan): ?>
            <div class="plan-card">
                <div class="plan-header">
                    <h3 class="plan-title"><?php echo htmlspecialchars($plan['plan_title']); ?></h3>
                    <div class="plan-meta">
                        <span class="badge badge-green">学习规划</span>
                        <span class="status-badge <?php echo getStatusClass($plan['status'] ?? '进行中'); ?>">
                            <?php echo htmlspecialchars($plan['status'] ?? ($plan['progress'] >= 100 ? '已完成' : '进行中')); ?>
                        </span>
                        <span class="badge badge-purple">
                            <?php echo htmlspecialchars($plan['student_name']); ?>
                        </span>
                    </div>
                </div>
                
                <div class="plan-body">
                    <div class="plan-goal">
                        <strong>目标：</strong>
                        <p><?php echo htmlspecialchars($plan['goal']); ?></p>
                    </div>
                    
                    <div>
                        <strong>当前进度：</strong>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?php echo $plan['progress']; ?>%;"></div>
                        </div>
                        <div class="progress-info">
                            <span>进度: <?php echo $plan['progress']; ?>%</span>
                            <span><?php echo $plan['progress'] >= 100 ? '已完成' : '继续努力！'; ?></span>
                        </div>
                    </div>
                    
                    <?php if (!empty($plan['start_date']) || !empty($plan['end_date'])): ?>
                        <div class="date-info">
                            <?php if (!empty($plan['start_date'])): ?>
                                <i class="fas fa-calendar-alt"></i> 开始日期: <?php echo date('Y-m-d', strtotime($plan['start_date'])); ?>
                            <?php endif; ?>
                            
                            <?php if (!empty($plan['end_date'])): ?>
                                <span style="margin: 0 8px;">-</span>
                                <i class="fas fa-calendar-check"></i> 结束日期: <?php echo date('Y-m-d', strtotime($plan['end_date'])); ?>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (!empty($plan['start_date']) && !empty($plan['end_date'])): ?>
                            <div style="margin-top: 5px; font-size: var(--font-small); color: var(--text-secondary);">
                                <?php 
                                    $timeProgress = calculateProgressByDate($plan['start_date'], $plan['end_date']); 
                                    if ($timeProgress !== null): 
                                ?>
                                    <small>基于时间的进度: <?php echo $timeProgress; ?>%</small>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <?php if (!empty($plan['result'])): ?>
                        <div class="plan-result">
                            <strong>阶段成果：</strong>
                            <p><?php echo htmlspecialchars($plan['result']); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="plan-footer">
                    <div class="plan-date">
                        创建于: <?php echo date('Y-m-d', strtotime($plan['created_at'])); ?>
                    </div>
                    <a href="update_plan.php?id=<?php echo $plan['id']; ?>" class="btn btn-secondary btn-sm">更新进度</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php
// 页面特定脚本
$extraScripts = <<<HTML
<script>
    // 如果有需要的JavaScript，可以在这里添加
</script>
HTML;

include TEMPLATES_PATH . '/footer.php';
?> 