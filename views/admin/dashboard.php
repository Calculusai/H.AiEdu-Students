<?php
/**
 * 管理员仪表盘视图
 */
include_once VIEW_PATH . '/header.php';

// 设置面包屑导航
$breadcrumbs = [
    ['title' => '首页', 'url' => site_url()],
    ['title' => '管理控制台', 'active' => true]
];
include_once VIEW_PATH . '/templates/breadcrumb.php';
?>

<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="h3 mb-3"><i class="fas fa-tachometer-alt me-2"></i>管理员控制台</h1>
            <p class="text-muted">欢迎回来，<?php echo htmlspecialchars($_SESSION['username'] ?? '管理员'); ?>！</p>
        </div>
    </div>
    
    <div class="row mb-4">
        <!-- 学生统计卡片 -->
        <div class="col-md-4 mb-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">学生数量</div>
                            <div class="h5 mb-0 font-weight-bold"><?php echo isset($stats['students']) ? number_format($stats['students']) : 0; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="<?php echo site_url('admin/students'); ?>" class="text-primary">查看详情 <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
        </div>
        
        <!-- 成就统计卡片 -->
        <div class="col-md-4 mb-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">成就数量</div>
                            <div class="h5 mb-0 font-weight-bold"><?php echo isset($stats['achievements']) ? number_format($stats['achievements']) : 0; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-award fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="<?php echo site_url('admin/achievements'); ?>" class="text-success">查看详情 <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
        </div>
        
        <!-- 成就类型统计卡片 -->
        <div class="col-md-4 mb-3">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">成就类型</div>
                            <div class="h5 mb-0 font-weight-bold"><?php echo isset($stats['achievement_types']) ? number_format($stats['achievement_types']) : 0; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="<?php echo site_url('admin/achievements'); ?>" class="text-info">查看详情 <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- 最新成就 -->
        <div class="col-md-12 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">最新添加的成就</h6>
                    <a href="<?php echo site_url('admin/achievements'); ?>" class="btn btn-sm btn-primary">
                        查看全部
                    </a>
                </div>
                <div class="card-body">
                    <?php if (!isset($stats['newest_achievements']) || empty($stats['newest_achievements'])): ?>
                        <p class="text-center py-3">暂无成就数据</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" width="100%" cellspacing="0">
                                <thead>
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
                                            <td><?php echo htmlspecialchars($achievement['student_name']); ?></td>
                                            <td><?php echo htmlspecialchars($achievement['title']); ?></td>
                                            <td><span class="badge bg-primary"><?php echo htmlspecialchars($achievement['achievement_type']); ?></span></td>
                                            <td><?php echo htmlspecialchars($achievement['achieved_date']); ?></td>
                                            <td>
                                                <a href="<?php echo site_url('admin/achievements/edit/'.$achievement['id']); ?>" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-edit"></i>
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
    
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">快速操作</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <a href="<?php echo site_url('admin/add_student'); ?>" class="btn btn-primary btn-block">
                                <i class="fas fa-user-plus mr-2"></i> 添加学生
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="<?php echo site_url('admin/add_achievement'); ?>" class="btn btn-success btn-block">
                                <i class="fas fa-plus-circle mr-2"></i> 添加成就
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="<?php echo site_url('admin/statistics'); ?>" class="btn btn-warning btn-block">
                                <i class="fas fa-chart-bar mr-2"></i> 数据统计
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="<?php echo site_url('admin/settings'); ?>" class="btn btn-info btn-block">
                                <i class="fas fa-cog mr-2"></i> 系统设置
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once VIEW_PATH . '/footer.php'; ?> 