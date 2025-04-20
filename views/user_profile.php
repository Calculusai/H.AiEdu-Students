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

<div class="container mt-5 mb-5">
    <div class="row">
        <div class="col-md-3">
            <div class="card dopamine-card mb-4">
                <div class="card-body text-center">
                    <div class="avatar-placeholder mb-3">
                        <i class="bi bi-person-circle" style="font-size: 5rem;"></i>
                    </div>
                    <h4 class="mb-1"><?php echo htmlspecialchars(isset($user['username']) ? $user['username'] : ''); ?></h4>
                    <p class="text-muted"><?php echo isset($user['role']) && $user['role'] == 'admin' ? '管理员' : '学生'; ?></p>
                    
                    <?php if (isset($user['role']) && $user['role'] == 'student' && $student): ?>
                    <div class="d-grid gap-2 mt-3">
                        <a href="<?php echo site_url('student/' . $student['id']); ?>" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-trophy me-1"></i> 查看我的成就
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">账号信息</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <p class="mb-1 small text-muted">账号状态</p>
                        <p class="mb-0">
                            <span class="badge bg-<?php echo isset($user['status']) && $user['status'] ? 'success' : 'danger'; ?>">
                                <?php echo isset($user['status']) && $user['status'] ? '正常' : '已禁用'; ?>
                            </span>
                        </p>
                    </div>
                    <div class="mb-3">
                        <p class="mb-1 small text-muted">上次登录</p>
                        <p class="mb-0"><?php echo isset($user['last_login']) && $user['last_login'] ? date('Y-m-d H:i', strtotime($user['last_login'])) : '从未登录'; ?></p>
                    </div>
                    <div class="mb-0">
                        <p class="mb-1 small text-muted">注册时间</p>
                        <p class="mb-0"><?php echo isset($user['created_at']) ? date('Y-m-d', strtotime($user['created_at'])) : '未知'; ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-9">
            <?php if ($flash_message): ?>
            <div class="alert alert-<?php echo $flash_type; ?> alert-dismissible fade show mb-4" role="alert">
                <?php echo $flash_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>
            
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">个人资料</h6>
                </div>
                <div class="card-body">
                    <form action="<?php echo site_url('profile/update'); ?>" method="post" class="needs-validation" novalidate>
                        <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                        
                        <?php if (isset($user['role']) && $user['role'] == 'student' && $student): ?>
                        <!-- 学生信息部分 -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="student_id" class="form-label">学号</label>
                                    <input type="text" class="form-control" id="student_id" value="<?php echo htmlspecialchars($student['student_id']); ?>" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="name" class="form-label">姓名</label>
                                    <input type="text" class="form-control" id="name" value="<?php echo htmlspecialchars($student['name']); ?>" readonly>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="class_name" class="form-label">班级</label>
                                    <input type="text" class="form-control" id="class_name" value="<?php echo htmlspecialchars($student['class_name'] ?: '未设置'); ?>" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="contact" class="form-label">联系方式</label>
                                    <input type="text" class="form-control" id="contact" value="<?php echo htmlspecialchars($student['contact'] ?: '未设置'); ?>" readonly>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <!-- 账号设置部分 -->
                        <h5 class="border-bottom pb-2 mb-4">账号设置</h5>
                        
                        <div class="form-group mb-3">
                            <label for="username" class="form-label">用户名</label>
                            <input type="text" class="form-control" id="username" value="<?php echo htmlspecialchars(isset($user['username']) ? $user['username'] : ''); ?>" readonly>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label for="email" class="form-label">邮箱</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($user['email']) ? htmlspecialchars($user['email']) : ''; ?>">
                        </div>
                        
                        <!-- 隐藏主题偏好字段 -->
                        <input type="hidden" id="theme_preference" name="theme_preference" value="light">
                        
                        <!-- 密码修改部分 -->
                        <h5 class="border-bottom pb-2 mb-4">密码修改</h5>
                        
                        <div class="form-group mb-3">
                            <label for="current_password" class="form-label">当前密码</label>
                            <input type="password" class="form-control" id="current_password" name="current_password">
                            <div class="form-text">如需修改密码，请先输入当前密码</div>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="new_password" class="form-label">新密码</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="confirm_password" class="form-label">确认新密码</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="submit" class="btn btn-primary">
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
    background-color: #e9ecef;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #adb5bd;
}
</style>

<script>
// 表单验证
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form.needs-validation');
    
    if (form) {
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