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

<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="h3 mb-3"><i class="fas fa-user-graduate me-2"></i>学生管理</h1>
        </div>
    </div>
    
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">学生列表</h6>
                    <div>
                        <a href="<?php echo site_url('admin/students/add'); ?>" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus-circle me-1"></i> 添加学生
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <form class="d-flex" action="<?php echo site_url('admin/students'); ?>" method="get">
                                <div class="input-group">
                                    <input type="text" class="form-control" placeholder="搜索学生姓名、学号或班级..." 
                                           name="search" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                                    <button class="btn btn-outline-secondary" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex justify-content-end">
                                <button type="button" class="btn btn-outline-secondary me-2" data-bs-toggle="modal" data-bs-target="#filterModal">
                                    <i class="fas fa-filter me-1"></i> 筛选
                                </button>
                                <div class="dropdown">
                                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="bulkActionsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                        批量操作
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="bulkActionsDropdown">
                                        <li><a class="dropdown-item" href="#" id="exportExcel">
                                            <i class="fas fa-file-excel me-2"></i>导出Excel
                                        </a></li>
                                        <li><a class="dropdown-item" href="#" id="importStudent" data-bs-toggle="modal" data-bs-target="#importModal">
                                            <i class="fas fa-file-import me-2"></i>导入学生
                                        </a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item text-danger" href="#" id="bulkDelete">
                                            <i class="fas fa-trash-alt me-2"></i>批量删除
                                        </a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    
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
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
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
                                        <td colspan="9" class="text-center py-4">
                                            <div class="text-muted mb-3">
                                                <i class="fas fa-folder-open fa-3x"></i>
                                            </div>
                                            <p>暂无学生数据</p>
                                            <a href="<?php echo site_url('admin/students/add'); ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-plus-circle me-1"></i> 添加学生
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
                                                <a href="<?php echo site_url('admin/students/view/' . $student['id']); ?>" class="fw-bold text-decoration-none">
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
                                                <div class="btn-group btn-group-sm">
                                                    <a href="<?php echo site_url('admin/students/edit/' . $student['id']); ?>" class="btn btn-outline-primary" data-bs-toggle="tooltip" title="编辑">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="<?php echo site_url('admin/students/achievements/' . $student['id']); ?>" class="btn btn-outline-info" data-bs-toggle="tooltip" title="成就管理">
                                                        <i class="fas fa-award"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-outline-danger delete-btn" data-id="<?php echo $student['id']; ?>" data-name="<?php echo htmlspecialchars($student['name']); ?>" data-bs-toggle="tooltip" title="删除">
                                                        <i class="fas fa-trash-alt"></i>
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
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <p>共 <strong><?php echo $total; ?></strong> 名学生，当前显示 <strong><?php echo count($students); ?></strong> 名</p>
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
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?php echo site_url('admin/students'); ?>" method="get">
                <div class="modal-header">
                    <h5 class="modal-title" id="filterModalLabel">筛选学生</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="filter_class" class="form-label">班级</label>
                        <select class="form-select" id="filter_class" name="class">
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
                        <label for="filter_date" class="form-label">注册日期</label>
                        <select class="form-select" id="filter_date" name="date_range">
                            <option value="">全部时间</option>
                            <option value="today" <?php echo (isset($_GET['date_range']) && $_GET['date_range'] == 'today') ? 'selected' : ''; ?>>今天</option>
                            <option value="yesterday" <?php echo (isset($_GET['date_range']) && $_GET['date_range'] == 'yesterday') ? 'selected' : ''; ?>>昨天</option>
                            <option value="this_week" <?php echo (isset($_GET['date_range']) && $_GET['date_range'] == 'this_week') ? 'selected' : ''; ?>>本周</option>
                            <option value="last_week" <?php echo (isset($_GET['date_range']) && $_GET['date_range'] == 'last_week') ? 'selected' : ''; ?>>上周</option>
                            <option value="this_month" <?php echo (isset($_GET['date_range']) && $_GET['date_range'] == 'this_month') ? 'selected' : ''; ?>>本月</option>
                            <option value="last_month" <?php echo (isset($_GET['date_range']) && $_GET['date_range'] == 'last_month') ? 'selected' : ''; ?>>上月</option>
                            <option value="custom" <?php echo (isset($_GET['date_range']) && $_GET['date_range'] == 'custom') ? 'selected' : ''; ?>>自定义日期</option>
                        </select>
                    </div>
                    <div id="custom_date_range" class="<?php echo (isset($_GET['date_range']) && $_GET['date_range'] == 'custom') ? '' : 'd-none'; ?>">
                        <div class="row g-3">
                            <div class="col-md-6 mb-3">
                                <label for="start_date" class="form-label">开始日期</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo isset($_GET['start_date']) ? htmlspecialchars($_GET['start_date']) : ''; ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="end_date" class="form-label">结束日期</label>
                                <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo isset($_GET['end_date']) ? htmlspecialchars($_GET['end_date']) : ''; ?>">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="filter_status" class="form-label">登录状态</label>
                        <select class="form-select" id="filter_status" name="login_status">
                            <option value="">所有状态</option>
                            <option value="logged_in" <?php echo (isset($_GET['login_status']) && $_GET['login_status'] == 'logged_in') ? 'selected' : ''; ?>>已登录过</option>
                            <option value="never_logged_in" <?php echo (isset($_GET['login_status']) && $_GET['login_status'] == 'never_logged_in') ? 'selected' : ''; ?>>从未登录</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="<?php echo site_url('admin/students'); ?>" class="btn btn-link text-decoration-none">重置筛选</a>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary">应用筛选</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 导入学生模态框 -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?php echo site_url('admin/students/import'); ?>" method="post" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="importModalLabel">导入学生</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>请上传包含学生信息的Excel文件。</p>
                    <p>文件格式要求：</p>
                    <ul>
                        <li>表格第一行必须是表头，包含字段名</li>
                        <li>必须包含学号(student_id)和姓名(name)字段</li>
                        <li>可选字段：班级(class_name)、联系方式(contact)、邮箱(email)、备注(notes)</li>
                    </ul>
                    <div class="mb-3">
                        <label for="import_file" class="form-label">选择Excel文件</label>
                        <input type="file" class="form-control" id="import_file" name="import_file" accept=".xlsx,.xls" required>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="generate_password" name="generate_password" value="1" checked>
                        <label class="form-check-label" for="generate_password">
                            为导入的学生自动生成密码
                        </label>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>如果Excel中已包含密码字段，系统将使用该密码；否则将根据设置自动生成。
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="<?php echo site_url('admin/students/download_template'); ?>" class="btn btn-link text-decoration-none">下载模板</a>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary">开始导入</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 删除确认模态框 -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="deleteForm" action="" method="post">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">删除学生</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <p><i class="fas fa-exclamation-triangle me-2"></i>警告：此操作无法撤销！</p>
                    </div>
                    <p>您确定要删除学生 <strong id="student-name"></strong> 吗？</p>
                    <p>删除后，该学生所有的相关记录（包括成就、登录记录等）将被永久删除。</p>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="confirm_delete" name="confirm_delete" value="1" required>
                        <label class="form-check-label" for="confirm_delete">
                            我已了解删除操作的后果，并确认要删除此学生
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-danger">确认删除</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 批量删除确认模态框 -->
<div class="modal fade" id="bulkDeleteModal" tabindex="-1" aria-labelledby="bulkDeleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bulkDeleteModalLabel">批量删除学生</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <p><i class="fas fa-exclamation-triangle me-2"></i>警告：此操作无法撤销！</p>
                </div>
                <p>您确定要删除选中的 <strong id="selected-count">0</strong> 名学生吗？</p>
                <p>删除后，这些学生所有的相关记录（包括成就、登录记录等）将被永久删除。</p>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="bulk_confirm_delete" name="confirm_delete" value="1" required>
                    <label class="form-check-label" for="bulk_confirm_delete">
                        我已了解删除操作的后果，并确认要删除选中的学生
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">取消</button>
                <button type="button" id="confirm-bulk-delete" class="btn btn-danger">确认删除</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 初始化工具提示
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
    
    // 全选/取消全选
    const selectAll = document.getElementById('select-all');
    const studentCheckboxes = document.querySelectorAll('.student-checkbox');
    
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            studentCheckboxes.forEach(checkbox => {
                checkbox.checked = selectAll.checked;
            });
        });
    }
    
    // 监听筛选日期选择
    const filterDate = document.getElementById('filter_date');
    const customDateRange = document.getElementById('custom_date_range');
    
    if (filterDate && customDateRange) {
        filterDate.addEventListener('change', function() {
            if (filterDate.value === 'custom') {
                customDateRange.classList.remove('d-none');
            } else {
                customDateRange.classList.add('d-none');
            }
        });
    }
    
    // 删除学生
    const deleteButtons = document.querySelectorAll('.delete-btn');
    const deleteForm = document.getElementById('deleteForm');
    const studentNameElement = document.getElementById('student-name');
    
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const studentId = this.getAttribute('data-id');
            const studentName = this.getAttribute('data-name');
            
            deleteForm.action = `<?php echo site_url('admin/students/delete/'); ?>${studentId}`;
            studentNameElement.textContent = studentName;
            
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
            deleteModal.show();
        });
    });
    
    // 批量删除
    const bulkDeleteBtn = document.getElementById('bulkDelete');
    const confirmBulkDeleteBtn = document.getElementById('confirm-bulk-delete');
    const selectedCountElement = document.getElementById('selected-count');
    const studentsForm = document.getElementById('studentsForm');
    
    if (bulkDeleteBtn && confirmBulkDeleteBtn) {
        bulkDeleteBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            const selectedCheckboxes = document.querySelectorAll('.student-checkbox:checked');
            const selectedCount = selectedCheckboxes.length;
            
            if (selectedCount === 0) {
                alert('请先选择要删除的学生');
                return;
            }
            
            selectedCountElement.textContent = selectedCount;
            const bulkDeleteModal = new bootstrap.Modal(document.getElementById('bulkDeleteModal'));
            bulkDeleteModal.show();
        });
        
        confirmBulkDeleteBtn.addEventListener('click', function() {
            const bulkConfirmDelete = document.getElementById('bulk_confirm_delete');
            
            if (!bulkConfirmDelete.checked) {
                alert('请确认删除操作');
                return;
            }
            
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'action';
            hiddenInput.value = 'delete';
            
            studentsForm.appendChild(hiddenInput);
            studentsForm.submit();
        });
    }
    
    // 导出Excel
    const exportExcelBtn = document.getElementById('exportExcel');
    
    if (exportExcelBtn) {
        exportExcelBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'action';
            hiddenInput.value = 'export';
            
            studentsForm.appendChild(hiddenInput);
            studentsForm.submit();
        });
    }
});
</script>

<?php include_once VIEW_PATH . '/footer.php'; ?> 