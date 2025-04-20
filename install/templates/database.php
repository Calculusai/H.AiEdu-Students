<?php
/**
 * 数据库配置模板
 */
?>

<div class="card mb-4">
    <div class="card-header">
        <h2 class="card-title">数据库配置</h2>
    </div>
    <div class="card-body">
        <p class="lead">请填写您的数据库连接信息。如果数据库不存在，系统将尝试创建。</p>
        
        <?php if (isset($error_message)): ?>
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <?php echo $error_message; ?>
        </div>
        <?php endif; ?>
        
        <form method="post" action="index.php?step=database" class="needs-validation" novalidate>
            <div class="mb-3">
                <label for="db_host" class="form-label">数据库主机 <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="db_host" name="db_host" value="localhost" required>
                <div class="form-text">通常为 localhost 或数据库服务器的IP地址</div>
            </div>
            
            <div class="mb-3">
                <label for="db_name" class="form-label">数据库名称 <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="db_name" name="db_name" value="achievements" required>
                <div class="form-text">如果不存在会尝试创建</div>
            </div>
            
            <div class="mb-3">
                <label for="db_user" class="form-label">数据库用户名 <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="db_user" name="db_user" required>
            </div>
            
            <div class="mb-3">
                <label for="db_pass" class="form-label">数据库密码</label>
                <input type="password" class="form-control" id="db_pass" name="db_pass">
            </div>
            
            <div class="mb-3">
                <label for="table_prefix" class="form-label">数据表前缀</label>
                <input type="text" class="form-control" id="table_prefix" name="table_prefix" value="ach_">
                <div class="form-text">如果您在同一数据库中有多个应用，使用不同的前缀可以避免表名冲突</div>
            </div>
            
            <div class="d-flex justify-content-between mt-4">
                <a href="index.php?step=requirements" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> 上一步
                </a>
                <button type="submit" class="btn btn-primary">
                    下一步 <i class="bi bi-arrow-right"></i>
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
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        
        form.classList.add('was-validated');
    });
});
</script> 