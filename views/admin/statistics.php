<?php
/**
 * 数据统计页面 - 现代多巴胺风格
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

<div class="container py-4">
    <!-- 顶部信息卡片 -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card glass animate-float">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div>
                            <h1 class="gradient-text mb-2 animate-pulse">数据统计</h1>
                            <p class="lead mb-0">查看系统数据统计和分析图表，了解使用情况和趋势。</p>
                        </div>
                        <button id="printStatistics" class="btn btn-outline-primary btn-rounded btn-shine hover-scale mt-3 mt-md-0">
                            <i class="bi bi-printer me-2"></i>打印报表
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 数据统计卡片 -->
    <div class="row g-4 mb-4">
        <!-- 学生总数卡片 -->
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card border-0 hover-scale">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon stat-icon-student">
                            <i class="bi bi-people-fill fs-4"></i>
                        </div>
                        <div class="ms-3">
                            <div class="small text-muted">学生总数</div>
                            <div class="fs-2 fw-bold"><?php echo $stats['students']; ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 成就总数卡片 -->
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card border-0 hover-scale">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon stat-icon-achievement">
                            <i class="bi bi-award-fill fs-4"></i>
                        </div>
                        <div class="ms-3">
                            <div class="small text-muted">成就总数</div>
                            <div class="fs-2 fw-bold"><?php echo $stats['achievements']; ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 成就类型数卡片 -->
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card border-0 hover-scale">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon stat-icon-type">
                            <i class="bi bi-grid-3x3-gap-fill fs-4"></i>
                        </div>
                        <div class="ms-3">
                            <div class="small text-muted">成就类型数</div>
                            <div class="fs-2 fw-bold"><?php echo $stats['achievement_types']; ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 平均成就/学生卡片 -->
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card border-0 hover-scale">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon" style="background-color: #FF9F43;">
                            <i class="bi bi-graph-up fs-4"></i>
                        </div>
                        <div class="ms-3">
                            <div class="small text-muted">平均成就数/学生</div>
                            <div class="fs-2 fw-bold"><?php echo $stats['avg_achievements_per_student']; ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 图表区域 -->
    <div class="row g-4 mb-4">
        <!-- 成就类型分布 -->
        <div class="col-lg-6">
            <div class="card glass">
                <div class="card-header d-flex justify-content-between align-items-center bg-transparent border-bottom-0 p-4">
                    <h5 class="gradient-text mb-0">成就类型分布</h5>
                </div>
                <div class="card-body p-4">
                    <?php if (empty($stats['achievement_by_type'])): ?>
                    <div class="text-center py-5">
                        <div class="badge-icon bg-light text-muted rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3 animate-pulse" style="width:72px;height:72px">
                            <i class="bi bi-bar-chart fs-1"></i>
                        </div>
                        <h5 class="text-muted mb-3">暂无成就数据</h5>
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
        <div class="col-lg-6">
            <div class="card glass">
                <div class="card-header d-flex justify-content-between align-items-center bg-transparent border-bottom-0 p-4">
                    <h5 class="gradient-text mb-0">月度成就趋势</h5>
                </div>
                <div class="card-body p-4">
                    <?php if (empty($stats['achievements_by_month'])): ?>
                    <div class="text-center py-5">
                        <div class="badge-icon bg-light text-muted rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3 animate-pulse" style="width:72px;height:72px">
                            <i class="bi bi-bar-chart-line fs-1"></i>
                        </div>
                        <h5 class="text-muted mb-3">暂无成就数据</h5>
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
    
    <!-- 学生和成就数据区域 -->
    <div class="row g-4">
        <!-- 成就最多的学生 -->
        <div class="col-lg-6">
            <div class="card glass">
                <div class="card-header d-flex justify-content-between align-items-center bg-transparent border-bottom-0 p-4">
                    <h5 class="gradient-text mb-0">成就最多的学生</h5>
                </div>
                <div class="card-body p-4">
                    <?php if (empty($stats['top_students'])): ?>
                    <div class="text-center py-5">
                        <div class="badge-icon bg-light text-muted rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3 animate-pulse" style="width:72px;height:72px">
                            <i class="bi bi-people fs-1"></i>
                        </div>
                        <h5 class="text-muted mb-3">暂无成就数据</h5>
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-borderless table-hover align-middle">
                            <thead class="text-muted small">
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
                                <tr class="student-row hover-scale-sm">
                                    <td>
                                        <span class="badge rounded-pill <?php echo $index < 3 ? ($index === 0 ? 'bg-warning text-dark' : ($index === 1 ? 'bg-secondary text-white' : 'bg-danger text-white')) : 'bg-light text-dark'; ?>">
                                            <?php echo $index + 1; ?>
                                        </span>
                                    </td>
                                    <td class="fw-medium"><?php echo htmlspecialchars($student['name']); ?></td>
                                    <td><?php echo htmlspecialchars($student['class_name'] ?: '未设置'); ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="progress flex-grow-1" style="height: 8px; width: 100px; border-radius: 4px; overflow: hidden;">
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo min(100, ($student['achievement_count'] / max(1, $stats['max_achievements'])) * 100); ?>%" aria-valuenow="<?php echo $student['achievement_count']; ?>" aria-valuemin="0" aria-valuemax="<?php echo $stats['max_achievements']; ?>"></div>
                                            </div>
                                            <span class="ms-2 fw-medium"><?php echo $student['achievement_count']; ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="<?php echo site_url('admin/students/achievements/' . $student['id']); ?>" class="btn btn-sm btn-primary btn-rounded btn-shine">
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
        <div class="col-lg-6">
            <div class="card glass">
                <div class="card-header d-flex justify-content-between align-items-center bg-transparent border-bottom-0 p-4">
                    <h5 class="gradient-text mb-0">最新成就</h5>
                </div>
                <div class="card-body p-4">
                    <?php if (empty($stats['recent_achievements'])): ?>
                    <div class="text-center py-5">
                        <div class="badge-icon bg-light text-muted rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3 animate-pulse" style="width:72px;height:72px">
                            <i class="bi bi-trophy fs-1"></i>
                        </div>
                        <h5 class="text-muted mb-3">暂无成就数据</h5>
                    </div>
                    <?php else: ?>
                    <div class="list-group">
                        <?php foreach ($stats['recent_achievements'] as $achievement): ?>
                        <div class="list-group-item border-0 rounded-4 mb-2 p-3 hover-scale-sm">
                            <div class="d-flex w-100 justify-content-between align-items-center">
                                <h6 class="mb-1"><?php echo htmlspecialchars($achievement['title']); ?></h6>
                                <small class="text-muted"><?php echo date('m-d', strtotime($achievement['achieved_date'])); ?></small>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="d-block"><?php echo htmlspecialchars($achievement['student_name']); ?></small>
                                    <span class="badge bg-primary"><?php echo htmlspecialchars($achievement['achievement_type']); ?></span>
                                </div>
                                <a href="<?php echo site_url('admin/students/achievements/' . $achievement['student_id']); ?>" class="btn btn-sm btn-outline-primary btn-rounded btn-shine hover-scale">
                                    <i class="bi bi-eye"></i>
                                </a>
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

<!-- 统计图表初始化脚本 -->
<?php if (!empty($stats['achievement_by_type']) || !empty($stats['achievements_by_month'])): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 设置图表字体和颜色
    Chart.defaults.font.family = "'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif";
    Chart.defaults.color = '#6c757d';
    
    <?php if (!empty($stats['achievement_by_type'])): ?>
    // 成就类型分布图
    const typeCtx = document.getElementById('achievementTypeChart').getContext('2d');
    const typeChart = new Chart(typeCtx, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode(array_keys($stats['achievement_by_type'])); ?>,
            datasets: [{
                data: <?php echo json_encode(array_values($stats['achievement_by_type'])); ?>,
                backgroundColor: [
                    '#7367F0', '#FF9F43', '#FF6B9A', '#39A2DB', '#28C76F', '#EA5455', '#9C8AFF', '#00CFE8'
                ],
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true,
                        pointStyle: 'circle'
                    }
                }
            },
            cutout: '70%',
            animation: {
                animateScale: true,
                animateRotate: true
            }
        }
    });
    <?php endif; ?>
    
    <?php if (!empty($stats['achievements_by_month'])): ?>
    // 月度成就趋势图
    const monthlyCtx = document.getElementById('achievementMonthlyChart').getContext('2d');
    const monthlyChart = new Chart(monthlyCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode(array_keys($stats['achievements_by_month'])); ?>,
            datasets: [{
                label: '成就数量',
                data: <?php echo json_encode(array_values($stats['achievements_by_month'])); ?>,
                backgroundColor: 'rgba(115, 103, 240, 0.2)',
                borderColor: '#7367F0',
                borderWidth: 2,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#7367F0',
                pointBorderColor: '#fff',
                pointHoverBackgroundColor: '#fff',
                pointHoverBorderColor: '#7367F0',
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        drawBorder: false
                    },
                    ticks: {
                        precision: 0
                    }
                }
            },
            animation: {
                duration: 1000,
                easing: 'easeOutQuart'
            }
        }
    });
    <?php endif; ?>
    
    // 打印功能
    document.getElementById('printStatistics').addEventListener('click', function() {
        window.print();
    });

    // 动画效果
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

    const studentRows = document.querySelectorAll('.student-row');
    studentRows.forEach((row, index) => {
        row.style.opacity = '0';
        row.style.transform = 'translateY(10px)';
        setTimeout(() => {
            row.style.transition = 'all 0.3s ease-out';
            row.style.opacity = '1';
            row.style.transform = 'translateY(0)';
        }, 50 + (index * 30));
    });
});
</script>
<?php endif; ?>

<style>
/* 行悬停微缩放效果 */
.hover-scale-sm {
    transition: transform 0.2s ease, background-color 0.2s ease;
}
.hover-scale-sm:hover {
    transform: scale(1.01);
    background-color: rgba(99, 102, 241, 0.03);
}

/* 为打印定制的样式 */
@media print {
    .navbar, .breadcrumb, .footer, button, .btn {
        display: none !important;
    }
    
    .card {
        box-shadow: none !important;
        border: 1px solid #ddd !important;
    }
    
    .container {
        width: 100% !important;
        max-width: 100% !important;
        padding: 0 !important;
    }
    
    body {
        font-size: 12pt;
        background: white !important;
        padding: 0 !important;
        margin: 0 !important;
    }
    
    .gradient-text {
        background: none !important;
        -webkit-background-clip: initial !important;
        -webkit-text-fill-color: #000 !important;
        color: #000 !important;
    }
}
</style>

<?php include_once VIEW_PATH . '/footer.php'; ?> 