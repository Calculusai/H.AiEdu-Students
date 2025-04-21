<?php
/**
 * 学生成就管理页面
 */
include_once VIEW_PATH . '/header.php';

// 设置面包屑导航
$breadcrumbs = [
    ['title' => '首页', 'url' => site_url()],
    ['title' => '管理控制台', 'url' => site_url('admin/dashboard')],
    ['title' => '学生管理', 'url' => site_url('admin/students')],
    ['title' => $student['name'] . ' 的成就', 'active' => true]
];
include_once VIEW_PATH . '/templates/breadcrumb.php';
?>

<div class="container-fluid py-5">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card glass animate-float mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h1 class="gradient-text mb-3 animate-pulse">
                                <i class="bi bi-award me-2"></i><?php echo htmlspecialchars($student['name']); ?> 的成就
                            </h1>
                            <p class="lead d-flex align-items-center">
                                <span class="badge bg-gradient-secondary btn-shine me-2"><?php echo htmlspecialchars($student['student_id']); ?></span>
                                管理该学生的所有编程成就与证书记录
                            </p>
                        </div>
                        <div>
                            <a href="<?php echo site_url('admin/students/edit/' . $student['id']); ?>" class="btn btn-outline-primary btn-rounded btn-shine hover-scale me-2">
                                <i class="bi bi-person-gear me-1"></i> 编辑学生信息
                            </a>
                            <a href="<?php echo site_url('admin/achievements/add?student_id=' . $student['id']); ?>" class="btn btn-primary btn-rounded btn-shine hover-scale">
                                <i class="bi bi-plus-circle me-1"></i> 添加成就
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 筛选工具栏 -->
    <div class="card glass animate-float card-gradient-border mb-4">
        <div class="card-body p-4">
            <form method="get" action="<?php echo site_url('admin/students/achievements/' . $student['id']); ?>" class="row g-3 align-items-end">
                <!-- 类型筛选 -->
                <div class="col-md-5">
                    <label for="type" class="form-label">成就类型</label>
                    <div class="input-group input-group-glow">
                        <span class="input-group-text bg-transparent border-end-0">
                            <i class="bi bi-filter"></i>
                        </span>
                        <select class="form-select btn-rounded custom-select border-start-0" id="type" name="type">
                            <option value="">全部类型</option>
                            <?php foreach ($types as $t): ?>
                            <option value="<?php echo htmlspecialchars($t); ?>" <?php echo $type === $t ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($t); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <!-- 搜索框 -->
                <div class="col-md-5">
                    <label for="search" class="form-label">搜索成就</label>
                    <div class="input-group input-group-glow">
                        <span class="input-group-text bg-transparent border-end-0">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" class="form-control btn-rounded custom-select border-start-0" id="search" name="search" 
                               placeholder="输入成就标题或描述..." value="<?php echo htmlspecialchars($search ?? ''); ?>">
                    </div>
                </div>
                
                <!-- 搜索按钮 -->
                <div class="col-md-2">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-rounded btn-shine hover-scale">
                            <i class="bi bi-funnel-fill me-1"></i> 筛选
                        </button>
                        <?php if (!empty($search) || !empty($type)): ?>
                        <a href="<?php echo site_url('admin/students/achievements/' . $student['id']); ?>" class="btn btn-outline-primary btn-rounded hover-scale">
                            <i class="bi bi-x-circle me-1"></i> 清除
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <?php if (isset($_SESSION['flash_message'])): ?>
    <div class="alert glass alert-dismissible fade show mb-4 animate-float" role="alert">
        <div class="d-flex align-items-center">
            <div class="badge-icon bg-gradient-<?php echo $_SESSION['flash_type'] ?? 'info'; ?> text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width:40px;height:40px">
                <i class="bi bi-info-circle fs-4"></i>
            </div>
            <div><?php echo $_SESSION['flash_message']; ?></div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php 
    unset($_SESSION['flash_message']);
    unset($_SESSION['flash_type']);
    endif; 
    ?>
    
    <!-- 成就列表 -->
    <div class="card glass animate-float card-gradient-border mb-4">
        <div class="card-header glass border-0 py-3 d-flex justify-content-between align-items-center">
            <h5 class="m-0 gradient-text">成就列表</h5>
            <span class="badge bg-gradient-primary btn-shine rounded-pill"><?php echo count($achievements); ?> 条记录</span>
        </div>
        <div class="card-body p-0">
            <?php if (empty($achievements)): ?>
            <div class="text-center py-5">
                <div class="badge-icon bg-light text-muted rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width:60px;height:60px">
                    <i class="bi bi-trophy fs-1"></i>
                </div>
                <h5 class="text-muted mb-3">
                    <?php if (!empty($search) || !empty($type)): ?>
                        未找到符合条件的成就记录
                    <?php else: ?>
                        该学生暂无成就记录
                    <?php endif; ?>
                </h5>
                <a href="<?php echo site_url('admin/achievements/add?student_id=' . $student['id']); ?>" class="btn btn-primary btn-shine btn-rounded hover-scale mt-2">
                    <i class="bi bi-plus-circle-fill me-2"></i>添加成就
                </a>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-borderless table-hover align-middle">
                    <thead class="text-muted small">
                        <tr>
                            <th>ID</th>
                            <th>成就标题</th>
                            <th>类型</th>
                            <th>获得日期</th>
                            <th>分数/评级</th>
                            <th>证书编号</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($achievements as $item): ?>
                        <tr class="hover-scale-sm">
                            <td><?php echo $item['id']; ?></td>
                            <td>
                                <div class="fw-medium gradient-text"><?php echo htmlspecialchars($item['title']); ?></div>
                                <?php if (!empty($item['description'])): ?>
                                <p class="small mb-0"><?php echo htmlspecialchars(mb_substr($item['description'], 0, 50)) . (mb_strlen($item['description']) > 50 ? '...' : ''); ?></p>
                                <?php endif; ?>
                            </td>
                            <td><span class="badge badge-primary"><?php echo htmlspecialchars($item['achievement_type']); ?></span></td>
                            <td><?php echo date('Y-m-d', strtotime($item['achieved_date'])); ?></td>
                            <td><?php echo !empty($item['score']) ? $item['score'] : '无'; ?></td>
                            <td><?php echo !empty($item['certificate_no']) ? $item['certificate_no'] : '无'; ?></td>
                            <td>
                                <div class="btn-group">
                                    <a href="<?php echo site_url('admin/achievements/edit/' . $item['id']); ?>" class="btn btn-sm btn-primary btn-shine" title="编辑成就">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <?php if (!empty($item['attachment'])): ?>
                                    <button type="button" class="btn btn-sm btn-info btn-shine" title="查看证书" 
                                            onclick="viewCertificate('<?php echo htmlspecialchars(addslashes($item['title'])); ?>', '<?php echo site_url('uploads/' . $item['attachment']); ?>')">
                                        <i class="bi bi-file-earmark-image"></i>
                                    </button>
                                    <?php endif; ?>
                                    <button type="button" class="btn btn-sm btn-danger btn-shine" title="删除成就" 
                                            onclick="confirmDelete(<?php echo $item['id']; ?>, '<?php echo htmlspecialchars(addslashes($item['title'])); ?>')">
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
            <?php if (!empty($pagination)): ?>
            <div class="d-flex justify-content-center my-4">
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
                <button type="button" class="btn btn-outline-secondary btn-rounded hover-scale" data-bs-dismiss="modal">取消</button>
                <form id="deleteForm" action="" method="post" style="display: inline;">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    <input type="hidden" id="redirect_to_student" name="redirect_to_student" value="">
                    <button type="submit" class="btn btn-danger btn-rounded btn-shine hover-scale">确认删除</button>
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
                <a id="downloadCertificate" href="#" target="_blank" class="btn btn-primary btn-rounded btn-shine hover-scale" download>
                    <i class="bi bi-download me-2"></i>下载证书
                </a>
                <button type="button" class="btn btn-outline-secondary btn-rounded" data-bs-dismiss="modal">关闭</button>
            </div>
        </div>
    </div>
</div>

<style>
.input-group-glow {
    transition: all 0.3s ease;
}

.input-group-glow:focus-within {
    box-shadow: 0 0 15px rgba(var(--primary-rgb), 0.5);
}

.hover-scale-sm {
    transition: all 0.2s ease;
}

.hover-scale-sm:hover {
    transform: scale(1.01);
    background-color: rgba(var(--primary-rgb), 0.05);
}

.modal-content {
    border-radius: 16px;
    overflow: hidden;
}

.certificate-image {
    max-width: 100%;
    box-shadow: 0 0 20px rgba(0,0,0,0.1);
    border-radius: 0.5rem;
    transition: all 0.3s ease;
}

.custom-select {
    border-radius: 10px;
    border: 1px solid rgba(0,0,0,0.1);
    padding: 0.5rem 1rem;
    transition: all 0.3s ease;
}

.custom-select:focus {
    box-shadow: none;
    border-color: var(--primary-color);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 初始化工具提示
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // 添加输入时的微动画效果
    const inputs = document.querySelectorAll('input, select');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            if (this.closest('.input-group')) {
                this.closest('.input-group').classList.add('input-focus-glow');
            }
        });
        input.addEventListener('blur', function() {
            if (this.closest('.input-group')) {
                this.closest('.input-group').classList.remove('input-focus-glow');
            }
        });
    });
});

function confirmDelete(id, title) {
    document.getElementById('achievementTitle').textContent = title;
    document.getElementById('deleteForm').action = '<?php echo site_url('admin/achievements/delete/'); ?>' + id;
    
    // 添加一个隐藏的input，用于在删除后重定向回学生成就页面
    const redirectInput = document.getElementById('redirect_to_student');
    if (!redirectInput) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.id = 'redirect_to_student';
        input.name = 'redirect_to_student';
        input.value = '<?php echo $student['id']; ?>';
        document.getElementById('deleteForm').appendChild(input);
    }
    
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    deleteModal.show();
}

function viewCertificate(title, certificateUrl) {
    document.getElementById('certificateModalLabel').textContent = title + ' - 证书';
    document.getElementById('certificateImage').src = certificateUrl;
    document.getElementById('downloadCertificate').href = certificateUrl;
    
    const certificateModal = new bootstrap.Modal(document.getElementById('certificateModal'));
    certificateModal.show();
}
</script>

<?php include_once VIEW_PATH . '/footer.php'; ?> 