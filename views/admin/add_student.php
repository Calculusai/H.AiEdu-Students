<?php
/**
 * 管理员添加学生视图
 */
include_once VIEW_PATH . '/header.php';

// 设置面包屑导航
$breadcrumbs = [
    ['title' => '首页', 'url' => site_url()],
    ['title' => '管理控制台', 'url' => site_url('admin/dashboard')],
    ['title' => '学生管理', 'url' => site_url('admin/students')],
    ['title' => '添加学生', 'active' => true]
];
include_once VIEW_PATH . '/templates/breadcrumb.php';
?>

<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="h3 mb-3"><i class="fas fa-user-plus me-2"></i>添加学生</h1>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">学生信息</h6>
                </div>
                <div class="card-body">
                    <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo $error_message; ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (isset($success_message)): ?>
                    <div class="alert alert-success" role="alert">
                        <?php echo $success_message; ?>
                    </div>
                    <?php endif; ?>
                    
                    <form id="addStudentForm" action="<?php echo site_url('admin/add_student'); ?>" method="post" class="needs-validation" novalidate>
                        <style>
                            /* 确保表单元素可见 */
                            .form-control, .form-select, .form-check-input {
                                display: block !important;
                                opacity: 1 !important;
                                visibility: visible !important;
                            }
                            .form-label {
                                display: block;
                                margin-bottom: 0.5rem;
                            }
                            .form-group {
                                margin-bottom: 1rem;
                            }
                        </style>
                        
                        <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="student_id" class="form-label">学号 <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="student_id" name="student_id" placeholder="输入学号" required
                                           value="<?php echo isset($form_data['student_id']) ? htmlspecialchars($form_data['student_id']) : ''; ?>">
                                    <div class="invalid-feedback">
                                        请输入有效的学号
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name" class="form-label">姓名 <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name" placeholder="输入姓名" required
                                           value="<?php echo isset($form_data['name']) ? htmlspecialchars($form_data['name']) : ''; ?>">
                                    <div class="invalid-feedback">
                                        请输入学生姓名
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="class_name" class="form-label">班级</label>
                                    <input type="text" class="form-control" id="class_name" name="class_name" placeholder="输入班级名称"
                                           value="<?php echo isset($form_data['class_name']) ? htmlspecialchars($form_data['class_name']) : ''; ?>"
                                           list="class_list">
                                    <datalist id="class_list">
                                        <?php if (!empty($classes)): ?>
                                            <?php foreach ($classes as $class): ?>
                                            <option value="<?php echo htmlspecialchars($class); ?>">
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </datalist>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="contact" class="form-label">联系方式</label>
                                    <input type="text" class="form-control" id="contact" name="contact" placeholder="输入手机号码"
                                           value="<?php echo isset($form_data['contact']) ? htmlspecialchars($form_data['contact']) : ''; ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label for="email" class="form-label">邮箱</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="输入邮箱地址"
                                   value="<?php echo isset($form_data['email']) ? htmlspecialchars($form_data['email']) : ''; ?>">
                            <div class="invalid-feedback">
                                请输入有效的邮箱地址
                            </div>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label for="notes" class="form-label">备注</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="输入备注信息（可选）"><?php echo isset($form_data['notes']) ? htmlspecialchars($form_data['notes']) : ''; ?></textarea>
                        </div>
                        
                        <div class="card bg-light mb-3">
                            <div class="card-header">
                                <h6 class="mb-0">账号设置</h6>
                            </div>
                            <div class="card-body">
                                <div class="form-group mb-3">
                                    <label for="password" class="form-label">密码</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="password" name="password"
                                               value="<?php echo isset($form_data['password']) ? htmlspecialchars($form_data['password']) : ''; ?>"
                                               placeholder="至少6位字符">
                                        <button class="btn btn-outline-secondary" type="button" id="generatePasswordBtn">
                                            <i class="fas fa-key me-1"></i> 生成密码
                                        </button>
                                    </div>
                                    <div class="invalid-feedback">
                                        请设置学生登录密码
                                    </div>
                                    <div class="form-text">如果不填写，默认密码将与学号相同</div>
                                </div>
                                
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="generate_password" name="generate_password" value="1">
                                    <label class="form-check-label" for="generate_password">
                                        自动生成随机密码
                                    </label>
                                </div>
                                
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" value="1" id="require_password_change" name="require_password_change" checked>
                                    <label class="form-check-label" for="require_password_change">
                                        要求学生首次登录时修改密码
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group text-end">
                            <a href="<?php echo site_url('admin/students'); ?>" class="btn btn-outline-secondary me-2">
                                <i class="fas fa-arrow-left me-1"></i> 返回列表
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> 保存学生
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">批量添加学生</h6>
                </div>
                <div class="card-body">
                    <p>需要一次添加多名学生？</p>
                    <p>您可以通过Excel表格导入多位学生的信息。</p>
                    
                    <div class="d-grid mb-3">
                        <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#importModal">
                            <i class="fas fa-file-import me-1"></i> 导入学生数据
                        </button>
                    </div>
                    
                    <hr>
                    
                    <div class="small text-muted">
                        <p class="mb-1"><strong>提示：</strong></p>
                        <ul class="ps-3 mb-0">
                            <li>学号为必填项，且必须唯一</li>
                            <li>系统会自动检测重复的学号</li>
                            <li>如未设置密码，将使用学号作为默认密码</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">帮助信息</h6>
                </div>
                <div class="card-body">
                    <div class="small">
                        <p class="mb-2"><i class="fas fa-info-circle me-1 text-info"></i> <strong>如何管理学生？</strong></p>
                        <p class="mb-3">添加学生后，您可以在学生列表页面编辑学生信息，管理学生成就，或删除学生账号。</p>
                        
                        <p class="mb-2"><i class="fas fa-award me-1 text-warning"></i> <strong>学生成就管理</strong></p>
                        <p class="mb-0">在学生列表中点击"成就管理"按钮，可以为学生添加或移除成就。</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 导入学生模态框 -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?php echo site_url('admin/import_students'); ?>" method="post" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="importModalLabel">导入学生</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                    
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
                        <input class="form-check-input" type="checkbox" id="import_generate_password" name="generate_password" value="1" checked>
                        <label class="form-check-label" for="import_generate_password">
                            为导入的学生自动生成密码
                        </label>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>如果Excel中已包含密码字段，系统将使用该密码；否则将根据设置自动生成。
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="<?php echo site_url('admin/download_template'); ?>" class="btn btn-link text-decoration-none">下载模板</a>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary">开始导入</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 表单验证
    const form = document.getElementById('addStudentForm');
    
    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        
        form.classList.add('was-validated');
    });
    
    // 生成随机密码
    const generatePasswordBtn = document.getElementById('generatePasswordBtn');
    const passwordInput = document.getElementById('password');
    const generatePasswordCheck = document.getElementById('generate_password');
    
    if (generatePasswordBtn) {
        generatePasswordBtn.addEventListener('click', function () {
            const randomPassword = Math.random().toString(36).slice(-8);
            passwordInput.value = randomPassword;
        });
    }
    
    // 自动生成密码选项
    if (generatePasswordCheck) {
        generatePasswordCheck.addEventListener('change', function() {
            if (this.checked) {
                passwordInput.value = '';
                passwordInput.readOnly = true;
                generatePasswordBtn.disabled = true;
            } else {
                passwordInput.readOnly = false;
                generatePasswordBtn.disabled = false;
            }
        });
    }
    
    // 如果学号输入，但密码为空，则默认使用学号作为密码
    const studentIdInput = document.getElementById('student_id');
    
    studentIdInput.addEventListener('blur', function() {
        if (studentIdInput.value && !passwordInput.value) {
            passwordInput.value = studentIdInput.value;
        }
    });
    
    // 页面加载时初始化自动生成密码选项状态
    if (generatePasswordCheck && generatePasswordCheck.checked) {
        passwordInput.value = '';
        passwordInput.readOnly = true;
        generatePasswordBtn.disabled = true;
    }
});
</script>

<?php include_once VIEW_PATH . '/footer.php'; ?> 