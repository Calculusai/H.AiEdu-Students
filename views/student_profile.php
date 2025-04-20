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

<div class="container mt-5 mb-5">
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card dopamine-card mb-4">
                <div class="card-body text-center">
                    <div class="avatar-placeholder mb-3">
                        <i class="bi bi-person-circle" style="font-size: 5rem;"></i>
                    </div>
                    <h2 class="card-title"><?php echo htmlspecialchars($student['name']); ?></h2>
                    <p class="text-muted"><?php echo htmlspecialchars($student['class_name'] ?: '未设置班级'); ?></p>
                    
                    <div class="d-flex justify-content-center mt-4">
                        <div class="px-3 text-center">
                            <h3 class="fw-bold text-primary"><?php echo $achievementStats['total']; ?></h3>
                            <p class="text-muted mb-0">总成就</p>
                        </div>
                        <div class="px-3 text-center">
                            <h3 class="fw-bold text-success"><?php echo count($achievementStats['types']); ?></h3>
                            <p class="text-muted mb-0">成就类型</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">学生资料</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($student['notes'])): ?>
                    <div class="mb-3">
                        <h6 class="text-muted">简介</h6>
                        <p><?php echo nl2br(htmlspecialchars($student['notes'])); ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <h6 class="text-muted">成就分布</h6>
                        <?php if (!empty($achievementStats['types'])): ?>
                            <?php foreach ($achievementStats['types'] as $type => $count): ?>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span><?php echo htmlspecialchars($type); ?></span>
                                <span class="badge bg-primary rounded-pill"><?php echo $count; ?></span>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted">暂无成就数据</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">筛选成就</h5>
                </div>
                <div class="card-body">
                    <form action="<?php echo site_url('student/' . $student_id); ?>" method="get">
                        <div class="mb-3">
                            <label for="type" class="form-label">成就类型</label>
                            <select class="form-select" id="type" name="type">
                                <option value="">全部类型</option>
                                <?php foreach ($achievementTypes as $achievementType): ?>
                                <option value="<?php echo htmlspecialchars($achievementType); ?>" <?php echo $type === $achievementType ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($achievementType); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">应用筛选</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3>成就展示</h3>
                <a href="<?php echo site_url('achievements'); ?>" class="btn btn-outline-primary">
                    <i class="bi bi-trophy me-1"></i> 查看所有成就
                </a>
            </div>
            
            <?php if (empty($achievements['data'])): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i> 暂无符合条件的成就记录
            </div>
            <?php else: ?>
            
            <div class="row row-cols-1 row-cols-md-2 g-4">
                <?php foreach ($achievements['data'] as $achievement): ?>
                <div class="col">
                    <div class="card h-100 dopamine-card">
                        <div class="card-body">
                            <span class="badge bg-primary mb-2"><?php echo htmlspecialchars($achievement['achievement_type']); ?></span>
                            <h5 class="card-title"><?php echo htmlspecialchars($achievement['title']); ?></h5>
                            
                            <?php if (!empty($achievement['description'])): ?>
                            <p class="card-text"><?php echo htmlspecialchars($achievement['description']); ?></p>
                            <?php endif; ?>
                            
                            <div class="mt-3">
                                <?php if (!empty($achievement['score'])): ?>
                                <div class="mb-1"><small class="text-muted">评分/等级: </small><span><?php echo htmlspecialchars($achievement['score']); ?></span></div>
                                <?php endif; ?>
                                
                                <?php if (!empty($achievement['issue_authority'])): ?>
                                <div class="mb-1"><small class="text-muted">颁发机构: </small><span><?php echo htmlspecialchars($achievement['issue_authority']); ?></span></div>
                                <?php endif; ?>
                                
                                <div><small class="text-muted">获得日期: </small><span><?php echo date('Y-m-d', strtotime($achievement['achieved_date'])); ?></span></div>
                            </div>
                        </div>
                        
                        <?php if (!empty($achievement['attachment'])): ?>
                        <div class="card-footer bg-transparent">
                            <?php 
                            $fileExt = pathinfo($achievement['attachment'], PATHINFO_EXTENSION);
                            $isImage = in_array(strtolower($fileExt), ['jpg', 'jpeg', 'png', 'gif']);
                            if ($isImage):
                            ?>
                            <a href="#" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#certificateModal-<?php echo $achievement['id']; ?>">
                                <i class="bi bi-file-earmark-image me-1"></i> 查看证书
                            </a>
                            <!-- 证书图片模态框 -->
                            <div class="modal fade" id="certificateModal-<?php echo $achievement['id']; ?>" tabindex="-1" aria-labelledby="certificateModalLabel-<?php echo $achievement['id']; ?>" aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="certificateModalLabel-<?php echo $achievement['id']; ?>"><?php echo htmlspecialchars($achievement['title']); ?> - 证书</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body text-center">
                                            <img src="<?php echo site_url('uploads/' . $achievement['attachment']); ?>" class="img-fluid certificate-image" alt="证书">
                                        </div>
                                        <div class="modal-footer">
                                            <a href="<?php echo site_url('uploads/' . $achievement['attachment']); ?>" class="btn btn-sm btn-outline-secondary" target="_blank" download>
                                                <i class="bi bi-download me-1"></i> 下载证书
                                            </a>
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">关闭</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php else: ?>
                            <a href="<?php echo site_url('uploads/' . $achievement['attachment']); ?>" class="btn btn-sm btn-outline-primary" target="_blank">
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

<style>
.avatar-placeholder {
    width: 100px;
    height: 100px;
    margin: 0 auto;
    border-radius: 50%;
    background-color: #e9ecef;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #adb5bd;
}

.achievement-timeline .card {
    position: relative;
    border-left: 4px solid var(--primary-color);
}

.achievement-timeline .card::before {
    content: '';
    position: absolute;
    width: 12px;
    height: 12px;
    background: var(--primary-color);
    border-radius: 50%;
    left: -8px;
    top: 20px;
}

.certificate-image {
    max-width: 100%;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}

.modal-body {
    padding: 1rem;
    background-color: #f8f9fa;
}

.modal-lg {
    max-width: 800px;
}
</style>

<?php include_once VIEW_PATH . '/footer.php'; ?> 