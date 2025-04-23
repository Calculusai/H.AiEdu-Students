<?php
/**
 * 学生个人成就展示页面
 */
include_once VIEW_PATH . '/header.php';

// 引入成就页面专用CSS
echo '<link rel="stylesheet" href="' . site_url('assets/css/achievements.css') . '">';
echo '<link rel="stylesheet" href="' . site_url('assets/css/modals.css') . '">';
?>

<div class="container py-5">
    <!-- 页面标题 -->
    <div class="achievement-header">
        <h1><?php echo htmlspecialchars($student['name']); ?>的成就展示</h1>
        <p>这里展示了<?php echo htmlspecialchars($student['name']); ?>在少儿编程领域的所有成就与证书，记录学习成长的点滴。</p>
        <div class="achievement-actions">
            <a href="<?php echo site_url('achievements'); ?>" class="btn btn-light btn-lg btn-shine">
                <i class="bi bi-trophy me-2"></i>查看全部学生成就
            </a>
        </div>
    </div>
    
    <div class="row g-4">
        <!-- 左侧边栏：学生信息和筛选面板 -->
        <div class="col-lg-4">
            <!-- 筛选面板 -->
            <div class="filter-panel">
                <h5 class="mb-3">学生信息</h5>
                <div class="student-profile mb-4">
                    <div class="student-avatar-lg">
                        <i class="bi bi-person"></i>
                    </div>
                    <div>
                        <h5 class="mb-1"><?php echo htmlspecialchars($student['name']); ?></h5>
                        <?php if (!empty($student['grade'])): ?>
                        <p class="mb-0 small text-muted"><?php echo htmlspecialchars($student['grade']); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($student['school'])): ?>
                        <p class="mb-0 small text-muted"><?php echo htmlspecialchars($student['school']); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="d-flex justify-content-between mb-4">
                    <div>
                        <span class="section-title">成就总数</span>
                        <h4><?php echo count($achievements); ?></h4>
                    </div>
                    <div>
                        <span class="section-title">加入时间</span>
                        <h4><?php echo date('Y-m-d', strtotime($student['created_at'])); ?></h4>
                    </div>
                </div>
                
                <?php if (!empty($types)): ?>
                <h5 class="mb-3">按类型筛选</h5>
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <a href="<?php echo site_url('student/' . $student['id']); ?>" 
                       class="btn <?php echo empty($type) ? 'filter-btn' : 'btn-outline-primary'; ?>">
                        全部
                    </a>
                    
                    <?php foreach ($types as $type_option): ?>
                    <a href="<?php echo site_url('student/' . $student['id'] . '?type=' . urlencode($type_option)); ?>" 
                       class="btn <?php echo ($type == $type_option) ? 'filter-btn' : 'btn-outline-primary'; ?>">
                        <?php echo htmlspecialchars($type_option); ?>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
            </div>
        </div>
        
        <!-- 右侧：成就列表 -->
        <div class="col-lg-8">
            <?php if (empty($achievements)): ?>
            <!-- 空状态 -->
            <div class="empty-state">
                <i class="bi bi-award"></i>
                <h3>暂无成就记录</h3>
                <?php if (!empty($type)): ?>
                <p>该学生在 "<?php echo htmlspecialchars($type); ?>" 类别下暂时没有成就记录。</p>
                <?php else: ?>
                <p>该学生暂时没有任何成就记录。</p>
                <?php endif; ?>
                <a href="<?php echo site_url('achievements'); ?>" class="btn btn-primary mt-3">
                    <i class="bi bi-trophy me-2"></i>查看全部学生成就
                </a>
            </div>
            <?php else: ?>
            <!-- 成就列表 -->
            <div class="row g-4">
                <?php foreach ($achievements as $achievement): ?>
                <div class="col-md-6 mb-4">
                    <div class="achievement-card certificate-style">
                        <div class="certificate-border"></div>
                        <div class="certificate-seal"></div>
                        <div class="card-body">
                            <div class="certificate-header">
                                <div class="certificate-ribbon">
                                    <span class="badge-certificate"><?php echo htmlspecialchars($achievement['achievement_type']); ?></span>
                                </div>
                                <small class="certificate-date">
                                    <i class="bi bi-calendar-check me-1"></i><?php echo date('Y-m-d', strtotime($achievement['achieved_date'])); ?>
                                </small>
                            </div>
                            
                            <div class="certificate-title-container">
                                <h5 class="certificate-title"><?php echo htmlspecialchars($achievement['title']); ?></h5>
                            </div>
                            
                            <?php if (!empty($achievement['description'])): ?>
                            <p class="card-text"><?php echo htmlspecialchars($achievement['description']); ?></p>
                            <?php endif; ?>
                            
                            <div class="certificate-student">
                                <div class="student-medal">
                                    <i class="bi bi-trophy"></i>
                                </div>
                                <span class="student-name"><?php echo htmlspecialchars($student['name']); ?></span>
                            </div>
                            
                            <?php if (!empty($achievement['attachment'])): ?>
                            <div class="card-footer text-center">
                                <?php 
                                $fileExt = pathinfo($achievement['attachment'], PATHINFO_EXTENSION);
                                $isImage = in_array(strtolower($fileExt), ['jpg', 'jpeg', 'png', 'gif']);
                                if ($isImage):
                                ?>
                                <button type="button" class="view-btn view-btn-primary" data-bs-toggle="modal" data-bs-target="#certificateModal<?php echo $achievement['id']; ?>">
                                    <i class="bi bi-eye me-2"></i>查看证书
                                </button>
                                <?php else: ?>
                                <a href="<?php echo site_url('uploads/' . $achievement['attachment']); ?>" 
                                   target="_blank" class="view-btn view-btn-primary">
                                    <i class="bi bi-file-earmark me-1"></i> 查看证书
                                </a>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- 证书模态框 -->
                <?php if (!empty($achievement['attachment']) && $isImage): ?>
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
                <?php endforeach; ?>
            </div>
            
            <!-- 成就详情模态框 -->
            <?php foreach ($achievements as $achievement): ?>
            <div class="modal fade achievement-modal" id="achievementModal<?php echo $achievement['id']; ?>" tabindex="-1" aria-labelledby="achievementModalLabel<?php echo $achievement['id']; ?>" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="achievementModalLabel<?php echo $achievement['id']; ?>"><?php echo htmlspecialchars($achievement['title']); ?></h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="d-flex justify-content-between mb-3">
                                <span class="achievement-detail-badge"><?php echo htmlspecialchars($achievement['achievement_type']); ?></span>
                                <span class="achievement-detail-date">
                                    <i class="bi bi-calendar-check me-1"></i><?php echo date('Y-m-d', strtotime($achievement['achieved_date'])); ?>
                                </span>
                            </div>
                            
                            <div class="student-profile">
                                <div class="student-avatar-lg">
                                    <i class="bi bi-person"></i>
                                </div>
                                <div>
                                    <h5 class="mb-1"><?php echo htmlspecialchars($student['name']); ?></h5>
                                    <?php if (!empty($student['grade'])): ?>
                                    <p class="mb-0 small text-muted"><?php echo htmlspecialchars($student['grade']); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <?php if (!empty($achievement['description'])): ?>
                            <div class="mb-3">
                                <div class="section-title">成就描述</div>
                                <div class="section-content"><?php echo htmlspecialchars($achievement['description']); ?></div>
                            </div>
                            <?php endif; ?>
                            
                            <div class="mb-3">
                                <div class="section-title">成就详情</div>
                                <div class="section-content">
                                    <?php if (!empty($achievement['score'])): ?>
                                    <div class="mb-2">
                                        <strong>分数/评级:</strong> <?php echo htmlspecialchars($achievement['score']); ?>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($achievement['certificate_no'])): ?>
                                    <div class="mb-2">
                                        <strong>证书编号:</strong> <?php echo htmlspecialchars($achievement['certificate_no']); ?>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($achievement['issue_authority'])): ?>
                                    <div class="mb-2">
                                        <strong>颁发机构:</strong> <?php echo htmlspecialchars($achievement['issue_authority']); ?>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <div>
                                        <strong>获得日期:</strong> <?php echo date('Y-m-d', strtotime($achievement['achieved_date'])); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <?php if (!empty($achievement['attachment'])): ?>
                            <?php 
                            $fileExt = pathinfo($achievement['attachment'], PATHINFO_EXTENSION);
                            $isImage = in_array(strtolower($fileExt), ['jpg', 'jpeg', 'png', 'gif']);
                            if ($isImage):
                            ?>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#certificateModal<?php echo $achievement['id']; ?>" data-bs-dismiss="modal">
                                <i class="bi bi-eye me-2"></i>查看证书
                            </button>
                            <?php else: ?>
                            <a href="<?php echo site_url('uploads/' . $achievement['attachment']); ?>" 
                               target="_blank" class="btn btn-primary">
                                <i class="bi bi-file-earmark me-1"></i> 查看证书
                            </a>
                            <?php endif; ?>
                            <?php endif; ?>
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">关闭</button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            
            <!-- 分页 -->
            <?php if (isset($pagination)): ?>
            <div class="text-center mt-4">
                <nav aria-label="成就分页导航">
                    <?php echo $pagination; ?>
                </nav>
            </div>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include_once VIEW_PATH . '/footer.php'; ?> 