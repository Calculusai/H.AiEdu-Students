    </main>
    
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3 class="footer-title">关于我们</h3>
                    <p>少儿编程考级与学习规划系统让每个孩子都能找到适合自己的编程学习方向。</p>
                </div>
                
                <div class="footer-section">
                    <h3 class="footer-title">快速链接</h3>
                    <ul class="footer-links">
                        <li><a href="https://algorithm.hoshinoai.xin">Algorithm</a></li>
                        <li><a href="https://algorithm.hoshinoai.xin">AoshinoAi</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3 class="footer-title">联系我们</h3>
                    <ul class="footer-links">
                        <li>邮箱：1697391069@qq.com</li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?> - 版本 <?php echo SYSTEM_VERSION; ?></p>
            </div>
        </div>
    </footer>
    
    <!-- 移动导航控制脚本 -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // 获取汉堡菜单、移动导航和遮罩元素
        const hamburger = document.querySelector('.hamburger-menu');
        const mobileNav = document.querySelector('.mobile-nav');
        const overlay = document.querySelector('.mobile-nav-overlay');
        const closeBtn = document.querySelector('.mobile-nav-close');
        
        // 打开移动导航
        hamburger.addEventListener('click', function() {
            mobileNav.classList.add('active');
            overlay.classList.add('active');
            document.body.style.overflow = 'hidden'; // 防止背景滚动
        });
        
        // 关闭移动导航（关闭按钮）
        closeBtn.addEventListener('click', function() {
            mobileNav.classList.remove('active');
            overlay.classList.remove('active');
            document.body.style.overflow = 'auto'; // 恢复背景滚动
        });
        
        // 关闭移动导航（点击遮罩）
        overlay.addEventListener('click', function() {
            mobileNav.classList.remove('active');
            overlay.classList.remove('active');
            document.body.style.overflow = 'auto'; // 恢复背景滚动
        });
        
        // 点击移动导航中的链接后自动关闭
        const mobileNavLinks = document.querySelectorAll('.mobile-nav-menu a');
        mobileNavLinks.forEach(function(link) {
            link.addEventListener('click', function() {
                mobileNav.classList.remove('active');
                overlay.classList.remove('active');
                document.body.style.overflow = 'auto'; // 恢复背景滚动
            });
        });
    });
    </script>
    
    <?php if (isset($extraScripts)) echo $extraScripts; ?>
</body>
</html> 