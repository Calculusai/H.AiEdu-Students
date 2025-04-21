<?php
/**
 * 公共成就展示页面 - 现代化、简约、多巴胺风格设计
 */
$page_title = '成就展示';
$active_page = 'achievements';
include_once VIEW_PATH . '/header.php';

// 引入成就页面专用CSS
echo '<link rel="stylesheet" href="' . site_url('assets/css/achievements.css') . '">';

// 获取筛选条件
$type = isset($_GET['type']) ? sanitize_input($_GET['type']) : '';
$student_id = isset($_GET['student_id']) ? (int)$_GET['student_id'] : 0;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 9; // 每页显示数量

// 加载成就模型
require_once MODEL_PATH . '/Achievement.php';
$achievementModel = new Achievement();

// 获取成就数据
$filters = [
    'type' => $type,
    'student_id' => $student_id,
    'search' => '',
    'order_by' => 'a.achieved_date',
    'order' => 'DESC'
];
$achievements = $achievementModel->getAchievementsWithFilters($page, $per_page, $filters);

// 获取成就类型列表（用于筛选）
$achievementTypes = $achievementModel->getAchievementTypes();

// 加载学生模型
require_once MODEL_PATH . '/Student.php';
$studentModel = new Student();

// 获取学生列表（用于筛选）
$students = $studentModel->all('name', 'ASC');

// 处理筛选参数
$where_clauses = [];
$params = [];

if (isset($_GET['achievement_type']) && $_GET['achievement_type'] != '') {
    $where_clauses[] = "a.achievement_type = ?";
    $params[] = $_GET['achievement_type'];
}

if (isset($_GET['student_id']) && $_GET['student_id'] != '') {
    $where_clauses[] = "a.student_id = ?";
    $params[] = $_GET['student_id'];
}

if (isset($_GET['year']) && $_GET['year'] != '') {
    $where_clauses[] = "YEAR(a.achieved_date) = ?";
    $params[] = $_GET['year'];
}

$where_sql = !empty($where_clauses) ? " WHERE " . implode(" AND ", $where_clauses) : "";

// 获取总记录数和筛选后的成就数据
$total_records = 0;
$filtered_achievements = [];

if (SYSTEM_INSTALLED) {
    // 使用Achievement模型获取总记录数和筛选数据，而不是直接使用$db
    $count_result = $achievementModel->countWithFilters($where_sql, $params);
    $total_records = $count_result ? $count_result : 0;
    
    $filtered_achievements = $achievementModel->getFilteredAchievements($where_sql, $params, $page, $per_page);
} else {
    $filtered_achievements = [];
}

$total_pages = ceil($total_records / $per_page);
?>

<div class="container py-5">
    <!-- 页面标题与介绍 -->
    <div class="achievement-header">
        <h1>成就展示</h1>
        <p>探索青少年编程学习历程中的每一个闪光时刻，见证学习成长的足迹。</p>
    </div>
    
    <div class="row g-4">
        <div class="col-lg-3">
            <!-- 筛选面板 -->
            <div class="filter-panel">
                <h5 class="mb-4 fw-bold">筛选条件</h5>
                <form id="filter-form" method="get" action="<?php echo site_url('achievements'); ?>">
                    <div class="mb-4">
                        <label for="achievement_type" class="form-label">成就类型</label>
                        <select class="form-select" id="achievement_type" name="achievement_type">
                            <option value="">全部类型</option>
                            <?php
                            foreach ($achievementTypes as $type) {
                                $selected = isset($_GET['achievement_type']) && $_GET['achievement_type'] == $type ? 'selected' : '';
                                echo '<option value="' . htmlspecialchars($type) . '" ' . $selected . '>' . htmlspecialchars($type) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <label for="student_id" class="form-label">学生</label>
                        <select class="form-select" id="student_id" name="student_id">
                            <option value="">全部学生</option>
                            <?php
                            foreach ($students as $student) {
                                $selected = isset($_GET['student_id']) && $_GET['student_id'] == $student['id'] ? 'selected' : '';
                                echo '<option value="' . $student['id'] . '" ' . $selected . '>' . htmlspecialchars($student['name']) . '</option>';
                            }
                            ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="year" class="form-label">年份</label>
                        <select class="form-select" id="year" name="year">
                            <option value="">全部年份</option>
                            <?php
                            for ($y = date('Y'); $y >= 2020; $y--) {
                                $selected = isset($_GET['year']) && $_GET['year'] == $y ? 'selected' : '';
                                echo '<option value="' . $y . '" ' . $selected . '>' . $y . '年</option>';
                            }
                            ?>
                        </select>
                    </div>
                    
                    <button type="submit" class="filter-btn w-100">
                        <i class="bi bi-filter"></i>应用筛选
                    </button>
                </form>
            </div>
        </div>
        
        <div class="col-lg-9">
            <?php if (empty($filtered_achievements)): ?>
                <!-- 空状态展示 -->
                <div class="empty-state">
                    <i class="bi bi-search"></i>
                    <h3>未找到符合条件的成就</h3>
                    <p>尝试更改筛选条件或查看全部成就记录</p>
                    <a href="<?php echo site_url('achievements'); ?>" class="btn btn-primary">
                        <i class="bi bi-arrow-repeat me-2"></i>查看全部成就
                    </a>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($filtered_achievements as $achievement): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="achievement-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <span class="badge"><?php echo htmlspecialchars($achievement['achievement_type']); ?></span>
                                    <small class="achievement-detail-date">
                                        <i class="bi bi-calendar3 me-1"></i><?php echo date('Y-m-d', strtotime($achievement['achieved_date'])); ?>
                                    </small>
                                </div>
                                
                                <h5 class="card-title"><?php echo htmlspecialchars($achievement['title']); ?></h5>
                                
                                <div class="student-info">
                                    <div class="student-avatar">
                                        <i class="bi bi-person"></i>
                                    </div>
                                    <span><?php echo htmlspecialchars($achievement['student_name']); ?></span>
                                </div>
                                
                                <p class="card-text">
                                    <?php echo strlen($achievement['description']) > 80 ? substr(htmlspecialchars($achievement['description']), 0, 80) . '...' : htmlspecialchars($achievement['description']); ?>
                                </p>
                                
                                <div class="d-flex gap-2 mt-3">
                                    <button type="button" class="view-btn view-btn-primary" data-bs-toggle="modal" data-bs-target="#achievementModal<?php echo $achievement['id']; ?>">
                                        <i class="bi bi-eye me-2"></i>查看详情
                                    </button>
                                    <?php if (!empty($achievement['attachment'])): ?>
                                    <button type="button" class="view-btn view-btn-outline" data-bs-toggle="modal" data-bs-target="#certificateModal<?php echo $achievement['id']; ?>">
                                        <i class="bi bi-award"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- 成就详情模态框 -->
                        <div class="modal fade achievement-modal" id="achievementModal<?php echo $achievement['id']; ?>" tabindex="-1" aria-labelledby="achievementModalLabel<?php echo $achievement['id']; ?>" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="achievementModalLabel<?php echo $achievement['id']; ?>"><?php echo htmlspecialchars($achievement['title']); ?></h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="d-flex justify-content-between mb-4">
                                            <span class="achievement-detail-badge"><?php echo htmlspecialchars($achievement['achievement_type']); ?></span>
                                            <span class="achievement-detail-date"><i class="bi bi-calendar3 me-1"></i><?php echo date('Y-m-d', strtotime($achievement['achieved_date'])); ?></span>
                                        </div>
                                        
                                        <div class="student-profile">
                                            <div class="student-avatar-lg">
                                                <i class="bi bi-person"></i>
                                            </div>
                                            <span class="fs-5"><?php echo htmlspecialchars($achievement['student_name']); ?></span>
                                        </div>
                                        
                                        <div class="mb-4">
                                            <h6 class="section-title">成就描述</h6>
                                            <div class="section-content">
                                                <?php echo nl2br(htmlspecialchars($achievement['description'])); ?>
                                            </div>
                                        </div>
                                        
                                        <?php if (!empty($achievement['achievement_details'])): ?>
                                        <div class="mb-4">
                                            <h6 class="section-title">成就详情</h6>
                                            <div class="section-content">
                                                <?php echo nl2br(htmlspecialchars($achievement['achievement_details'])); ?>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="modal-footer">
                                        <?php if (!empty($achievement['attachment'])): ?>
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#certificateModal<?php echo $achievement['id']; ?>" data-bs-dismiss="modal">
                                            <i class="bi bi-award me-2"></i>查看证书
                                        </button>
                                        <?php endif; ?>
                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">关闭</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- 证书模态框 -->
                        <?php if (!empty($achievement['attachment'])): ?>
                        <div class="modal fade certificate-modal" id="certificateModal<?php echo $achievement['id']; ?>" tabindex="-1" aria-labelledby="certificateModalLabel<?php echo $achievement['id']; ?>" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="certificateModalLabel<?php echo $achievement['id']; ?>"><?php echo htmlspecialchars($achievement['title']); ?> - 证书</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body text-center p-0">
                                        <img src="<?php echo site_url('uploads/' . $achievement['attachment']); ?>" class="certificate-image img-fluid" alt="证书">
                                    </div>
                                    <div class="modal-footer">
                                        <a href="<?php echo site_url('uploads/' . $achievement['attachment']); ?>" class="btn btn-primary" target="_blank" download>
                                            <i class="bi bi-download me-2"></i>下载证书
                                        </a>
                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">关闭</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- 分页 -->
                <?php if ($total_pages > 1): ?>
                <div class="d-flex justify-content-center">
                    <nav aria-label="成就分页">
                        <ul class="pagination">
                        <?php
                            // 计算分页链接
                            $url_params = $_GET;
                            unset($url_params['page']);
                            $query_string = !empty($url_params) ? '&' . http_build_query($url_params) : '';
                            
                            // 上一页
                            if ($page > 1) {
                                echo '<li class="page-item"><a class="page-link" href="?page=' . ($page - 1) . $query_string . '"><i class="bi bi-chevron-left"></i></a></li>';
                            } else {
                                echo '<li class="page-item disabled"><span class="page-link"><i class="bi bi-chevron-left"></i></span></li>';
                            }
                            
                            // 页码链接
                            $range = 2; // 当前页前后显示的页码数
                            
                            // 开始页码
                            $start_page = max(1, $page - $range);
                            
                            // 结束页码
                            $end_page = min($total_pages, $page + $range);
                            
                            // 是否显示第一页和省略号
                            if ($start_page > 1) {
                                echo '<li class="page-item"><a class="page-link" href="?page=1' . $query_string . '">1</a></li>';
                                if ($start_page > 2) {
                                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                }
                            }
                            
                            // 显示页码
                            for ($i = $start_page; $i <= $end_page; $i++) {
                                if ($i == $page) {
                                    echo '<li class="page-item active"><span class="page-link">' . $i . '</span></li>';
                                } else {
                                    echo '<li class="page-item"><a class="page-link" href="?page=' . $i . $query_string . '">' . $i . '</a></li>';
                                }
                            }
                            
                            // 是否显示最后一页和省略号
                            if ($end_page < $total_pages) {
                                if ($end_page < $total_pages - 1) {
                                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                }
                                echo '<li class="page-item"><a class="page-link" href="?page=' . $total_pages . $query_string . '">' . $total_pages . '</a></li>';
                            }
                            
                            // 下一页
                            if ($page < $total_pages) {
                                echo '<li class="page-item"><a class="page-link" href="?page=' . ($page + 1) . $query_string . '"><i class="bi bi-chevron-right"></i></a></li>';
                            } else {
                                echo '<li class="page-item disabled"><span class="page-link"><i class="bi bi-chevron-right"></i></span></li>';
                            }
                        ?>
                        </ul>
                    </nav>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include_once VIEW_PATH . '/footer.php'; ?> 