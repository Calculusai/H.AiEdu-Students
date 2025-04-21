<?php
/**
 * 用户个人资料页面
 */
include_once VIEW_PATH . '/header.php';

// 获取消息提示
$flash_message = isset($_SESSION['flash_message']) ? $_SESSION['flash_message'] : null;
$flash_type = isset($_SESSION['flash_type']) ? $_SESSION['flash_type'] : 'info';

// 清除会话消息
unset($_SESSION['flash_message']);
unset($_SESSION['flash_type']);
?>

<div class="container py-5">
    <div class="row g-4">
        <div class="col-md-3">
            <div class="card glass animate-float card-gradient-border mb-4">
                <div class="card-body text-center p-4">
                    <div class="badge-icon bg-gradient-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width:100px;height:100px">
                        <i class="bi bi-person-circle" style="font-size: 3.5rem;"></i>
                    </div>
                    <h4 class="mb-1 gradient-text"><?php echo htmlspecialchars(isset($user['username']) ? $user['username'] : ''); ?></h4>
                    <p class="mb-3"><?php echo isset($user['role']) && $user['role'] == 'admin' ? '管理员' : '学生'; ?></p>
                    
                    <?php if (isset($user['role']) && $user['role'] == 'student' && $student): ?>
                    <div class="d-grid gap-2 mt-3">
                        <a href="<?php echo site_url('student/' . $student['id']); ?>" class="btn btn-primary btn-rounded btn-shine hover-scale">
                            <i class="bi bi-trophy me-1"></i> 查看我的成就
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card glass animate-float card-gradient-border">
                <div class="card-header glass border-0">
                    <h5 class="card-title mb-0 gradient-text">账号信息</h5>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center glass p-3 btn-rounded mb-3 hover-scale">
                            <div>
                                <p class="mb-0 small">账号状态</p>
                                <p class="mb-0">
                                    <span class="badge bg-gradient-<?php echo isset($user['status']) && $user['status'] ? 'success' : 'danger'; ?> btn-shine">
                                        <?php echo isset($user['status']) && $user['status'] ? '正常' : '已禁用'; ?>
                                    </span>
                                </p>
                            </div>
                            <i class="bi bi-shield-<?php echo isset($user['status']) && $user['status'] ? 'check' : 'x'; ?> fs-3 text-<?php echo isset($user['status']) && $user['status'] ? 'success' : 'danger'; ?>"></i>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center glass p-3 btn-rounded mb-3 hover-scale">
                            <div>
                                <p class="mb-0 small">上次登录</p>
                                <p class="mb-0 gradient-text"><?php echo isset($user['last_login']) && $user['last_login'] ? date('Y-m-d H:i', strtotime($user['last_login'])) : '从未登录'; ?></p>
                            </div>
                            <i class="bi bi-clock-history fs-3 text-info"></i>
                        </div>
                    </div>
                    <div class="mb-0">
                        <div class="d-flex justify-content-between align-items-center glass p-3 btn-rounded hover-scale">
                            <div>
                                <p class="mb-0 small">注册时间</p>
                                <p class="mb-0 gradient-text"><?php echo isset($user['created_at']) ? date('Y-m-d', strtotime($user['created_at'])) : '未知'; ?></p>
                            </div>
                            <i class="bi bi-calendar-check fs-3 text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-9">
            <?php if ($flash_message): ?>
            <div class="alert glass alert-dismissible fade show mb-4 p-3 animate-float" role="alert">
                <div class="d-flex align-items-center">
                    <div class="badge-icon bg-gradient-<?php echo $flash_type; ?> text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width:40px;height:40px">
                        <i class="bi bi-info-circle fs-4"></i>
                    </div>
                    <div>
                        <?php echo $flash_message; ?>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>
            
            <div class="card glass animate-float card-gradient-border">
                <div class="card-header glass border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="m-0 gradient-text">个人资料</h5>
                </div>
                <div class="card-body p-4">
                    <form action="<?php echo site_url('profile/update'); ?>" method="post" class="needs-validation" novalidate>
                        <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                        
                        <?php if (isset($user['role']) && $user['role'] == 'student' && $student): ?>
                        <!-- 学生信息部分 -->
                        <div class="card glass mb-4">
                            <div class="card-header glass border-0">
                                <h5 class="mb-0 gradient-text">学生信息</h5>
                            </div>
                            <div class="card-body">
                                <div class="row mb-4 g-3">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="student_id" class="form-label">学号</label>
                                            <div class="input-group input-group-glow">
                                                <span class="input-group-text badge-icon bg-gradient-primary text-white"><i class="bi bi-hash"></i></span>
                                                <input type="text" class="form-control btn-rounded" id="student_id" value="<?php echo htmlspecialchars($student['student_id']); ?>" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="name" class="form-label">姓名</label>
                                            <div class="input-group input-group-glow">
                                                <span class="input-group-text badge-icon bg-gradient-secondary text-white"><i class="bi bi-person"></i></span>
                                                <input type="text" class="form-control btn-rounded" id="name" value="<?php echo htmlspecialchars($student['name']); ?>" readonly>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row mb-4 g-3">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="class_name" class="form-label">班级</label>
                                            <div class="input-group input-group-glow">
                                                <span class="input-group-text badge-icon bg-gradient-info text-white"><i class="bi bi-people"></i></span>
                                                <input type="text" class="form-control btn-rounded" id="class_name" value="<?php echo htmlspecialchars($student['class_name'] ?: '未设置'); ?>" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="contact" class="form-label">联系方式</label>
                                            <div class="input-group input-group-glow">
                                                <span class="input-group-text badge-icon bg-gradient-warning text-white"><i class="bi bi-telephone"></i></span>
                                                <input type="text" class="form-control btn-rounded" id="contact" value="<?php echo htmlspecialchars($student['contact'] ?: '未设置'); ?>" readonly>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <!-- 账号设置部分 -->
                        <div class="card glass mb-4">
                            <div class="card-header glass border-0">
                                <h5 class="mb-0 gradient-text">账号设置</h5>
                            </div>
                            <div class="card-body">
                                <div class="form-group mb-3">
                                    <label for="username" class="form-label">用户名</label>
                                    <div class="input-group input-group-glow">
                                        <span class="input-group-text badge-icon bg-gradient-primary text-white"><i class="bi bi-person-badge"></i></span>
                                        <input type="text" class="form-control btn-rounded" id="username" value="<?php echo htmlspecialchars(isset($user['username']) ? $user['username'] : ''); ?>" readonly>
                                    </div>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label for="email" class="form-label">邮箱</label>
                                    <div class="input-group input-group-glow">
                                        <span class="input-group-text badge-icon bg-gradient-secondary text-white"><i class="bi bi-envelope"></i></span>
                                        <input type="email" class="form-control btn-rounded" id="email" name="email" value="<?php echo isset($user['email']) ? htmlspecialchars($user['email']) : ''; ?>">
                                    </div>
                                </div>
                                
                                <!-- 隐藏主题偏好字段 -->
                                <input type="hidden" id="theme_preference" name="theme_preference" value="light">
                            </div>
                        </div>
                        
                        <!-- 密码修改部分 -->
                        <div class="card glass mb-4">
                            <div class="card-header glass border-0">
                                <h5 class="mb-0 gradient-text">密码修改</h5>
                            </div>
                            <div class="card-body">
                                <div class="form-group mb-3">
                                    <label for="current_password" class="form-label">当前密码</label>
                                    <div class="input-group input-group-glow">
                                        <span class="input-group-text badge-icon bg-gradient-primary text-white"><i class="bi bi-key"></i></span>
                                        <input type="password" class="form-control btn-rounded" id="current_password" name="current_password">
                                    </div>
                                    <div class="form-text">如需修改密码，请先输入当前密码</div>
                                </div>
                                
                                <div class="row mb-4 g-3">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="new_password" class="form-label">新密码</label>
                                            <div class="input-group input-group-glow">
                                                <span class="input-group-text badge-icon bg-gradient-secondary text-white"><i class="bi bi-lock"></i></span>
                                                <input type="password" class="form-control btn-rounded" id="new_password" name="new_password">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="confirm_password" class="form-label">确认新密码</label>
                                            <div class="input-group input-group-glow">
                                                <span class="input-group-text badge-icon bg-gradient-info text-white"><i class="bi bi-check2-circle"></i></span>
                                                <input type="password" class="form-control btn-rounded" id="confirm_password" name="confirm_password">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="submit" class="btn btn-primary btn-rounded btn-shine hover-scale">
                                <i class="bi bi-save me-1"></i> 保存修改
                            </button>
                        </div>
                    </form>
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

.input-group-glow {
    transition: all 0.3s ease;
}

.input-group-glow:focus-within {
    box-shadow: 0 0 15px rgba(var(--primary-rgb), 0.5);
}

.input-focus-glow {
    box-shadow: 0 0 15px rgba(var(--primary-rgb), 0.5);
}

.btn-rounded, .input-group-text, .form-control {
    border-radius: 10px;
}

.form-control:focus {
    border-color: var(--primary-color);
    box-shadow: none;
}
</style>

<script>
// 表单验证
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form.needs-validation');
    
    if (form) {
        // 添加输入时的微动画效果
        const inputs = document.querySelectorAll('input');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.closest('.input-group').classList.add('input-focus-glow');
            });
            input.addEventListener('blur', function() {
                this.closest('.input-group').classList.remove('input-focus-glow');
            });
        });
        
        form.addEventListener('submit', function(event) {
            let isValid = true;
            
            // 获取密码字段
            const currentPassword = document.getElementById('current_password');
            const newPassword = document.getElementById('new_password');
            const confirmPassword = document.getElementById('confirm_password');
            
            // 检查密码修改
            if (currentPassword.value.trim() !== '' || 
                newPassword.value.trim() !== '' || 
                confirmPassword.value.trim() !== '') {
                
                // 移除之前的验证提示
                document.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
                document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                
                // 确保所有密码字段都已填写
                if (currentPassword.value.trim() === '') {
                    currentPassword.classList.add('is-invalid');
                    addFeedback(currentPassword, '请输入当前密码');
                    isValid = false;
                }
                
                if (newPassword.value.trim() === '') {
                    newPassword.classList.add('is-invalid');
                    addFeedback(newPassword, '请输入新密码');
                    isValid = false;
                }
                
                if (confirmPassword.value.trim() === '') {
                    confirmPassword.classList.add('is-invalid');
                    addFeedback(confirmPassword, '请确认新密码');
                    isValid = false;
                }
                
                // 检查新密码长度
                if (newPassword.value.trim() !== '' && newPassword.value.trim().length < 6) {
                    newPassword.classList.add('is-invalid');
                    addFeedback(newPassword, '新密码长度必须至少为6个字符');
                    isValid = false;
                }
                
                // 检查两次输入的密码是否一致
                if (newPassword.value.trim() !== '' && confirmPassword.value.trim() !== '' && 
                    newPassword.value.trim() !== confirmPassword.value.trim()) {
                    confirmPassword.classList.add('is-invalid');
                    addFeedback(confirmPassword, '两次输入的密码不一致');
                    isValid = false;
                }
            }
            
            // 如果验证失败，阻止表单提交
            if (!isValid) {
                event.preventDefault();
                event.stopPropagation();
            }
        });
    }
    
    // 辅助函数：添加反馈提示
    function addFeedback(element, message) {
        if (!element.nextElementSibling || !element.nextElementSibling.classList.contains('invalid-feedback')) {
            const feedback = document.createElement('div');
            feedback.className = 'invalid-feedback';
            feedback.textContent = message;
            element.parentNode.appendChild(feedback);
        }
    }
});
</script>

<?php include_once VIEW_PATH . '/footer.php'; ?> 