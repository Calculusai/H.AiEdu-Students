    </main>
    
    <footer class="mt-5 py-4 bg-light text-center text-muted">
        <div class="container">
            <p>
                &copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?> - 所有权利保留
            </p>
            <p class="small">
                <a href="#" class="text-muted mx-2">关于我们</a>
                <a href="#" class="text-muted mx-2">使用条款</a>
                <a href="#" class="text-muted mx-2">隐私政策</a>
                <a href="#" class="text-muted mx-2">联系我们</a>
            </p>
        </div>
    </footer>
    
    <?php if (isset($extra_js)): ?>
    <!-- 额外JavaScript -->
    <?php echo $extra_js; ?>
    <?php endif; ?>
</body>
</html> 