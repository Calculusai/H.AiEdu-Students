<?php
/**
 * 管理员仪表盘视图 - 现代多巴胺风格
 */
include_once VIEW_PATH . '/header.php';

// 设置面包屑导航
$breadcrumbs = [
    ['title' => '首页', 'url' => site_url()],
    ['title' => '管理控制台', 'active' => true]
];
include_once VIEW_PATH . '/templates/breadcrumb.php';
?>

<div class="container py-4">
    <!-- 欢迎卡片 -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card glass animate-float">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div>
                            <h1 class="gradient-text mb-2 animate-pulse">管理控制台</h1>
                            <p class="lead mb-0">欢迎回来，<?php echo htmlspecialchars($_SESSION['username'] ?? '管理员'); ?>！</p>
                        </div>
                        <div class="d-flex gap-2 mt-3 mt-md-0">
                            <a href="<?php echo site_url('admin/settings'); ?>" class="btn btn-sm btn-primary btn-rounded btn-shine">
                                <i class="bi bi-gear-fill me-1"></i> 系统设置
                            </a>
                            <a href="<?php echo site_url('admin/statistics'); ?>" class="btn btn-sm btn-secondary btn-rounded">
                                <i class="bi bi-bar-chart-fill me-1"></i> 数据分析
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 数据统计卡片 -->
    <div class="row g-4 mb-4">
        <!-- 学生统计卡片 -->
        <div class="col-md-4">
            <div class="card stat-card border-0 hover-scale">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon stat-icon-student">
                            <i class="bi bi-people-fill fs-4"></i>
                        </div>
                        <div class="ms-3">
                            <div class="small text-muted">学生数量</div>
                            <div class="fs-2 fw-bold"><?php echo isset($stats['students']) ? number_format($stats['students']) : 0; ?></div>
                        </div>
                        <div class="ms-auto">
                            <span class="badge stat-badge stat-badge-student">学生</span>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0 pt-0 pb-3 px-4">
                    <a href="<?php echo site_url('admin/students'); ?>" class="btn btn-sm btn-primary btn-rounded w-100 btn-shine">
                        <i class="bi bi-arrow-right me-2"></i>管理学生
                    </a>
                </div>
            </div>
        </div>
        
        <!-- 成就统计卡片 -->
        <div class="col-md-4">
            <div class="card stat-card border-0 hover-scale">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon stat-icon-achievement">
                            <i class="bi bi-award-fill fs-4"></i>
                        </div>
                        <div class="ms-3">
                            <div class="small text-muted">成就记录</div>
                            <div class="fs-2 fw-bold"><?php echo isset($stats['achievements']) ? number_format($stats['achievements']) : 0; ?></div>
                        </div>
                        <div class="ms-auto">
                            <span class="badge stat-badge stat-badge-achievement">成就</span>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0 pt-0 pb-3 px-4">
                    <a href="<?php echo site_url('admin/achievements'); ?>" class="btn btn-sm btn-primary btn-rounded w-100 btn-shine">
                        <i class="bi bi-arrow-right me-2"></i>管理成就
                    </a>
                </div>
            </div>
        </div>
        
        <!-- 成就类型统计卡片 -->
        <div class="col-md-4">
            <div class="card stat-card border-0 hover-scale">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon stat-icon-type">
                            <i class="bi bi-grid-3x3-gap-fill fs-4"></i>
                        </div>
                        <div class="ms-3">
                            <div class="small text-muted">成就类型</div>
                            <div class="fs-2 fw-bold"><?php echo isset($stats['achievement_types']) ? number_format($stats['achievement_types']) : 0; ?></div>
                        </div>
                        <div class="ms-auto">
                            <span class="badge stat-badge stat-badge-type">类型</span>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0 pt-0 pb-3 px-4">
                    <a href="<?php echo site_url('admin/achievements'); ?>" class="btn btn-sm btn-primary btn-rounded w-100 btn-shine">
                        <i class="bi bi-arrow-right me-2"></i>查看类型
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 快速操作区 -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card glass">
                <div class="card-body p-4">
                    <h5 class="gradient-text mb-4">快速操作</h5>
                    <div class="row g-3">
                        <div class="col-6 col-md-3">
                            <a href="<?php echo site_url('admin/students/add'); ?>" class="card card-gradient h-100 hover-scale text-decoration-none border-0">
                                <div class="card-body p-4 text-center">
                                    <div class="badge-icon bg-gradient-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3 animate-float" style="width:56px;height:56px">
                                        <i class="bi bi-person-plus-fill fs-4"></i>
                                    </div>
                                    <h6 class="mb-0">添加学生</h6>
                                </div>
                            </a>
                        </div>
                        <div class="col-6 col-md-3">
                            <a href="<?php echo site_url('admin/achievements/add'); ?>" class="card card-gradient h-100 hover-scale text-decoration-none border-0">
                                <div class="card-body p-4 text-center">
                                    <div class="badge-icon bg-gradient-secondary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3 animate-float" style="width:56px;height:56px">
                                        <i class="bi bi-plus-circle-fill fs-4"></i>
                                    </div>
                                    <h6 class="mb-0">添加成就</h6>
                                </div>
                            </a>
                        </div>
                        <div class="col-6 col-md-3">
                            <a href="<?php echo site_url('admin/statistics'); ?>" class="card card-gradient h-100 hover-scale text-decoration-none border-0">
                                <div class="card-body p-4 text-center">
                                    <div class="badge-icon bg-warning text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3 animate-float" style="width:56px;height:56px">
                                        <i class="bi bi-bar-chart-fill fs-4"></i>
                                    </div>
                                    <h6 class="mb-0">数据统计</h6>
                                </div>
                            </a>
                        </div>
                        <div class="col-6 col-md-3">
                            <a href="<?php echo site_url('admin/settings'); ?>" class="card card-gradient h-100 hover-scale text-decoration-none border-0">
                                <div class="card-body p-4 text-center">
                                    <div class="badge-icon bg-info text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3 animate-float" style="width:56px;height:56px">
                                        <i class="bi bi-gear-fill fs-4"></i>
                                    </div>
                                    <h6 class="mb-0">系统设置</h6>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 最新成就区域 -->
    <div class="row">
        <div class="col-12">
            <div class="card glass">
                <div class="card-header d-flex justify-content-between align-items-center bg-transparent border-bottom-0 p-4">
                    <h5 class="gradient-text mb-0">最新添加的成就</h5>
                    <a href="<?php echo site_url('admin/achievements'); ?>" class="btn btn-sm btn-primary btn-rounded btn-shine">
                        查看全部 <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
                <div class="card-body p-4">
                    <?php if (!isset($stats['newest_achievements']) || empty($stats['newest_achievements'])): ?>
                        <div class="text-center py-5">
                            <div class="badge-icon bg-light text-muted rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3 animate-pulse" style="width:72px;height:72px">
                                <i class="bi bi-inbox fs-1"></i>
                            </div>
                            <h5 class="text-muted mb-3">暂无成就数据</h5>
                            <a href="<?php echo site_url('admin/achievements/add'); ?>" class="btn btn-primary btn-rounded btn-shine">
                                <i class="bi bi-plus-circle me-2"></i>添加成就
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-borderless table-hover align-middle">
                                <thead class="text-muted small">
                                    <tr>
                                        <th>学生</th>
                                        <th>成就名称</th>
                                        <th>类型</th>
                                        <th>获得日期</th>
                                        <th>操作</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($stats['newest_achievements'] as $achievement): ?>
                                        <tr>
                                            <td class="fw-medium"><?php echo htmlspecialchars($achievement['student_name']); ?></td>
                                            <td><?php echo htmlspecialchars($achievement['title']); ?></td>
                                            <td><span class="badge bg-primary"><?php echo htmlspecialchars($achievement['achievement_type']); ?></span></td>
                                            <td><?php echo htmlspecialchars($achievement['achieved_date']); ?></td>
                                            <td>
                                                <a href="<?php echo site_url('admin/achievements/edit/'.$achievement['id']); ?>" class="btn btn-sm btn-primary btn-rounded btn-shine">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 添加一些用于增强动画效果的JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 为卡片添加进入动画
    const statCards = document.querySelectorAll('.stat-card');
    statCards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        setTimeout(() => {
            card.style.transition = 'all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1)';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, 100 + (index * 100));
    });
});
</script>

<?php include_once VIEW_PATH . '/footer.php'; ?> 