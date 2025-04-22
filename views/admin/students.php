<?php
/**
 * 管理员学生列表视图
 */
include_once VIEW_PATH . '/header.php';

// 设置面包屑导航
$breadcrumbs = [
    ['title' => '首页', 'url' => site_url()],
    ['title' => '管理控制台', 'url' => site_url('admin/dashboard')],
    ['title' => '学生管理', 'active' => true]
];
include_once VIEW_PATH . '/templates/breadcrumb.php';
?>

<div class="container py-5">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card glass animate-float mb-4">
                <div class="card-body p-4">
                    <h1 class="gradient-text mb-3 animate-pulse">学生管理</h1>
                    <p class="lead">管理所有学生信息，添加、编辑或查看学生的成就记录。</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mb-4">
        <div class="col-12">
            <div class="card glass">
                <div class="card-header d-flex justify-content-between align-items-center bg-transparent border-bottom-0 p-4">
                    <h5 class="gradient-text mb-0">学生列表</h5>
                    <a href="<?php echo site_url('admin/students/add'); ?>" class="btn btn-primary btn-shine">
                        <i class="bi bi-person-plus-fill me-2"></i>添加学生
                    </a>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3 mb-4">
                        <div class="col-md-8">
                            <form class="d-flex" action="<?php echo site_url('admin/students'); ?>" method="get">
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent border-end-0">
                                        <i class="bi bi-search"></i>
                                    </span>
                                    <input type="text" class="form-control custom-select border-start-0" placeholder="搜索学生姓名、学号或班级" 
                                           name="search" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                                    <button class="btn btn-primary btn-shine" type="submit">搜索</button>
                                </div>
                            </form>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex justify-content-end gap-2">
                                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#filterModal">
                                    <i class="bi bi-funnel-fill me-2"></i>筛选
                                </button>
                                <div class="dropdown">
                                    <button class="btn btn-outline-primary dropdown-toggle" type="button" id="bulkActionsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                        批量操作
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="bulkActionsDropdown">
                                        <li><a class="dropdown-item" href="#" id="exportExcel">
                                            <i class="bi bi-file-earmark-excel me-2"></i>导出Excel
                                        </a></li>
                                        <li><a class="dropdown-item" href="#" id="importStudent" data-bs-toggle="modal" data-bs-target="#importModal">
                                            <i class="bi bi-file-earmark-arrow-up me-2"></i>导入学生
                                        </a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item text-danger" href="#" id="bulkDelete">
                                            <i class="bi bi-trash me-2"></i>批量删除
                                        </a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <?php if (isset($_SESSION['search_message']) && !empty($_SESSION['search_message'])): ?>
                    <div class="alert alert-info d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <i class="bi bi-info-circle-fill me-2"></i>
                            当前筛选条件: <?php echo $_SESSION['search_message']; ?>
                        </div>
                        <a href="<?php echo site_url('admin/students'); ?>" class="btn btn-sm btn-light">
                            <i class="bi bi-x-lg me-1"></i>清除筛选
                        </a>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger">
                        <?php echo $error_message; ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (isset($success_message)): ?>
                    <div class="alert alert-success">
                        <?php echo $success_message; ?>
                    </div>
                    <?php endif; ?>
                    
                    <form id="studentsForm" action="<?php echo site_url('admin/students/bulk_action'); ?>" method="post">
                        <div class="table-responsive">
                            <table class="table table-borderless table-hover align-middle">
                                <thead class="text-muted small">
                                    <tr>
                                        <th width="40">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="select-all">
                                            </div>
                                        </th>
                                        <th>学号</th>
                                        <th>姓名</th>
                                        <th>班级</th>
                                        <th>联系方式</th>
                                        <th>邮箱</th>
                                        <th>注册时间</th>
                                        <th>上次登录</th>
                                        <th width="140">操作</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($students)): ?>
                                    <tr>
                                        <td colspan="9" class="text-center py-5">
                                            <div class="badge-icon bg-light text-muted rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width:60px;height:60px">
                                                <i class="bi bi-people fs-1"></i>
                                            </div>
                                            <h5 class="text-muted mb-3">暂无学生数据</h5>
                                            <a href="<?php echo site_url('admin/students/add'); ?>" class="btn btn-primary btn-shine">
                                                <i class="bi bi-person-plus-fill me-2"></i>添加学生
                                            </a>
                                        </td>
                                    </tr>
                                    <?php else: ?>
                                        <?php foreach ($students as $student): ?>
                                        <tr>
                                            <td>
                                                <div class="form-check">
                                                    <input class="form-check-input student-checkbox" type="checkbox" name="selected_students[]" value="<?php echo $student['id']; ?>">
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                                            <td>
                                                <a href="<?php echo site_url('admin/students/view/' . $student['id']); ?>" class="fw-medium text-decoration-none">
                                                    <?php echo htmlspecialchars($student['name']); ?>
                                                </a>
                                            </td>
                                            <td><?php echo htmlspecialchars($student['class_name'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($student['contact'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($student['email'] ?? ''); ?></td>
                                            <td><?php echo isset($student['created_at']) ? date('Y-m-d', strtotime($student['created_at'])) : ''; ?></td>
                                            <td>
                                                <?php if (!empty($student['last_login'])): ?>
                                                <span data-bs-toggle="tooltip" title="<?php echo date('Y-m-d H:i:s', strtotime($student['last_login'])); ?>">
                                                    <?php echo date('Y-m-d', strtotime($student['last_login'])); ?>
                                                </span>
                                                <?php else: ?>
                                                <span class="text-muted">尚未登录</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="<?php echo site_url('admin/students/edit/' . $student['id']); ?>" class="btn btn-sm btn-primary btn-shine" data-bs-toggle="tooltip" title="编辑">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <a href="<?php echo site_url('admin/students/achievements/' . $student['id']); ?>" class="btn btn-sm btn-info btn-shine" data-bs-toggle="tooltip" title="成就管理">
                                                        <i class="bi bi-trophy"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-danger btn-shine delete-btn" data-id="<?php echo $student['id']; ?>" data-name="<?php echo htmlspecialchars($student['name']); ?>" data-bs-toggle="tooltip" title="删除">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </form>
                    
                    <?php if (!empty($students)): ?>
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <p>共 <span class="fw-medium"><?php echo $total; ?></span> 名学生，当前显示 <span class="fw-medium"><?php echo count($students); ?></span> 名</p>
                        </div>
                        <div class="col-md-6">
                            <nav aria-label="Page navigation">
                                <ul class="pagination justify-content-end">
                                    <?php echo $pagination; ?>
                                </ul>
                            </nav>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 筛选模态框 -->
<div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass">
            <form action="<?php echo site_url('admin/students'); ?>" method="get">
                <div class="modal-header border-0">
                    <h5 class="modal-title gradient-text" id="filterModalLabel">筛选学生</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="filter_class" class="form-label">班级</label>
                        <select class="form-select custom-select" id="filter_class" name="class">
                            <option value="">所有班级</option>
                            <?php if (!empty($classes)): ?>
                                <?php foreach ($classes as $class): ?>
                                <option value="<?php echo htmlspecialchars($class); ?>" <?php echo (isset($_GET['class']) && $_GET['class'] == $class) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($class); ?>
                                </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="filter_grade" class="form-label">年级</label>
                        <select class="form-select custom-select" id="filter_grade" name="grade">
                            <option value="">所有年级</option>
                            <?php if (!empty($grades)): ?>
                                <?php foreach ($grades as $grade): ?>
                                <option value="<?php echo htmlspecialchars($grade); ?>" <?php echo (isset($_GET['grade']) && $_GET['grade'] == $grade) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($grade); ?>
                                </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="filter_status" class="form-label">账号状态</label>
                        <select class="form-select custom-select" id="filter_status" name="status">
                            <option value="">所有状态</option>
                            <option value="1" <?php echo (isset($_GET['status']) && $_GET['status'] == '1') ? 'selected' : ''; ?>>启用</option>
                            <option value="0" <?php echo (isset($_GET['status']) && $_GET['status'] == '0') ? 'selected' : ''; ?>>禁用</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary btn-shine">应用筛选</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 删除确认模态框 -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass">
            <div class="modal-header border-0">
                <h5 class="modal-title gradient-text" id="deleteModalLabel">确认删除</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <div class="badge-icon bg-danger text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width:60px;height:60px">
                        <i class="bi bi-exclamation-triangle-fill fs-4"></i>
                    </div>
                </div>
                <p class="text-center">确定要删除学生 <strong id="studentName"></strong> 吗？</p>
                <p class="text-center text-danger">此操作将同时删除该学生的所有成就记录且不可恢复！</p>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">取消</button>
                <button type="button" id="confirmDeleteBtn" class="btn btn-danger btn-shine">确认删除</button>
            </div>
        </div>
    </div>
</div>

<!-- 创建一个隐藏的删除表单 -->
<form id="deleteForm" action="" method="post" style="display:none;">
    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
</form>

<!-- 导入学生模态框 -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass">
            <form action="<?php echo site_url('admin/students/import'); ?>" method="post" enctype="multipart/form-data">
                <div class="modal-header border-0">
                    <h5 class="modal-title gradient-text" id="importModalLabel">导入学生</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="importFile" class="form-label">选择Excel文件</label>
                        <input type="file" class="form-control" id="importFile" name="import_file" accept=".xlsx,.xls,.csv" required>
                        <div class="form-text">支持 Excel (.xlsx, .xls) 或 CSV 格式</div>
                    </div>
                    <div class="alert alert-info">
                        <h6 class="alert-heading mb-2"><i class="bi bi-info-circle me-2"></i>导入说明</h6>
                        <p class="mb-2">请确保Excel文件包含以下列：</p>
                        <ul class="mb-0">
                            <li>学号 (student_id)</li>
                            <li>姓名 (name)</li>
                            <li>班级 (class_name)</li>
                            <li>电子邮箱 (email)</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary btn-shine">导入</button>
                </div>
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 删除确认
    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const name = this.getAttribute('data-name');
            
            document.getElementById('studentName').textContent = name;
            document.getElementById('deleteForm').action = '<?php echo site_url('admin/students/delete/'); ?>' + id;
            console.log('删除表单将提交到: ' + document.getElementById('deleteForm').action);
            
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
            deleteModal.show();
        });
    });

    // 确认删除按钮点击事件
    document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
        console.log('确认删除按钮被点击，提交表单到: ' + document.getElementById('deleteForm').action);
        document.getElementById('deleteForm').submit();
    });

    // 全选/取消全选
    document.getElementById('select-all').addEventListener('change', function() {
        const isChecked = this.checked;
        document.querySelectorAll('.student-checkbox').forEach(checkbox => {
            checkbox.checked = isChecked;
        });
    });

    // 批量删除
    document.getElementById('bulkDelete').addEventListener('click', function(e) {
        e.preventDefault();
        
        const selectedCount = document.querySelectorAll('.student-checkbox:checked').length;
        
        if (selectedCount === 0) {
            alert('请先选择要删除的学生');
            return;
        }
        
        if (confirm(`确定要删除选中的 ${selectedCount} 名学生吗？此操作不可恢复！`)) {
            const form = document.getElementById('studentsForm');
            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = 'delete';
            form.appendChild(actionInput);
            
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = 'csrf_token';
            csrfInput.value = '<?php echo generate_csrf_token(); ?>';
            form.appendChild(csrfInput);
            
            form.submit();
        }
    });

    // 导出Excel
    document.getElementById('exportExcel').addEventListener('click', function(e) {
        e.preventDefault();
        
        const selectedStudents = Array.from(document.querySelectorAll('.student-checkbox:checked')).map(checkbox => checkbox.value);
        
        let url = '<?php echo site_url('admin/students/export'); ?>';
        if (selectedStudents.length > 0) {
            url += '?ids=' + selectedStudents.join(',');
        }
        
        window.location.href = url;
    });

    // 初始化工具提示
    if (typeof bootstrap !== 'undefined') {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    } else {
        console.warn('Bootstrap未加载，工具提示功能不可用');
    }
});
</script>

<?php include_once VIEW_PATH . '/footer.php'; ?> 