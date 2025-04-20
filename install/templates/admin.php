<?php
/**
 * 管理员账号设置模板
 */
?>

<div class="card mb-4">
    <div class="card-header">
        <h2 class="card-title">网站设置</h2>
    </div>
    <div class="card-body">
        <p class="lead">请设置网站信息和创建管理员账号。</p>
        
        <?php if (isset($error_message)): ?>
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <?php echo $error_message; ?>
        </div>
        <?php endif; ?>
        
        <form method="post" action="index.php?step=admin" class="needs-validation" novalidate>
            <h5 class="border-bottom pb-2 mb-3">网站信息</h5>
            
            <div class="mb-3">
                <label for="site_name" class="form-label">网站名称 <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="site_name" name="site_name" value="少儿编程成就展示系统" required>
            </div>
            
            <div class="mb-3">
                <label for="site_url" class="form-label">网站URL</label>
                <input type="url" class="form-control" id="site_url" name="site_url" value="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>">
                <div class="form-text">例如: https://example.com</div>
            </div>
            
            <div class="mb-3">
                <label for="default_theme" class="form-label">默认主题</label>
                <select class="form-select" id="default_theme" name="default_theme">
                    <option value="light">浅色主题</option>
                    <option value="dark">深色主题</option>
                    <option value="auto">跟随系统</option>
                </select>
            </div>
            
            <h5 class="border-bottom pb-2 mb-3 mt-4">管理员账号</h5>
            
            <div class="mb-3">
                <label for="admin_user" class="form-label">用户名 <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="admin_user" name="admin_user" value="admin" required>
            </div>
            
            <div class="mb-3">
                <label for="admin_email" class="form-label">邮箱</label>
                <input type="email" class="form-control" id="admin_email" name="admin_email">
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="admin_pass" class="form-label">密码 <span class="text-danger">*</span></label>
                    <input type="password" class="form-control" id="admin_pass" name="admin_pass" required>
                    <div class="form-text">密码长度至少为6个字符</div>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="admin_pass_confirm" class="form-label">确认密码 <span class="text-danger">*</span></label>
                    <input type="password" class="form-control" id="admin_pass_confirm" name="admin_pass_confirm" required>
                </div>
            </div>
            
            <div class="d-flex justify-content-between mt-4">
                <a href="index.php?step=database" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> 上一步
                </a>
                <button type="submit" class="btn btn-primary">
                    完成安装 <i class="bi bi-arrow-right"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// 表单验证
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form.needs-validation');
    
    form.addEventListener('submit', function(event) {
        const password = document.getElementById('admin_pass').value;
        const confirmPassword = document.getElementById('admin_pass_confirm').value;
        
        if (password !== confirmPassword) {
            event.preventDefault();
            event.stopPropagation();
            
            const confirmPasswordInput = document.getElementById('admin_pass_confirm');
            confirmPasswordInput.setCustomValidity('两次输入的密码不一致');
            
            // 显示错误消息
            const errorDiv = document.createElement('div');
            errorDiv.className = 'invalid-feedback';
            errorDiv.innerText = '两次输入的密码不一致';
            
            // 清除旧的错误消息
            const existingError = confirmPasswordInput.nextElementSibling;
            if (existingError && existingError.className === 'invalid-feedback') {
                existingError.remove();
            }
            
            confirmPasswordInput.parentNode.appendChild(errorDiv);
        } else {
            document.getElementById('admin_pass_confirm').setCustomValidity('');
        }
        
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        
        form.classList.add('was-validated');
    });
});
</script> 