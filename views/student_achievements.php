<?php
/**
 * 学生个人成就展示页面
 */
include_once VIEW_PATH . '/header.php';
?>

<div class="container py-5">
    <div class="row mb-4">
        <div class="col-md-12">
            <nav aria-label="breadcrumb" class="animate-float">
                <ol class="breadcrumb glass p-3 btn-rounded">
                    <li class="breadcrumb-item"><a href="<?php echo site_url(); ?>" class="btn-link btn-shine">首页</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo site_url('achievements'); ?>" class="btn-link btn-shine">成就展示</a></li>
                    <li class="breadcrumb-item active gradient-text" aria-current="page"><?php echo htmlspecialchars($student['name']); ?></li>
                </ol>
            </nav>
        </div>
    </div>
    
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card glass animate-float card-gradient-border mb-4">
                <div class="card-body text-center">
                    <div class="badge-icon bg-gradient-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width:100px;height:100px">
                        <i class="bi bi-person-circle display-1"></i>
                    </div>
                    <h3 class="card-title gradient-text"><?php echo htmlspecialchars($student['name']); ?></h3>
                    <?php if (!empty($student['grade'])): ?>
                    <p class="mb-1"><?php echo htmlspecialchars($student['grade']); ?></p>
                    <?php endif; ?>
                    <?php if (!empty($student['school'])): ?>
                    <p class="mb-1"><?php echo htmlspecialchars($student['school']); ?></p>
                    <?php endif; ?>
                </div>
                
                <ul class="list-group list-group-flush glass">
                    <li class="list-group-item d-flex justify-content-between align-items-center glass border-0">
                        成就总数
                        <span class="badge bg-gradient-primary rounded-pill btn-shine"><?php echo count($achievements); ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center glass border-0">
                        加入时间
                        <span class="badge bg-gradient-secondary rounded-pill btn-shine"><?php echo date('Y-m-d', strtotime($student['created_at'])); ?></span>
                    </li>
                </ul>
            </div>
            
            <?php if (!empty($types)): ?>
            <div class="card glass animate-float card-gradient-border">
                <div class="card-header glass border-0">
                    <h5 class="card-title mb-0 gradient-text">按类型筛选</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2">
                        <a href="<?php echo site_url('student/' . $student['id']); ?>" class="btn btn-sm <?php echo empty($type) ? 'btn-primary btn-shine' : 'btn-outline-primary hover-scale'; ?> btn-rounded">
                            全部
                        </a>
                        
                        <?php foreach ($types as $type_option): ?>
                        <a href="<?php echo site_url('student/' . $student['id'] . '?type=' . urlencode($type_option)); ?>" 
                           class="btn btn-sm <?php echo ($type == $type_option) ? 'btn-primary btn-shine' : 'btn-outline-primary hover-scale'; ?> btn-rounded">
                            <?php echo htmlspecialchars($type_option); ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="col-md-8">
            <div class="card glass animate-float card-gradient-border mb-4">
                <div class="card-header glass border-0">
                    <h2 class="mb-0 gradient-text"><?php echo htmlspecialchars($student['name']); ?> 的成就</h2>
                </div>
                
                <div class="card-body">
                    <?php if (empty($achievements)): ?>
                    <div class="alert glass text-center p-4">
                        <div class="badge-icon bg-warning text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width:60px;height:60px">
                            <i class="bi bi-exclamation-circle fs-4"></i>
                        </div>
                        <?php if (!empty($type)): ?>
                            <p class="mb-0">该学生在 "<?php echo htmlspecialchars($type); ?>" 类别下没有成就记录。</p>
                        <?php else: ?>
                            <p class="mb-0">该学生暂无成就记录。</p>
                        <?php endif; ?>
                    </div>
                    <?php else: ?>
                    <div class="achievement-timeline">
                        <?php foreach ($achievements as $achievement): ?>
                        <div class="card mb-3 achievement-card glass hover-scale">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h5 class="card-title mb-0 gradient-text animate-pulse"><?php echo htmlspecialchars($achievement['title']); ?></h5>
                                    <span class="badge bg-gradient-primary btn-shine"><?php echo htmlspecialchars($achievement['achievement_type']); ?></span>
                                </div>
                                
                                <?php if (!empty($achievement['description'])): ?>
                                <p class="card-text"><?php echo htmlspecialchars($achievement['description']); ?></p>
                                <?php endif; ?>
                                
                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <div class="achievement-details">
                                            <?php if (!empty($achievement['score'])): ?>
                                            <div class="mb-1 small">
                                                <strong>分数/评级:</strong> <span class="gradient-text"><?php echo htmlspecialchars($achievement['score']); ?></span>
                                            </div>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($achievement['certificate_no'])): ?>
                                            <div class="mb-1 small">
                                                <strong>证书编号:</strong> <span class="gradient-text"><?php echo htmlspecialchars($achievement['certificate_no']); ?></span>
                                            </div>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($achievement['issue_authority'])): ?>
                                            <div class="mb-1 small">
                                                <strong>颁发机构:</strong> <span class="gradient-text"><?php echo htmlspecialchars($achievement['issue_authority']); ?></span>
                                            </div>
                                            <?php endif; ?>
                                            
                                            <div class="mb-1 small">
                                                <strong>获得日期:</strong> <span class="gradient-text"><?php echo date('Y-m-d', strtotime($achievement['achieved_date'])); ?></span>
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
                                        <a href="#" class="btn btn-primary btn-rounded btn-shine hover-scale" data-bs-toggle="modal" data-bs-target="#certificateModal-<?php echo $achievement['id']; ?>">
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
                                        <a href="<?php echo site_url('uploads/' . $achievement['attachment']); ?>" 
                                           target="_blank" class="btn btn-primary btn-rounded btn-shine hover-scale">
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
                    <div class="mt-4 text-center">
                        <?php echo $pagination; ?>
                    </div>
                    <?php endif; ?>
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