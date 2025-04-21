/**
 * 少儿编程成就展示系统 - 主JavaScript文件
 */

document.addEventListener('DOMContentLoaded', function () {
    // 导航菜单功能
    const navbarToggler = document.querySelector('.navbar-toggler');
    const navbarNav = document.querySelector('.navbar-nav');

    if (navbarToggler && navbarNav) {
        // 汉堡菜单点击事件
        navbarToggler.addEventListener('click', function () {
            this.classList.toggle('is-active');
            navbarNav.classList.toggle('show');
        });

        // 点击导航链接时关闭菜单（在移动设备上）
        const navLinks = navbarNav.querySelectorAll('.nav-link');
        navLinks.forEach(link => {
            link.addEventListener('click', function () {
                if (window.innerWidth <= 768) {
                    navbarToggler.classList.remove('is-active');
                    navbarNav.classList.remove('show');
                }
            });
        });

        // 点击页面其他区域关闭菜单
        document.addEventListener('click', function (event) {
            if (window.innerWidth <= 768 && navbarNav.classList.contains('show')) {
                const isClickInside = navbarNav.contains(event.target) || navbarToggler.contains(event.target);

                if (!isClickInside) {
                    navbarToggler.classList.remove('is-active');
                    navbarNav.classList.remove('show');
                }
            }
        });
    }

    // 窗口大小改变时的处理
    window.addEventListener('resize', function () {
        if (window.innerWidth > 768 && navbarNav) {
            navbarToggler.classList.remove('is-active');
            navbarNav.classList.remove('show');
        }
    });

    // 切换深色/浅色模式 (如果存在切换按钮)
    const darkModeToggle = document.getElementById('darkModeToggle');
    if (darkModeToggle) {
        // 检查用户偏好
        const prefersDarkMode = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        const savedTheme = localStorage.getItem('theme');

        // 根据用户偏好设置初始主题
        if (savedTheme === 'dark' || (!savedTheme && prefersDarkMode)) {
            document.documentElement.setAttribute('data-theme', 'dark');
            darkModeToggle.checked = true;
        }

        // 监听切换事件
        darkModeToggle.addEventListener('change', function () {
            if (this.checked) {
                document.documentElement.setAttribute('data-theme', 'dark');
                localStorage.setItem('theme', 'dark');
            } else {
                document.documentElement.removeAttribute('data-theme');
                localStorage.setItem('theme', 'light');
            }
        });
    }

    // 给卡片添加悬停效果类
    const cards = document.querySelectorAll('.card');
    cards.forEach(card => {
        if (!card.classList.contains('card-gradient')) {
            card.addEventListener('mouseenter', function () {
                if (window.innerWidth > 768) { // 仅在非移动端应用
                    this.style.transform = 'translateY(-5px) scale(1.01)';
                    this.style.boxShadow = 'var(--box-shadow-lg)';
                }
            });

            card.addEventListener('mouseleave', function () {
                this.style.transform = '';
                this.style.boxShadow = '';
            });
        }
    });
}); 