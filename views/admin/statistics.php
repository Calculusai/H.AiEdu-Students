<?php
/**
 * 数据统计页面
 */
include_once VIEW_PATH . '/header.php';

// 设置面包屑导航
$breadcrumbs = [
    ['title' => '首页', 'url' => site_url()],
    ['title' => '管理控制台', 'url' => site_url('admin/dashboard')],
    ['title' => '数据统计', 'active' => true]
];
include_once VIEW_PATH . '/templates/breadcrumb.php';
?>

<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="h3 mb-3"><i class="bi bi-bar-chart-line me-2"></i>数据统计</h1>
        </div>
        <div class="col-md-6 text-end">
            <button id="printStatistics" class="btn btn-outline-secondary">
                <i class="bi bi-printer me-1"></i> 打印报表
            </button>
        </div>
    </div>
    
    <div class="row">
        <!-- 总览卡片 -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">学生总数</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['students']; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-people fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">成就总数</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['achievements']; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-award fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">成就类型数</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['achievement_types']; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-list-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">平均成就数/学生</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['avg_achievements_per_student']; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-graph-up fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- 成就类型分布 -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">成就类型分布</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($stats['achievement_by_type'])): ?>
                    <div class="text-center my-4">
                        <i class="bi bi-exclamation-circle text-muted" style="font-size: 2rem;"></i>
                        <p class="mt-2 text-muted">暂无成就数据</p>
                    </div>
                    <?php else: ?>
                    <div class="chart-container" style="height: 300px;">
                        <canvas id="achievementTypeChart"></canvas>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- 月度成就趋势 -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">月度成就趋势</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($stats['achievements_by_month'])): ?>
                    <div class="text-center my-4">
                        <i class="bi bi-exclamation-circle text-muted" style="font-size: 2rem;"></i>
                        <p class="mt-2 text-muted">暂无成就数据</p>
                    </div>
                    <?php else: ?>
                    <div class="chart-container" style="height: 300px;">
                        <canvas id="achievementMonthlyChart"></canvas>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- 成就最多的学生 -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">成就最多的学生</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($stats['top_students'])): ?>
                    <div class="text-center my-4">
                        <i class="bi bi-exclamation-circle text-muted" style="font-size: 2rem;"></i>
                        <p class="mt-2 text-muted">暂无成就数据</p>
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>排名</th>
                                    <th>学生姓名</th>
                                    <th>班级</th>
                                    <th>成就数量</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($stats['top_students'] as $index => $student): ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td><?php echo htmlspecialchars($student['name']); ?></td>
                                    <td><?php echo htmlspecialchars($student['class_name'] ?: '未设置'); ?></td>
                                    <td><?php echo $student['achievement_count']; ?></td>
                                    <td>
                                        <a href="<?php echo site_url('student/' . $student['id']); ?>" class="btn btn-sm btn-outline-primary">
                                            查看成就
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
        
        <!-- 最新成就 -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">最新成就</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($stats['recent_achievements'])): ?>
                    <div class="text-center my-4">
                        <i class="bi bi-exclamation-circle text-muted" style="font-size: 2rem;"></i>
                        <p class="mt-2 text-muted">暂无成就数据</p>
                    </div>
                    <?php else: ?>
                    <div class="list-group">
                        <?php foreach ($stats['recent_achievements'] as $achievement): ?>
                        <div class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1"><?php echo htmlspecialchars($achievement['title']); ?></h6>
                                <small class="text-muted"><?php echo date('Y-m-d', strtotime($achievement['achieved_date'])); ?></small>
                            </div>
                            <p class="mb-1">
                                <span class="badge bg-primary"><?php echo htmlspecialchars($achievement['achievement_type']); ?></span>
                                <span class="ms-2"><?php echo htmlspecialchars($achievement['student_name']); ?></span>
                            </p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 打印报表
    document.getElementById('printStatistics').addEventListener('click', function() {
        window.print();
    });
    
    <?php if (!empty($stats['achievement_by_type'])): ?>
    // 成就类型分布图
    const typeCtx = document.getElementById('achievementTypeChart').getContext('2d');
    const typeLabels = <?php echo json_encode(array_keys($stats['achievement_by_type'])); ?>;
    const typeData = <?php echo json_encode(array_values($stats['achievement_by_type'])); ?>;
    
    new Chart(typeCtx, {
        type: 'pie',
        data: {
            labels: typeLabels,
            datasets: [{
                data: typeData,
                backgroundColor: [
                    '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#5a5c69',
                    '#6f42c1', '#fd7e14', '#20c997', '#6610f2'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right'
                }
            }
        }
    });
    <?php endif; ?>
    
    <?php if (!empty($stats['achievements_by_month'])): ?>
    // 月度成就趋势图
    const monthlyCtx = document.getElementById('achievementMonthlyChart').getContext('2d');
    const monthlyLabels = <?php echo json_encode(array_keys($stats['achievements_by_month'])); ?>;
    const monthlyData = <?php echo json_encode(array_values($stats['achievements_by_month'])); ?>;
    
    new Chart(monthlyCtx, {
        type: 'line',
        data: {
            labels: monthlyLabels,
            datasets: [{
                label: '成就数量',
                data: monthlyData,
                borderColor: '#4e73df',
                backgroundColor: 'rgba(78, 115, 223, 0.05)',
                pointBackgroundColor: '#4e73df',
                tension: 0.3,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            }
        }
    });
    <?php endif; ?>
});
</script>

<?php include_once VIEW_PATH . '/footer.php'; ?> 