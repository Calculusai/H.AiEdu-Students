<?php
$page_title = '用户登录';
$active_page = 'login';
include_once VIEW_PATH . '/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card glass animate-float card-gradient-border">
                <div class="card-header bg-transparent border-0 text-center pt-4 pb-0">
                    <div class="badge-icon bg-gradient-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width:70px;height:70px">
                        <i class="bi bi-person-circle fs-1"></i>
                    </div>
                    <h2 class="gradient-text text-center mb-3 animate-pulse fw-bold">用户登录</h2>
                    <div class="card-gradient-border mt-2 mx-auto" style="max-width: 150px;"></div>
                </div>
                
                <div class="card-body p-4 pt-2">
                    <form action="<?php echo site_url('login'); ?>" method="post">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                        
                        <div class="mb-4">
                            <label for="username" class="form-label">用户名</label>
                            <div class="input-group input-group-glow">
                                <span class="input-group-text badge-icon bg-gradient-primary text-white"><i class="bi bi-person"></i></span>
                                <input type="text" class="form-control btn-rounded" id="username" name="username" required autofocus>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="password" class="form-label">密码</label>
                            <div class="input-group input-group-glow">
                                <span class="input-group-text badge-icon bg-gradient-secondary text-white"><i class="bi bi-lock"></i></span>
                                <input type="password" class="form-control btn-rounded" id="password" name="password" required>
                            </div>
                        </div>
                        
                        <div class="mb-4 form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember" value="1">
                            <label class="form-check-label" for="remember">记住我</label>
                        </div>
                        
                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-primary btn-shine hover-scale btn-rounded animate-pulse">
                                <i class="bi bi-box-arrow-in-right me-2"></i>登录
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card glass mt-4 p-3 text-center card-gradient-border hover-scale">
                <div class="d-flex align-items-center justify-content-center">
                    <span class="badge-icon bg-gradient-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width:40px;height:40px">
                        <i class="bi bi-info-circle fs-5"></i>
                    </span>
                    <div class="text-start">
                        <p class="small mb-1">老师和管理员请使用管理账号登录。</p>
                        <p class="small mb-1">学生请使用<strong>学号</strong>作为用户名，<strong>初始密码也是学号</strong>。</p>
                        <p class="small mb-0">首次登录可能需要<a href="<?php echo site_url('profile'); ?>" class="btn-link btn-shine fw-bold">修改密码</a>，或直接<a href="<?php echo site_url('achievements'); ?>" class="btn-link btn-shine fw-bold">浏览成就展示</a>。</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
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
});
</script>

<?php include_once VIEW_PATH . '/footer.php'; ?> 