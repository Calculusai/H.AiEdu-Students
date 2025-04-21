<?php
/**
 * 管理员查看学生详情视图
 */
include_once VIEW_PATH . '/header.php';

// 引入学生详情页面的特定样式
echo '<link rel="stylesheet" href="' . site_url('assets/css/student-detail.css') . '">';

// 设置面包屑导航
$breadcrumbs = [
    ['title' => '首页', 'url' => site_url()],
    ['title' => '管理控制台', 'url' => site_url('admin/dashboard')],
    ['title' => '学生管理', 'url' => site_url('admin/students')],
    ['title' => '查看学生', 'active' => true]
];
include_once VIEW_PATH . '/templates/breadcrumb.php';
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card glass animate-float">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h1 class="gradient-text mb-2 animate-pulse"><i class="fas fa-user me-2"></i>学生详情</h1>
                            <p class="lead text-muted">查看学生 <strong><?php echo htmlspecialchars($student['name']); ?></strong> 的完整信息</p>
                        </div>
                        <div>
                            <a href="<?php echo site_url('admin/students/edit/' . $student['id']); ?>" class="btn btn-primary btn-shine me-2">
                                <i class="fas fa-edit me-1"></i> 编辑信息
                            </a>
                            <a href="<?php echo site_url('admin/students'); ?>" class="btn btn-outline-primary btn-shine">
                                <i class="fas fa-arrow-left me-1"></i> 返回列表
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card glass shadow-sm mb-4 border-0 overflow-hidden">
                <div class="card-header bg-gradient-primary-to-secondary py-3 border-0">
                    <h5 class="text-white m-0 fw-bold"><i class="fas fa-info-circle me-2"></i>基本信息</h5>
                </div>
                <div class="card-body p-4 position-relative bg-light bg-opacity-75">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="info-card">
                                <div class="info-card-icon bg-primary text-white">
                                    <i class="fas fa-id-card"></i>
                                </div>
                                <div class="info-card-content">
                                    <div class="info-card-label">学号</div>
                                    <div class="info-card-value"><?php echo htmlspecialchars($student['student_id']); ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-card">
                                <div class="info-card-icon bg-info text-white">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div class="info-card-content">
                                    <div class="info-card-label">姓名</div>
                                    <div class="info-card-value"><?php echo htmlspecialchars($student['name']); ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-card">
                                <div class="info-card-icon bg-success text-white">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div class="info-card-content">
                                    <div class="info-card-label">班级</div>
                                    <div class="info-card-value"><?php echo htmlspecialchars($student['class_name'] ?? '未设置'); ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-card">
                                <div class="info-card-icon bg-warning text-white">
                                    <i class="fas fa-phone"></i>
                                </div>
                                <div class="info-card-content">
                                    <div class="info-card-label">联系方式</div>
                                    <div class="info-card-value"><?php echo htmlspecialchars($student['contact'] ?? '未设置'); ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="info-card">
                                <div class="info-card-icon bg-secondary text-white">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div class="info-card-content">
                                    <div class="info-card-label">邮箱</div>
                                    <div class="info-card-value"><?php echo htmlspecialchars($student['email'] ?? '未设置'); ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="info-card p-0">
                                <div class="info-card-label ps-3 pt-2 pb-1 border-bottom">备注</div>
                                <div class="info-card-value p-3 bg-white">
                                    <?php echo !empty($student['notes']) ? nl2br(htmlspecialchars($student['notes'])) : '<span class="text-muted">暂无备注</span>'; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-shape"></div>
                </div>
            </div>
            
            <div class="card glass shadow-sm mb-4">
                <div class="card-header bg-transparent py-3 border-bottom-0 d-flex justify-content-between align-items-center">
                    <h5 class="gradient-text m-0"><i class="fas fa-award me-2"></i>学生成就</h5>
                    <a href="<?php echo site_url('admin/students/achievements/' . $student['id']); ?>" class="btn btn-sm btn-outline-primary rounded-pill">
                        <i class="fas fa-eye me-1"></i> 查看全部
                    </a>
                </div>
                <div class="card-body p-4">
                    <?php if (empty($student_achievements)): ?>
                    <div class="text-center py-4">
                        <div class="avatar-circle bg-warning-soft mx-auto mb-3">
                            <i class="fas fa-award fa-2x text-warning"></i>
                        </div>
                        <h6 class="mb-2">尚未获得成就</h6>
                        <p class="text-muted small">该学生目前还没有获得任何成就。</p>
                        <a href="<?php echo site_url('admin/achievements/add'); ?>?student_id=<?php echo $student['id']; ?>" class="btn btn-sm btn-gradient btn-shine mt-2">
                            <i class="fas fa-plus me-1"></i> 添加成就
                        </a>
                    </div>
                    <?php else: ?>
                    <div class="achievements-grid">
                        <?php foreach ($student_achievements as $achievement): ?>
                        <div class="achievement-item animate-hover">
                            <div class="achievement-icon">
                                <i class="<?php echo htmlspecialchars($achievement['icon'] ?? 'fas fa-trophy'); ?>"></i>
                            </div>
                            <div class="achievement-info">
                                <h6 class="mb-0"><?php echo htmlspecialchars($achievement['title']); ?></h6>
                                <small class="text-muted">
                                    <?php echo date('Y-m-d', strtotime($achievement['achieved_date'])); ?>
                                </small>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card glass shadow-sm mb-4 border-0 overflow-hidden">
                <div class="card-header bg-gradient-primary-to-secondary py-3 border-0">
                    <h5 class="text-white m-0 fw-bold"><i class="fas fa-user-shield me-2"></i>账号信息</h5>
                </div>
                <div class="card-body p-4 position-relative bg-light bg-opacity-75">
                    <div class="account-status mb-4">
                        <div class="account-status-label">账号状态</div>
                        <div class="account-status-indicator">
                            <?php if (!isset($student['status']) || $student['status'] == 1): ?>
                            <div class="status-badge active">
                                <div class="status-dot"></div>
                                <span>已启用</span>
                            </div>
                            <?php else: ?>
                            <div class="status-badge inactive">
                                <div class="status-dot"></div>
                                <span>已禁用</span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="time-info mb-3">
                        <div class="time-box">
                            <div class="time-icon">
                                <i class="fas fa-calendar-plus"></i>
                            </div>
                            <div class="time-details">
                                <div class="time-label">注册时间</div>
                                <div class="time-value"><?php echo isset($student['created_at']) ? date('Y-m-d H:i', strtotime($student['created_at'])) : '未知'; ?></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="time-info">
                        <div class="time-box">
                            <div class="time-icon">
                                <i class="fas fa-sign-in-alt"></i>
                            </div>
                            <div class="time-details">
                                <div class="time-label">最后登录</div>
                                <div class="time-value"><?php echo isset($student['last_login']) ? date('Y-m-d H:i', strtotime($student['last_login'])) : '从未登录'; ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-shape right"></div>
                </div>
            </div>
            
            <div class="card glass shadow-sm mb-4 animate-float-delay-2">
                <div class="card-header bg-transparent py-3 border-bottom-0">
                    <h5 class="gradient-text m-0"><i class="fas fa-chart-bar me-2"></i>统计数据</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="card h-100 bg-light-opacity rounded-4 border-0">
                                <div class="card-body text-center p-3">
                                    <div class="avatar-circle bg-primary-soft mx-auto mb-2">
                                        <i class="fas fa-trophy fa-lg text-primary"></i>
                                    </div>
                                    <h3 class="mb-0 gradient-text"><?php echo isset($stats['achievements_count']) ? $stats['achievements_count'] : 0; ?></h3>
                                    <p class="text-muted small mb-0">成就总数</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="card h-100 bg-light-opacity rounded-4 border-0">
                                <div class="card-body text-center p-3">
                                    <div class="avatar-circle bg-success-soft mx-auto mb-2">
                                        <i class="fas fa-star fa-lg text-success"></i>
                                    </div>
                                    <h3 class="mb-0 gradient-text"><?php echo isset($stats['total_points']) ? $stats['total_points'] : 0; ?></h3>
                                    <p class="text-muted small mb-0">获得积分</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card glass shadow-sm mb-4 animate-float-delay-3">
                <div class="card-header bg-transparent py-3 border-bottom-0">
                    <h5 class="gradient-text m-0"><i class="fas fa-history me-2"></i>活动记录</h5>
                </div>
                <div class="card-body p-4">
                    <?php if (empty($activity_logs)): ?>
                    <div class="text-center py-4">
                        <div class="avatar-circle bg-info-soft mx-auto mb-3">
                            <i class="fas fa-history fa-2x text-info"></i>
                        </div>
                        <h6 class="mb-2">没有活动记录</h6>
                        <p class="text-muted small">该学生暂时没有任何活动记录。</p>
                    </div>
                    <?php else: ?>
                    <div class="timeline">
                        <?php foreach ($activity_logs as $log): ?>
                        <div class="timeline-item">
                            <div class="timeline-dot"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1 small"><?php echo htmlspecialchars($log['action']); ?></h6>
                                <div class="text-muted smaller">
                                    <i class="far fa-clock me-1"></i> <?php echo date('Y-m-d H:i', strtotime($log['created_at'])); ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.avatar-circle {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}
.bg-primary-soft {
    background-color: rgba(var(--primary-rgb), 0.1);
}
.bg-success-soft {
    background-color: rgba(25, 135, 84, 0.1);
}
.bg-warning-soft {
    background-color: rgba(255, 193, 7, 0.1);
}
.bg-info-soft {
    background-color: rgba(13, 202, 240, 0.1);
}
.bg-light-opacity {
    background-color: rgba(240, 242, 245, 0.5);
}
.gradient-text {
    background: linear-gradient(45deg, var(--primary-color), #6f42c1);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    color: transparent;
}
.btn-gradient {
    background: linear-gradient(45deg, var(--primary-color), #6f42c1);
    border: none;
    color: white;
}
.animate-float-delay {
    animation-delay: 0.2s;
}
.animate-float-delay-2 {
    animation-delay: 0.4s;
}
.animate-float-delay-3 {
    animation-delay: 0.6s;
}
.achievements-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 10px;
}
.achievement-item {
    display: flex;
    align-items: center;
    padding: 12px;
    border-radius: 12px;
    background-color: rgba(255, 255, 255, 0.5);
    transition: all 0.3s ease;
}
.achievement-item:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
}
.achievement-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 10px;
    background: linear-gradient(45deg, var(--primary-color), #6f42c1);
    color: white;
}
.achievement-info {
    flex: 1;
}
.timeline {
    position: relative;
    padding-left: 25px;
}
.timeline::before {
    content: '';
    position: absolute;
    left: 5px;
    top: 5px;
    bottom: 5px;
    width: 2px;
    background: linear-gradient(to bottom, var(--primary-color), rgba(var(--primary-rgb), 0.1));
}
.timeline-item {
    position: relative;
    margin-bottom: 16px;
}
.timeline-dot {
    position: absolute;
    left: -25px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: linear-gradient(45deg, var(--primary-color), #6f42c1);
    top: 6px;
}
.timeline-content {
    padding-bottom: 5px;
}

/* 新增多巴胺风格样式 */
.bg-gradient-primary-to-secondary {
    background: linear-gradient(135deg, #4776E6 0%, #8E54E9 100%);
}

.info-card {
    display: flex;
    background-color: #fff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0,0,0,0.03);
    transition: all 0.3s ease;
}

.info-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 16px rgba(0,0,0,0.06);
}

.info-card-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 60px;
    height: 60px;
    flex-shrink: 0;
    font-size: 1.4rem;
}

.info-card-content {
    flex: 1;
    padding: 12px 16px;
}

.info-card-label {
    font-size: 12px;
    color: #808080;
    margin-bottom: 3px;
}

.info-card-value {
    font-weight: 600;
    color: #333;
    font-size: 16px;
}

.bg-shape {
    position: absolute;
    width: 200px;
    height: 200px;
    background: linear-gradient(135deg, rgba(142, 84, 233, 0.1) 0%, rgba(71, 118, 230, 0.1) 100%);
    border-radius: 50%;
    bottom: -80px;
    left: -80px;
    z-index: 0;
}

.bg-shape.right {
    left: auto;
    right: -80px;
    top: -80px;
    bottom: auto;
}

.account-status {
    background-color: #fff;
    border-radius: 12px;
    padding: 16px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.03);
}

.account-status-label {
    font-size: 12px;
    color: #808080;
    margin-bottom: 8px;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    padding: 6px 12px;
    border-radius: 30px;
    font-weight: 600;
    font-size: 14px;
}

.status-badge.active {
    background-color: rgba(25, 135, 84, 0.1);
    color: #198754;
}

.status-badge.inactive {
    background-color: rgba(220, 53, 69, 0.1);
    color: #dc3545;
}

.status-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    margin-right: 8px;
}

.status-badge.active .status-dot {
    background-color: #198754;
    box-shadow: 0 0 0 3px rgba(25, 135, 84, 0.2);
    animation: pulse 2s infinite;
}

.status-badge.inactive .status-dot {
    background-color: #dc3545;
}

.time-info {
    margin-bottom: 16px;
}

.time-box {
    display: flex;
    background-color: #fff;
    border-radius: 12px;
    overflow: hidden;
    padding: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.03);
    transition: all 0.3s ease;
}

.time-box:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 16px rgba(0,0,0,0.06);
}

.time-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #4776E6 0%, #8E54E9 100%);
    border-radius: 10px;
    margin-right: 12px;
    color: white;
}

.time-details {
    flex: 1;
}

.time-label {
    font-size: 12px;
    color: #808080;
    margin-bottom: 3px;
}

.time-value {
    font-weight: 600;
    color: #333;
    font-size: 14px;
}

@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(25, 135, 84, 0.4);
    }
    70% {
        box-shadow: 0 0 0 6px rgba(25, 135, 84, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(25, 135, 84, 0);
    }
}
</style>

<?php include_once VIEW_PATH . '/footer.php'; ?> 