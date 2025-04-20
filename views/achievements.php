<?php
/**
 * 公共成就展示页面
 */
include_once VIEW_PATH . '/header.php';

// 获取筛选条件
$type = isset($_GET['type']) ? sanitize_input($_GET['type']) : '';
$student_id = isset($_GET['student_id']) ? (int)$_GET['student_id'] : 0;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 12; // 每页显示数量

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
?>

<div class="container mt-5 mb-5">
    <h1 class="text-center mb-4">成就展示</h1>
    
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <form action="<?php echo site_url('achievements'); ?>" method="get" class="row g-3">
                        <div class="col-md-5">
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
                        <div class="col-md-5">
                            <label for="student_id" class="form-label">学生</label>
                            <select class="form-select" id="student_id" name="student_id">
                                <option value="">全部学生</option>
                                <?php foreach ($students as $student): ?>
                                <option value="<?php echo $student['id']; ?>" <?php echo $student_id === (int)$student['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($student['name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">筛选</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <?php if (empty($achievements['data'])): ?>
    <div class="alert alert-info">
        <i class="bi bi-info-circle me-2"></i> 暂无符合条件的成就记录
    </div>
    <?php else: ?>
    
    <div class="row row-cols-1 row-cols-md-3 g-4">
        <?php foreach ($achievements['data'] as $achievement): ?>
        <div class="col">
            <div class="card h-100 dopamine-card">
                <div class="card-body">
                    <span class="badge bg-primary mb-2"><?php echo htmlspecialchars($achievement['achievement_type']); ?></span>
                    <h5 class="card-title"><?php echo htmlspecialchars($achievement['title']); ?></h5>
                    <h6 class="card-subtitle mb-2 text-muted">
                        <i class="bi bi-person me-1"></i> <?php echo htmlspecialchars($achievement['student_name']); ?>
                    </h6>
                    
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
        if (!empty($student_id)) $query_params['student_id'] = $student_id;
        
        $url_pattern = 'achievements?page=%d';
        if (!empty($query_params)) {
            $url_pattern .= '&' . http_build_query($query_params);
        }
        
        echo get_pagination($achievements['total'], $per_page, $page, $url_pattern);
        ?>
    </div>
    
    <?php endif; ?>
</div>

<!-- 添加一些CSS样式用于证书显示 -->
<style>
.certificate-image {
    max-width: 100%;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}
.modal-body {
    padding: 1rem;
    background-color: #f8f9fa;
}
</style>

<?php include_once VIEW_PATH . '/footer.php'; ?> 