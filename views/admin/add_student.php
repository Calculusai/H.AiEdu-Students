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

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card glass animate-float">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h1 class="gradient-text mb-2 animate-pulse"><i class="fas fa-user-plus me-2"></i>添加学生</h1>
                            <p class="lead text-muted">创建新的学生账号并设置基本信息</p>
                        </div>
                        <a href="<?php echo site_url('admin/students'); ?>" class="btn btn-outline-primary btn-shine">
                            <i class="fas fa-arrow-left me-1"></i> 返回列表
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card glass shadow-sm mb-4">
                <div class="card-header bg-transparent py-3 border-bottom-0">
                    <h5 class="gradient-text m-0">学生信息</h5>
                </div>
                <div class="card-body p-4">
                    <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger rounded-4 border-0" role="alert">
                        <div class="d-flex">
                            <i class="fas fa-exclamation-circle fa-lg me-3 mt-1"></i>
                            <div><?php echo $error_message; ?></div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (isset($success_message)): ?>
                    <div class="alert alert-success rounded-4 border-0" role="alert">
                        <div class="d-flex">
                            <i class="fas fa-check-circle fa-lg me-3 mt-1"></i>
                            <div><?php echo $success_message; ?></div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <form id="addStudentForm" action="<?php echo site_url('admin/add_student'); ?>" method="post" class="needs-validation" novalidate>
                        <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="student_id" class="form-label">学号 <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-transparent border-end-0">
                                            <i class="fas fa-id-card"></i>
                                        </span>
                                        <input type="text" class="form-control custom-input rounded-pill ps-0 border-start-0" id="student_id" name="student_id" placeholder="输入学号" required
                                               value="<?php echo isset($form_data['student_id']) ? htmlspecialchars($form_data['student_id']) : ''; ?>">
                                    </div>
                                    <div class="invalid-feedback">
                                        请输入有效的学号
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name" class="form-label">姓名 <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-transparent border-end-0">
                                            <i class="fas fa-user"></i>
                                        </span>
                                        <input type="text" class="form-control custom-input rounded-pill ps-0 border-start-0" id="name" name="name" placeholder="输入姓名" required
                                               value="<?php echo isset($form_data['name']) ? htmlspecialchars($form_data['name']) : ''; ?>">
                                    </div>
                                    <div class="invalid-feedback">
                                        请输入学生姓名
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="class_name" class="form-label">班级</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-transparent border-end-0">
                                            <i class="fas fa-users"></i>
                                        </span>
                                        <input type="text" class="form-control custom-input rounded-pill ps-0 border-start-0" id="class_name" name="class_name" placeholder="输入班级名称"
                                               value="<?php echo isset($form_data['class_name']) ? htmlspecialchars($form_data['class_name']) : ''; ?>"
                                               list="class_list">
                                    </div>
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
                                    <div class="input-group">
                                        <span class="input-group-text bg-transparent border-end-0">
                                            <i class="fas fa-phone"></i>
                                        </span>
                                        <input type="text" class="form-control custom-input rounded-pill ps-0 border-start-0" id="contact" name="contact" placeholder="输入手机号码"
                                               value="<?php echo isset($form_data['contact']) ? htmlspecialchars($form_data['contact']) : ''; ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group mb-4">
                            <label for="email" class="form-label">邮箱</label>
                            <div class="input-group">
                                <span class="input-group-text bg-transparent border-end-0">
                                    <i class="fas fa-envelope"></i>
                                </span>
                                <input type="email" class="form-control custom-input rounded-pill ps-0 border-start-0" id="email" name="email" placeholder="输入邮箱地址"
                                       value="<?php echo isset($form_data['email']) ? htmlspecialchars($form_data['email']) : ''; ?>">
                            </div>
                            <div class="invalid-feedback">
                                请输入有效的邮箱地址
                            </div>
                        </div>
                        
                        <div class="form-group mb-4">
                            <label for="notes" class="form-label">备注</label>
                            <textarea class="form-control custom-input rounded-4" id="notes" name="notes" rows="3" placeholder="输入备注信息（可选）"><?php echo isset($form_data['notes']) ? htmlspecialchars($form_data['notes']) : ''; ?></textarea>
                        </div>
                        
                        <div class="card glass bg-light-opacity mb-4 border-0 rounded-4">
                            <div class="card-header bg-transparent border-bottom-0">
                                <h6 class="mb-0 gradient-text-light"><i class="fas fa-key me-2"></i>账号设置</h6>
                            </div>
                            <div class="card-body">
                                <div class="form-group mb-3">
                                    <label for="password" class="form-label">密码</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control custom-input rounded-pill rounded-end-0" id="password" name="password"
                                               value="<?php echo isset($form_data['password']) ? htmlspecialchars($form_data['password']) : ''; ?>"
                                               placeholder="至少6位字符">
                                        <button class="btn btn-outline-primary rounded-pill rounded-start-0" type="button" id="generatePasswordBtn">
                                            <i class="fas fa-random me-1"></i> 生成密码
                                        </button>
                                    </div>
                                    <div class="invalid-feedback">
                                        请设置学生登录密码
                                    </div>
                                    <div class="form-text">如果不填写，默认密码将与学号相同</div>
                                </div>
                                
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="generate_password" name="generate_password" value="1">
                                    <label class="form-check-label" for="generate_password">
                                        自动生成随机密码
                                    </label>
                                </div>
                                
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" value="1" id="require_password_change" name="require_password_change" checked>
                                    <label class="form-check-label" for="require_password_change">
                                        要求学生首次登录时修改密码
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group text-end">
                            <a href="<?php echo site_url('admin/students'); ?>" class="btn btn-outline-secondary me-2">
                                <i class="fas fa-times me-1"></i> 取消
                            </a>
                            <button type="submit" class="btn btn-primary btn-shine">
                                <i class="fas fa-save me-1"></i> 保存学生
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card glass shadow-sm mb-4 animate-float-delay">
                <div class="card-header bg-transparent py-3 border-bottom-0">
                    <h5 class="gradient-text m-0"><i class="fas fa-file-import me-2"></i>批量导入</h5>
                </div>
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <div class="avatar-circle bg-primary-soft mx-auto mb-3">
                            <i class="fas fa-users fa-2x text-primary"></i>
                        </div>
                        <h6>需要一次添加多名学生？</h6>
                        <p class="text-muted">您可以通过Excel表格导入多位学生的信息。</p>
                    </div>
                    
                    <div class="d-grid mb-3">
                        <button type="button" class="btn btn-gradient btn-shine" data-bs-toggle="modal" data-bs-target="#importModal">
                            <i class="fas fa-file-import me-1"></i> 导入学生数据
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="card glass shadow-sm mb-4 animate-float-delay-2">
                <div class="card-header bg-transparent py-3 border-bottom-0">
                    <h5 class="gradient-text m-0"><i class="fas fa-info-circle me-2"></i>帮助信息</h5>
                </div>
                <div class="card-body p-4">
                    <div class="help-item mb-4">
                        <div class="d-flex align-items-center mb-2">
                            <div class="icon-circle bg-info text-white">
                                <i class="fas fa-info"></i>
                            </div>
                            <h6 class="ms-2 mb-0">如何管理学生？</h6>
                        </div>
                        <p class="small text-muted ms-4 ps-2">
                            添加学生后，您可以在学生列表页面编辑学生信息，管理学生成就，或删除学生账号。
                        </p>
                    </div>
                    
                    <div class="help-item">
                        <div class="d-flex align-items-center mb-2">
                            <div class="icon-circle bg-warning text-white">
                                <i class="fas fa-award"></i>
                            </div>
                            <h6 class="ms-2 mb-0">学生成就管理</h6>
                        </div>
                        <p class="small text-muted ms-4 ps-2">
                            在学生列表中点击"成就管理"按钮，可以为学生添加或移除成就。
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 导入学生模态框 -->
<div class="modal fade modal-info" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="/admin/import_students" method="post" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="importModalLabel">导入学生</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php $csrf_token = get_csrf_token(); ?>
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    
                    <div class="text-center mb-4">
                        <div class="avatar-circle bg-primary-soft mx-auto mb-3">
                            <i class="fas fa-file-csv fa-2x text-primary"></i>
                        </div>
                    </div>
                    
                    <p>请上传包含学生信息的CSV文件：</p>
                    <div class="file-upload-wrapper mb-3">
                        <div class="custom-file-upload">
                            <input type="file" class="form-control custom-input rounded-pill" id="import_file" name="import_file" accept=".csv" required>
                        </div>
                    </div>
                    
                    <div class="card bg-light-opacity rounded-4 border-0 mb-3">
                        <div class="card-body small">
                            <h6 class="gradient-text-light mb-2">文件格式要求</h6>
                            <ul class="ps-3 mb-0 text-muted">
                                <li>表格第一行必须是表头，包含字段名</li>
                                <li>必须包含学号(student_id)和姓名(name)字段</li>
                                <li>可选字段：班级(class_name)、联系方式(contact)、邮箱(email)、备注(notes)</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="import_generate_password" name="generate_password" value="1" checked>
                        <label class="form-check-label" for="import_generate_password">
                            为导入的学生自动生成密码
                        </label>
                    </div>
                    
                    <div class="alert alert-info rounded-4 border-0">
                        <div class="d-flex">
                            <i class="fas fa-info-circle fa-lg me-3 mt-1"></i>
                            <div>如果CSV中已包含密码字段，系统将使用该密码；否则将根据设置自动生成。</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="<?php echo site_url('admin/download_template'); ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-download me-1"></i> 下载模板
                    </a>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary btn-shine">开始导入</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.custom-input, .custom-select {
    border: 1px solid rgba(0,0,0,0.1);
    padding: 0.6rem 1rem;
    transition: all 0.3s ease;
}
.custom-input:focus, .custom-select:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.25rem rgba(var(--primary-rgb), 0.25);
}
.bg-light-opacity {
    background-color: rgba(240, 242, 245, 0.5);
}
.icon-circle {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}
.avatar-circle {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}
.bg-primary-soft {
    background-color: rgba(var(--primary-rgb), 0.1);
}
.gradient-text-light {
    background: linear-gradient(45deg, var(--primary-color), #6f42c1);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    color: transparent;
}
.btn-gradient {
    background: linear-gradient(45deg, var(--primary-color), #6f42c1);
    border: none;
    color: white;
}
.btn-gradient:hover {
    background: linear-gradient(45deg, #2e59d9, #5a34a5);
    color: white;
    transform: translateY(-2px);
}
.animate-float-delay {
    animation-delay: 0.2s;
}
.animate-float-delay-2 {
    animation-delay: 0.4s;
}
.form-check-input:checked {
    background-color: var(--primary-color);
    border-color: var(--primary-color);
}
.form-switch .form-check-input {
    transition: background-position 0.25s ease-in-out, background-color 0.25s ease-in-out;
}
</style>

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
            // 生成8位随机密码，包含数字和字母
            const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
            let password = '';
            
            for (let i = 0; i < 8; i++) {
                password += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            
            passwordInput.value = password;
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