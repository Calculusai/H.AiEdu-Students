<?php
/**
 * 学生个人资料和成就页面
 */
include_once VIEW_PATH . '/header.php';

// 获取学生ID
$student_id = isset($params['id']) ? (int)$params['id'] : 0;

if (empty($student_id)) {
    include_once VIEW_PATH . '/404.php';
    exit;
}

// 加载学生模型
require_once MODEL_PATH . '/Student.php';
$studentModel = new Student();

// 获取学生信息
$student = $studentModel->getStudentWithInfo($student_id);

if (empty($student)) {
    include_once VIEW_PATH . '/404.php';
    exit;
}

// 加载成就模型
require_once MODEL_PATH . '/Achievement.php';
$achievementModel = new Achievement();

// 获取筛选条件
$type = isset($_GET['type']) ? sanitize_input($_GET['type']) : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 6; // 每页显示数量

// 获取学生成就
$filters = [
    'type' => $type,
    'student_id' => $student_id,
    'order_by' => 'a.achieved_date',
    'order' => 'DESC'
];
$achievements = $achievementModel->getAchievementsWithFilters($page, $per_page, $filters);

// 获取成就类型列表（用于筛选）
$achievementTypes = $achievementModel->getAchievementTypes();

// 获取学生的总成就数和分类统计
$achievementStats = $achievementModel->getStudentAchievementStats($student_id);
?>

<div class="container py-5">
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card glass animate-float card-gradient-border mb-4">
                <div class="card-body text-center p-4">
                    <div class="badge-icon bg-gradient-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width:100px;height:100px">
                        <i class="bi bi-person-circle" style="font-size: 3.5rem;"></i>
                    </div>
                    <h2 class="card-title gradient-text mb-2"><?php echo htmlspecialchars($student['name']); ?></h2>
                    <p class="mb-4"><?php echo htmlspecialchars($student['class_name'] ?: '未设置班级'); ?></p>
                    
                    <div class="d-flex justify-content-center gap-4 mt-3">
                        <div class="text-center">
                            <div class="badge-icon bg-gradient-secondary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2" style="width:50px;height:50px">
                                <i class="bi bi-award fs-4"></i>
                            </div>
                            <h3 class="fw-bold gradient-text animate-pulse"><?php echo $achievementStats['total']; ?></h3>
                            <p class="mb-0 small">总成就</p>
                        </div>
                        <div class="text-center">
                            <div class="badge-icon bg-gradient-info text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2" style="width:50px;height:50px">
                                <i class="bi bi-grid-3x3-gap fs-4"></i>
                            </div>
                            <h3 class="fw-bold gradient-text animate-pulse"><?php echo count($achievementStats['types']); ?></h3>
                            <p class="mb-0 small">成就类型</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card glass animate-float card-gradient-border mb-4">
                <div class="card-header glass border-0">
                    <h5 class="card-title mb-0 gradient-text">学生资料</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($student['notes'])): ?>
                    <div class="mb-4">
                        <h6 class="text-muted">简介</h6>
                        <div class="p-3 glass btn-rounded">
                            <p class="mb-0"><?php echo nl2br(htmlspecialchars($student['notes'])); ?></p>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <h6 class="text-muted mb-3">成就分布</h6>
                        <?php if (!empty($achievementStats['types'])): ?>
                            <?php foreach ($achievementStats['types'] as $type => $count): ?>
                            <div class="d-flex justify-content-between align-items-center glass p-2 btn-rounded mb-2 hover-scale">
                                <span class="ms-2"><?php echo htmlspecialchars($type); ?></span>
                                <span class="badge bg-gradient-primary rounded-pill btn-shine"><?php echo $count; ?></span>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center p-4 glass btn-rounded">
                                <i class="bi bi-emoji-frown fs-4 mb-2 d-block"></i>
                                <p class="mb-0">暂无成就数据</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="card glass animate-float card-gradient-border">
                <div class="card-header glass border-0">
                    <h5 class="card-title mb-0 gradient-text">筛选成就</h5>
                </div>
                <div class="card-body">
                    <form action="<?php echo site_url('student/' . $student_id); ?>" method="get">
                        <div class="mb-3">
                            <label for="type" class="form-label">成就类型</label>
                            <select class="form-select btn-rounded" id="type" name="type">
                                <option value="">全部类型</option>
                                <?php foreach ($achievementTypes as $achievementType): ?>
                                <option value="<?php echo htmlspecialchars($achievementType); ?>" <?php echo $type === $achievementType ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($achievementType); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-rounded btn-shine hover-scale">
                                <i class="bi bi-filter me-2"></i>应用筛选
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card glass animate-float card-gradient-border mb-4">
                <div class="card-header glass border-0 d-flex justify-content-between align-items-center">
                    <h3 class="mb-0 gradient-text">成就展示</h3>
                    <a href="<?php echo site_url('achievements'); ?>" class="btn btn-primary btn-rounded btn-shine hover-scale">
                        <i class="bi bi-trophy me-1"></i> 查看所有成就
                    </a>
                </div>
                
                <div class="card-body">
                    <?php if (empty($achievements['data'])): ?>
                    <div class="alert glass text-center p-4">
                        <div class="badge-icon bg-warning text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width:60px;height:60px">
                            <i class="bi bi-info-circle fs-4"></i>
                        </div>
                        <p class="mb-0">暂无符合条件的成就记录</p>
                    </div>
                    <?php else: ?>
                    
                    <div class="row row-cols-1 row-cols-md-2 g-4">
                        <?php foreach ($achievements['data'] as $achievement): ?>
                        <div class="col">
                            <div class="card h-100 glass hover-scale card-gradient-border">
                                <div class="card-header glass border-0 p-3">
                                    <span class="badge bg-gradient-primary mb-2 btn-shine"><?php echo htmlspecialchars($achievement['achievement_type']); ?></span>
                                    <h5 class="card-title gradient-text mb-0"><?php echo htmlspecialchars($achievement['title']); ?></h5>
                                </div>
                                
                                <div class="card-body p-3">
                                    <?php if (!empty($achievement['description'])): ?>
                                    <p class="card-text"><?php echo htmlspecialchars($achievement['description']); ?></p>
                                    <?php endif; ?>
                                    
                                    <div class="mt-3">
                                        <?php if (!empty($achievement['score'])): ?>
                                        <div class="mb-1"><small class="text-muted">评分/等级: </small><span class="gradient-text"><?php echo htmlspecialchars($achievement['score']); ?></span></div>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($achievement['issue_authority'])): ?>
                                        <div class="mb-1"><small class="text-muted">颁发机构: </small><span class="gradient-text"><?php echo htmlspecialchars($achievement['issue_authority']); ?></span></div>
                                        <?php endif; ?>
                                        
                                        <div><small class="text-muted">获得日期: </small><span class="gradient-text"><?php echo date('Y-m-d', strtotime($achievement['achieved_date'])); ?></span></div>
                                    </div>
                                </div>
                                
                                <?php if (!empty($achievement['attachment'])): ?>
                                <div class="card-footer bg-transparent glass border-0 p-3">
                                    <?php 
                                    $fileExt = pathinfo($achievement['attachment'], PATHINFO_EXTENSION);
                                    $isImage = in_array(strtolower($fileExt), ['jpg', 'jpeg', 'png', 'gif']);
                                    if ($isImage):
                                    ?>
                                    <a href="#" class="btn btn-primary btn-rounded btn-shine hover-scale w-100" data-bs-toggle="modal" data-bs-target="#certificateModal-<?php echo $achievement['id']; ?>">
                                        <i class="bi bi-file-earmark-image me-1"></i> 查看证书
                                    </a>
                                    <!-- 证书图片模态框 -->
                                    <div class="modal fade" id="certificateModal-<?php echo $achievement['id']; ?>" tabindex="-1" aria-labelledby="certificateModalLabel-<?php echo $achievement['id']; ?>" aria-hidden="true">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content glass">
                                                <div class="modal-header glass border-0">
                                                    <h5 class="modal-title gradient-text" id="certificateModalLabel-<?php echo $achievement['id']; ?>"><?php echo htmlspecialchars($achievement['title']); ?> - 证书</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body text-center glass">
                                                    <img src="<?php echo site_url('uploads/' . $achievement['attachment']); ?>" class="img-fluid certificate-image btn-shine" alt="证书">
                                                </div>
                                                <div class="modal-footer glass border-0">
                                                    <a href="<?php echo site_url('uploads/' . $achievement['attachment']); ?>" class="btn btn-primary btn-rounded btn-shine hover-scale" target="_blank" download>
                                                        <i class="bi bi-download me-1"></i> 下载证书
                                                    </a>
                                                    <button type="button" class="btn btn-secondary btn-rounded" data-bs-dismiss="modal">关闭</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php else: ?>
                                    <a href="<?php echo site_url('uploads/' . $achievement['attachment']); ?>" class="btn btn-primary btn-rounded btn-shine hover-scale w-100" target="_blank">
                                        <i class="bi bi-file-earmark me-1"></i> 查看证书
                                    </a>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- 分页 -->
                    <div class="d-flex justify-content-center mt-4">
                        <?php
                        $query_params = [];
                        if (!empty($type)) $query_params['type'] = $type;
                        
                        $url_pattern = 'student/' . $student_id . '?page=%d';
                        if (!empty($query_params)) {
                            $url_pattern .= '&' . http_build_query($query_params);
                        }
                        
                        echo get_pagination($achievements['total'], $per_page, $page, $url_pattern);
                        ?>
                    </div>
                    
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.avatar-placeholder {
    width: 100px;
    height: 100px;
    margin: 0 auto;
    border-radius: 50%;
    background-color: rgba(233, 236, 239, 0.3);
    backdrop-filter: blur(5px);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
}

.achievement-timeline .card {
    position: relative;
    border-left: 4px solid transparent;
    background-image: linear-gradient(to right, var(--primary-color), transparent);
    background-size: 4px 100%;
    background-repeat: no-repeat;
}

.achievement-timeline .card::before {
    content: '';
    position: absolute;
    width: 16px;
    height: 16px;
    background: var(--primary-color);
    border-radius: 50%;
    left: -10px;
    top: 20px;
    box-shadow: 0 0 10px rgba(var(--primary-rgb), 0.7);
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(var(--primary-rgb), 0.7);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(var(--primary-rgb), 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(var(--primary-rgb), 0);
    }
}

.certificate-image {
    max-width: 100%;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    border-radius: 8px;
    transition: all 0.3s ease;
}

.certificate-image:hover {
    transform: scale(1.02);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.modal-content {
    border-radius: 16px;
    overflow: hidden;
}

.modal-body {
    padding: 1.5rem;
    background-color: rgba(248, 249, 250, 0.1);
}

.modal-lg {
    max-width: 800px;
}
</style>

<?php include_once VIEW_PATH . '/footer.php'; ?> 