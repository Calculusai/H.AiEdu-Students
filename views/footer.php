    </main>
    
    <footer class="mt-5 py-4 glass text-center animate-float">
        <div class="container">
            <div class="badge-icon bg-gradient-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width:50px;height:50px">
                <i class="bi bi-stars fs-4"></i>
            </div>
            <p class="gradient-text mb-3 fw-bold">
                版权所有 © <?php echo date('Y'); ?> <?php echo get_setting('site_name', SITE_NAME); ?> - <?php echo get_setting('footer_copyright_suffix', '保留一切权利'); ?>
            </p>
            <div class="d-flex justify-content-center flex-wrap mb-3">
                <a href="#" class="btn btn-sm btn-primary btn-rounded btn-shine mx-2 mb-2 hover-scale">
                    <i class="bi bi-info-circle me-1"></i>关于我们
                </a>
                <a href="#" class="btn btn-sm btn-secondary btn-rounded btn-shine mx-2 mb-2 hover-scale">
                    <i class="bi bi-file-text me-1"></i>使用条款
                </a>
                <a href="#" class="btn btn-sm btn-info btn-rounded btn-shine mx-2 mb-2 hover-scale">
                    <i class="bi bi-shield-lock me-1"></i>隐私政策
                </a>
                <a href="#" class="btn btn-sm btn-warning btn-rounded btn-shine mx-2 mb-2 hover-scale">
                    <i class="bi bi-envelope me-1"></i>联系我们
                </a>
            </div>
            <div class="card-gradient-border mt-2 mx-auto" style="max-width: 100px;"></div>
        </div>
    </footer>
    
    <?php if (isset($extra_js)): ?>
    <!-- 额外JavaScript -->
    <?php echo $extra_js; ?>
    <?php endif; ?>
</body>
</html> 