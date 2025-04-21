<?php
/**
 * 管理员成就列表视图
 */
include_once VIEW_PATH . '/header.php';

// 设置面包屑导航
$breadcrumbs = [
    ['title' => '首页', 'url' => site_url()],
    ['title' => '管理控制台', 'url' => site_url('admin/dashboard')],
    ['title' => '成就管理', 'active' => true]
];
include_once VIEW_PATH . '/templates/breadcrumb.php';
?>

<div class="container py-5">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card glass animate-float mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h1 class="gradient-text mb-3 animate-pulse">成就管理</h1>
                            <p class="lead">管理所有学生的编程成就与证书记录。</p>
                        </div>
                        <a href="<?php echo site_url('admin/achievements/add'); ?>" class="btn btn-primary btn-shine">
                            <i class="bi bi-plus-circle-fill me-2"></i>添加成就
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card glass mb-4">
        <div class="card-body p-4">
            <!-- 搜索和筛选表单 -->
            <form action="<?php echo site_url('admin/achievements'); ?>" method="get" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-end-0">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" class="form-control custom-select border-start-0" name="search" value="<?php echo htmlspecialchars($search ?? ''); ?>" placeholder="搜索成就标题或描述...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select custom-select" name="type">
                            <option value="">- 所有类型 -</option>
                            <?php foreach ($types as $type_option): ?>
                            <option value="<?php echo htmlspecialchars($type_option); ?>" <?php echo ($type == $type_option) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($type_option); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select custom-select" name="student_id">
                            <option value="">- 所有学生 -</option>
                            <?php foreach ($students as $student_option): ?>
                            <option value="<?php echo $student_option['id']; ?>" <?php echo ($student_id == $student_option['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($student_option['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <div class="d-grid d-md-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-shine">
                                <i class="bi bi-funnel-fill me-1"></i>筛选
                            </button>
                            <?php if (!empty($search) || !empty($type) || !empty($student_id)): ?>
                            <a href="<?php echo site_url('admin/achievements'); ?>" class="btn btn-outline-primary">
                                <i class="bi bi-x-circle me-1"></i>清除
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </form>
            
            <?php if (empty($achievements)): ?>
                <div class="text-center py-5">
                    <div class="badge-icon bg-light text-muted rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width:60px;height:60px">
                        <i class="bi bi-trophy fs-1"></i>
                    </div>
                    <h5 class="text-muted mb-3">
                        <?php if (!empty($search) || !empty($type) || !empty($student_id)): ?>
                            未找到符合条件的成就记录
                        <?php else: ?>
                            暂无成就记录
                        <?php endif; ?>
                    </h5>
                    <a href="<?php echo site_url('admin/achievements/add'); ?>" class="btn btn-primary btn-shine mt-2">
                        <i class="bi bi-plus-circle-fill me-2"></i>添加成就
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-borderless table-hover align-middle">
                        <thead class="text-muted small">
                            <tr>
                                <th>ID</th>
                                <th>学生</th>
                                <th>成就标题</th>
                                <th>类型</th>
                                <th>分数/评级</th>
                                <th>证书编号</th>
                                <th>获得日期</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($achievements as $achievement): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($achievement['id']); ?></td>
                                <td class="fw-medium">
                                    <a href="<?php echo site_url('admin/students/view/' . $achievement['student_id']); ?>" class="gradient-text btn-link hover-scale-sm" data-bs-toggle="tooltip" title="查看学生详情">
                                        <i class="bi bi-person-badge me-1"></i><?php echo htmlspecialchars($achievement['student_name']); ?>
                                    </a>
                                </td>
                                <td><?php echo htmlspecialchars($achievement['title']); ?></td>
                                <td><span class="badge badge-primary"><?php echo htmlspecialchars($achievement['achievement_type']); ?></span></td>
                                <td><?php echo htmlspecialchars($achievement['score'] ?: '无'); ?></td>
                                <td><?php echo htmlspecialchars($achievement['certificate_no'] ?: '无'); ?></td>
                                <td><?php echo date('Y-m-d', strtotime($achievement['achieved_date'])); ?></td>
                                <td>
                                    <div class="btn-group">
                                        <a href="<?php echo site_url('admin/achievements/edit/' . $achievement['id']); ?>" class="btn btn-sm btn-primary btn-shine" title="编辑">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-info btn-shine" title="查看证书" 
                                                onclick="viewCertificate('<?php echo htmlspecialchars(addslashes($achievement['title'])); ?>', '<?php echo (!empty($achievement['attachment'])) ? site_url('uploads/' . $achievement['attachment']) : ''; ?>')">
                                            <i class="bi bi-file-earmark-image"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger btn-shine" title="删除" 
                                                onclick="confirmDelete(<?php echo $achievement['id']; ?>, '<?php echo htmlspecialchars(addslashes($achievement['title'])); ?>')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- 分页 -->
                <?php if (isset($pagination)): ?>
                <div class="d-flex justify-content-center mt-4">
                    <?php echo $pagination; ?>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- 删除确认模态框 -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass">
            <div class="modal-header border-0">
                <h5 class="modal-title gradient-text" id="deleteModalLabel">确认删除</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="关闭"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <div class="badge-icon bg-danger text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width:60px;height:60px">
                        <i class="bi bi-exclamation-triangle-fill fs-4"></i>
                    </div>
                </div>
                <p class="text-center">确定要删除成就 "<span id="achievementTitle"></span>" 吗？</p>
                <p class="text-center text-danger">此操作不可恢复！</p>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">取消</button>
                <form id="deleteForm" action="" method="post" style="display: inline;">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    <button type="submit" class="btn btn-danger btn-shine">确认删除</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- 查看证书模态框 -->
<div class="modal fade" id="certificateModal" tabindex="-1" aria-labelledby="certificateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content glass">
            <div class="modal-header border-0">
                <h5 class="modal-title gradient-text" id="certificateModalLabel">查看证书</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="关闭"></button>
            </div>
            <div class="modal-body text-center p-0">
                <img id="certificateImage" src="" class="img-fluid certificate-image" alt="证书">
            </div>
            <div class="modal-footer border-0">
                <a id="downloadCertificate" href="#" target="_blank" class="btn btn-primary btn-shine" download>
                    <i class="bi bi-download me-2"></i>下载证书
                </a>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">关闭</button>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(id, title) {
    document.getElementById('achievementTitle').textContent = title;
    document.getElementById('deleteForm').action = '<?php echo site_url('admin/achievements/delete/'); ?>' + id;
    
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    deleteModal.show();
}

function viewCertificate(title, certificateUrl) {
    document.getElementById('certificateModalLabel').textContent = title + ' - 证书';
    document.getElementById('certificateImage').src = certificateUrl;
    document.getElementById('downloadCertificate').href = certificateUrl;
    
    // 添加图片加载失败处理
    const certificateImage = document.getElementById('certificateImage');
    certificateImage.onerror = function() {
        this.onerror = null; // 防止无限循环
        this.style.display = 'none';
        document.querySelector('.modal-body').innerHTML = 
            '<div class="alert alert-warning p-4 text-center">' +
            '<i class="bi bi-exclamation-triangle-fill fs-1 mb-3 d-block"></i>' +
            '<p>无法加载证书图片或文件不存在。</p>' +
            '</div>';
    };
    
    const certificateModal = new bootstrap.Modal(document.getElementById('certificateModal'));
    certificateModal.show();
}

// 初始化Bootstrap工具提示
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>

<style>
.certificate-image {
    max-width: 100%;
    box-shadow: 0 0 20px rgba(0,0,0,0.1);
    border-radius: 0.5rem;
}

.hover-scale-sm {
    transition: all 0.2s ease-in-out;
}

.hover-scale-sm:hover {
    transform: scale(1.05);
    text-decoration: none;
}

.btn-link {
    text-decoration: none;
}

.btn-link:hover {
    text-decoration: underline;
}
</style>

<?php include_once VIEW_PATH . '/footer.php'; ?> 