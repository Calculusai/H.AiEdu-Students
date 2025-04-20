<?php
/**
 * 学生个人成就展示页面
 */
include_once VIEW_PATH . '/header.php';
?>

<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo site_url(); ?>">首页</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo site_url('achievements'); ?>">成就展示</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($student['name']); ?></li>
                </ol>
            </nav>
        </div>
    </div>
    
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card mb-4 dopamine-card">
                <div class="card-body text-center">
                    <div class="avatar-placeholder mb-3">
                        <i class="bi bi-person-circle display-1"></i>
                    </div>
                    <h3 class="card-title"><?php echo htmlspecialchars($student['name']); ?></h3>
                    <?php if (!empty($student['grade'])): ?>
                    <p class="mb-1"><?php echo htmlspecialchars($student['grade']); ?></p>
                    <?php endif; ?>
                    <?php if (!empty($student['school'])): ?>
                    <p class="mb-1"><?php echo htmlspecialchars($student['school']); ?></p>
                    <?php endif; ?>
                </div>
                
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        成就总数
                        <span class="badge bg-primary rounded-pill"><?php echo count($achievements); ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        加入时间
                        <span class="text-muted"><?php echo date('Y-m-d', strtotime($student['created_at'])); ?></span>
                    </li>
                </ul>
            </div>
            
            <?php if (!empty($types)): ?>
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">按类型筛选</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2">
                        <a href="<?php echo site_url('student/' . $student['id']); ?>" class="btn btn-sm <?php echo empty($type) ? 'btn-primary' : 'btn-outline-primary'; ?>">
                            全部
                        </a>
                        
                        <?php foreach ($types as $type_option): ?>
                        <a href="<?php echo site_url('student/' . $student['id'] . '?type=' . urlencode($type_option)); ?>" 
                           class="btn btn-sm <?php echo ($type == $type_option) ? 'btn-primary' : 'btn-outline-primary'; ?>">
                            <?php echo htmlspecialchars($type_option); ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="col-md-8">
            <h2 class="mb-3"><?php echo htmlspecialchars($student['name']); ?> 的成就</h2>
            
            <?php if (empty($achievements)): ?>
            <div class="alert alert-info">
                <?php if (!empty($type)): ?>
                    该学生在 "<?php echo htmlspecialchars($type); ?>" 类别下没有成就记录。
                <?php else: ?>
                    该学生暂无成就记录。
                <?php endif; ?>
            </div>
            <?php else: ?>
            <div class="achievement-timeline">
                <?php foreach ($achievements as $achievement): ?>
                <div class="card mb-3 achievement-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="card-title mb-0"><?php echo htmlspecialchars($achievement['title']); ?></h5>
                            <span class="badge bg-primary"><?php echo htmlspecialchars($achievement['achievement_type']); ?></span>
                        </div>
                        
                        <?php if (!empty($achievement['description'])): ?>
                        <p class="card-text"><?php echo htmlspecialchars($achievement['description']); ?></p>
                        <?php endif; ?>
                        
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="achievement-details">
                                    <?php if (!empty($achievement['score'])): ?>
                                    <div class="mb-1 small">
                                        <strong>分数/评级:</strong> <?php echo htmlspecialchars($achievement['score']); ?>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($achievement['certificate_no'])): ?>
                                    <div class="mb-1 small">
                                        <strong>证书编号:</strong> <?php echo htmlspecialchars($achievement['certificate_no']); ?>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($achievement['issue_authority'])): ?>
                                    <div class="mb-1 small">
                                        <strong>颁发机构:</strong> <?php echo htmlspecialchars($achievement['issue_authority']); ?>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <div class="mb-1 small">
                                        <strong>获得日期:</strong> <?php echo date('Y-m-d', strtotime($achievement['achieved_date'])); ?>
                                    </div>
                                </div>
                            </div>
                            
                            <?php if (!empty($achievement['attachment'])): ?>
                            <div class="col-md-6 text-end">
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
                                                <img src="<?php echo asset_url('uploads/' . $achievement['attachment']); ?>" class="img-fluid certificate-image" alt="证书">
                                            </div>
                                            <div class="modal-footer">
                                                <a href="<?php echo asset_url('uploads/' . $achievement['attachment']); ?>" class="btn btn-sm btn-outline-secondary" target="_blank" download>
                                                    <i class="bi bi-download me-1"></i> 下载证书
                                                </a>
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">关闭</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php else: ?>
                                <a href="<?php echo asset_url('uploads/' . $achievement['attachment']); ?>" 
                                   target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-file-earmark me-1"></i> 查看证书
                                </a>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- 分页 -->
            <?php if (isset($pagination)): ?>
            <div class="mt-4">
                <?php echo $pagination; ?>
            </div>
            <?php endif; ?>
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