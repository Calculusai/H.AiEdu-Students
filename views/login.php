<?php
$page_title = '用户登录';
$active_page = 'login';
include_once VIEW_PATH . '/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card dopamine-card">
            <div class="card-body p-4">
                <h2 class="text-center mb-4">用户登录</h2>
                
                <form action="<?php echo site_url('login'); ?>" method="post">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    
                    <div class="mb-3">
                        <label for="username" class="form-label">用户名</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                            <input type="text" class="form-control" id="username" name="username" required autofocus>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">密码</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember" value="1">
                        <label class="form-check-label" for="remember">记住我</label>
                    </div>
                    
                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-primary dopamine-button">
                            <i class="bi bi-box-arrow-in-right me-2"></i>登录
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="text-center mt-4 text-muted small">
            <p>本系统仅限教师和管理员登录。</p>
            <p>学生可以<a href="<?php echo site_url('achievements'); ?>">直接查看成就展示页面</a>。</p>
        </div>
    </div>
</div>

<?php include_once VIEW_PATH . '/footer.php'; ?> 