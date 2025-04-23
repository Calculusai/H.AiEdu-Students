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

<!-- 导入自定义CSS样式 -->
<link rel="stylesheet" href="<?php echo site_url('assets/css/user-profile.css'); ?>">

<div class="up-container py-4">
    <?php if ($flash_message): ?>
    <div class="up-alert up-alert-<?php echo $flash_type; ?>">
        <div class="up-alert-icon">
            <i class="bi bi-info-circle"></i>
        </div>
        <div class="up-alert-content">
            <?php echo $flash_message; ?>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="关闭"></button>
    </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-4">
            <!-- 用户信息卡片 -->
            <div class="up-profile-header up-animate-float">
                <div class="up-avatar-container">
                    <div class="up-avatar-wrapper">
                        <div class="up-avatar">
                            <i class="bi bi-person-circle"></i>
                        </div>
                    </div>
                    <h3 class="up-username"><?php echo htmlspecialchars(isset($user['username']) ? $user['username'] : ''); ?></h3>
                    <div class="up-role-badge">
                        <?php echo isset($user['role']) && $user['role'] == 'admin' ? '管理员' : '学生'; ?>
                    </div>
                    
                    <?php if (isset($user['role']) && $user['role'] == 'student' && $student): ?>
                    <a href="<?php echo site_url('student/' . $student['id']); ?>" class="up-btn-achievements">
                        <i class="bi bi-trophy"></i> 查看我的成就
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- 账号信息卡片 -->
            <div class="up-card">
                <div class="up-card-header">
                    <h5 class="up-info-title">
                        <i class="bi bi-shield-lock"></i> 账号信息
                    </h5>
                </div>
                <div class="up-card-body">
                    <div class="up-info-item">
                        <div class="up-info-content">
                            <p class="up-info-label">账号状态</p>
                            <p class="mb-0">
                                <span class="badge bg-gradient-<?php echo isset($user['status']) && $user['status'] ? 'success' : 'danger'; ?> btn-shine">
                                    <?php echo isset($user['status']) && $user['status'] ? '正常' : '已禁用'; ?>
                                </span>
                            </p>
                        </div>
                        <div class="up-info-icon <?php echo isset($user['status']) && $user['status'] ? 'success' : 'danger'; ?>">
                            <i class="bi bi-shield-<?php echo isset($user['status']) && $user['status'] ? 'check' : 'x'; ?>"></i>
                        </div>
                    </div>
                    
                    <div class="up-info-item">
                        <div class="up-info-content">
                            <p class="up-info-label">上次登录</p>
                            <p class="up-info-value"><?php echo isset($user['last_login']) && $user['last_login'] ? date('Y-m-d H:i', strtotime($user['last_login'])) : '从未登录'; ?></p>
                        </div>
                        <div class="up-info-icon info">
                            <i class="bi bi-clock-history"></i>
                        </div>
                    </div>
                    
                    <div class="up-info-item">
                        <div class="up-info-content">
                            <p class="up-info-label">注册时间</p>
                            <p class="up-info-value"><?php echo isset($user['created_at']) ? date('Y-m-d', strtotime($user['created_at'])) : '未知'; ?></p>
                        </div>
                        <div class="up-info-icon success">
                            <i class="bi bi-calendar-check"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <!-- 选项卡导航 -->
            <div class="up-card mb-4">
                <div class="up-tabs">
                    <div class="up-tab-item active" data-tab="student-info">
                        <i class="bi bi-mortarboard"></i> 学生信息
                    </div>
                    <div class="up-tab-item" data-tab="account-settings">
                        <i class="bi bi-gear"></i> 账号设置
                    </div>
                    <div class="up-tab-item" data-tab="change-password">
                        <i class="bi bi-key"></i> 密码修改
                    </div>
                </div>
            </div>
            
            <!-- 表单容器 -->
            <div class="up-card">
                <div class="up-card-body">
                    <form action="<?php echo site_url('profile/update'); ?>" method="post" class="needs-validation" novalidate>
                        <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                        
                        <!-- 学生信息部分 -->
                        <div class="up-tab-content" id="student-info">
                            <?php if (isset($user['role']) && $user['role'] == 'student' && $student): ?>
                            <h5 class="up-form-section-title">
                                <i class="bi bi-mortarboard"></i> 学生信息
                            </h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="up-form-group">
                                        <label for="student_id" class="up-form-label">学号</label>
                                        <div class="up-input-group">
                                            <div class="up-input-icon">
                                                <i class="bi bi-hash"></i>
                                            </div>
                                            <input type="text" class="up-form-control" id="student_id" value="<?php echo htmlspecialchars($student['student_id']); ?>" readonly>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="up-form-group">
                                        <label for="name" class="up-form-label">姓名</label>
                                        <div class="up-input-group">
                                            <div class="up-input-icon">
                                                <i class="bi bi-person"></i>
                                            </div>
                                            <input type="text" class="up-form-control" id="name" value="<?php echo htmlspecialchars($student['name']); ?>" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="up-form-group">
                                        <label for="class_name" class="up-form-label">班级</label>
                                        <div class="up-input-group">
                                            <div class="up-input-icon">
                                                <i class="bi bi-people"></i>
                                            </div>
                                            <input type="text" class="up-form-control" id="class_name" value="<?php echo htmlspecialchars($student['class_name'] ?: '未设置'); ?>" readonly>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="up-form-group">
                                        <label for="contact" class="up-form-label">联系方式</label>
                                        <div class="up-input-group">
                                            <div class="up-input-icon">
                                                <i class="bi bi-telephone"></i>
                                            </div>
                                            <input type="text" class="up-form-control" id="contact" value="<?php echo htmlspecialchars($student['contact'] ?: '未设置'); ?>" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php else: ?>
                            <div class="up-empty-state">
                                <i class="bi bi-mortarboard"></i>
                                <h4>没有学生信息</h4>
                                <p>您的账号未关联学生信息</p>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- 账号设置部分 -->
                        <div class="up-tab-content" id="account-settings" style="display: none;">
                            <h5 class="up-form-section-title">
                                <i class="bi bi-gear"></i> 账号设置
                            </h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="up-form-group">
                                        <label for="username" class="up-form-label">用户名</label>
                                        <div class="up-input-group">
                                            <div class="up-input-icon">
                                                <i class="bi bi-person-badge"></i>
                                            </div>
                                            <input type="text" class="up-form-control" id="username" value="<?php echo htmlspecialchars(isset($user['username']) ? $user['username'] : ''); ?>" readonly>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="up-form-group">
                                        <label for="email" class="up-form-label">邮箱</label>
                                        <div class="up-input-group">
                                            <div class="up-input-icon">
                                                <i class="bi bi-envelope"></i>
                                            </div>
                                            <input type="email" class="up-form-control" id="email" name="email" value="<?php echo isset($user['email']) ? htmlspecialchars($user['email']) : ''; ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- 隐藏主题偏好字段 -->
                            <input type="hidden" id="theme_preference" name="theme_preference" value="light">
                        </div>
                        
                        <!-- 密码修改部分 -->
                        <div class="up-tab-content" id="change-password" style="display: none;">
                            <h5 class="up-form-section-title">
                                <i class="bi bi-key"></i> 密码修改
                            </h5>
                            <div class="up-form-group">
                                <label for="current_password" class="up-form-label">当前密码</label>
                                <div class="up-input-group">
                                    <div class="up-input-icon">
                                        <i class="bi bi-key"></i>
                                    </div>
                                    <input type="password" class="up-form-control" id="current_password" name="current_password">
                                </div>
                                <div class="up-form-text">如需修改密码，请先输入当前密码</div>
                            </div>
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="up-form-group">
                                        <label for="new_password" class="up-form-label">新密码</label>
                                        <div class="up-input-group">
                                            <div class="up-input-icon">
                                                <i class="bi bi-lock"></i>
                                            </div>
                                            <input type="password" class="up-form-control" id="new_password" name="new_password">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="up-form-group">
                                        <label for="confirm_password" class="up-form-label">确认新密码</label>
                                        <div class="up-input-group">
                                            <div class="up-input-icon">
                                                <i class="bi bi-check2-circle"></i>
                                            </div>
                                            <input type="password" class="up-form-control" id="confirm_password" name="confirm_password">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                            <button type="submit" class="up-btn up-btn-primary up-btn-shine">
                                <i class="bi bi-save me-1"></i> 保存修改
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// 表单验证和选项卡切换
document.addEventListener('DOMContentLoaded', function() {
    // 选项卡切换功能
    const tabItems = document.querySelectorAll('.up-tab-item');
    const tabContents = document.querySelectorAll('.up-tab-content');

    tabItems.forEach(item => {
        item.addEventListener('click', function() {
            // 移除所有选项卡的活动状态
            tabItems.forEach(tab => tab.classList.remove('active'));
            // 隐藏所有内容
            tabContents.forEach(content => content.style.display = 'none');
            
            // 激活当前选项卡
            this.classList.add('active');
            
            // 显示对应内容
            const tabId = this.getAttribute('data-tab');
            document.getElementById(tabId).style.display = 'block';
        });
    });

    // 表单验证
    const form = document.querySelector('form.needs-validation');
    
    if (form) {
        const inputs = document.querySelectorAll('.up-form-control');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.closest('.up-input-group').classList.add('focus');
            });
            input.addEventListener('blur', function() {
                this.closest('.up-input-group').classList.remove('focus');
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