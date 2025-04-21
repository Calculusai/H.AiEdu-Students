/**
 * 少儿编程成就展示系统 - 导航栏交互脚本
 */

document.addEventListener('DOMContentLoaded', function () {
    // 滚动时导航栏效果
    const navbar = document.querySelector('.navbar');
    let lastScrollTop = 0;

    if (navbar) {
        window.addEventListener('scroll', function () {
            let scrollTop = window.pageYOffset || document.documentElement.scrollTop;

            if (scrollTop > 100) {
                navbar.style.boxShadow = '0 10px 30px rgba(0, 0, 0, 0.1)';
                navbar.style.background = 'rgba(255, 255, 255, 0.95)';

                if (document.body.getAttribute('data-theme') === 'dark') {
                    navbar.style.background = 'rgba(30, 41, 59, 0.95)';
                }
            } else {
                navbar.style.boxShadow = '';
                navbar.style.background = '';
            }

            lastScrollTop = scrollTop;
        });
    }

    // 移动端下阻止下拉菜单关闭父级折叠菜单
    const userDropdown = document.getElementById('userDropdown');
    if (userDropdown) {
        userDropdown.addEventListener('click', function (e) {
            // 如果是移动端视图
            if (window.innerWidth < 992) {
                // 阻止事件冒泡，防止关闭导航菜单
                e.stopPropagation();

                // 防止切换导航菜单的折叠状态
                e.preventDefault();

                // 手动切换下拉菜单
                const dropdownMenu = this.nextElementSibling;
                if (dropdownMenu.classList.contains('show')) {
                    dropdownMenu.classList.remove('show');
                } else {
                    dropdownMenu.classList.add('show');
                }
            }
        });

        // 为下拉菜单项也添加阻止冒泡
        const dropdownItems = document.querySelectorAll('.dropdown-menu .dropdown-item');
        dropdownItems.forEach(item => {
            item.addEventListener('click', function (e) {
                // 如果是移动端视图，允许链接跳转但阻止关闭导航菜单
                if (window.innerWidth < 992) {
                    e.stopPropagation();
                }
            });
        });

        // 点击下拉菜单外部区域时关闭菜单
        document.addEventListener('click', function (e) {
            if (window.innerWidth < 992) {
                const dropdownMenu = userDropdown.nextElementSibling;
                if (!userDropdown.contains(e.target) && !dropdownMenu.contains(e.target) && dropdownMenu.classList.contains('show')) {
                    dropdownMenu.classList.remove('show');
                }
            }
        });
    }

    // 监听Bootstrap的折叠菜单事件，优化汉堡菜单图标过渡
    const navbarToggler = document.querySelector('.navbar-toggler');
    const navbarCollapse = document.querySelector('.navbar-collapse');

    if (navbarToggler && navbarCollapse) {
        // 状态标记，防止下拉菜单事件触发导致图标错误切换
        let isUserDropdownOpen = false;

        // 用户下拉菜单状态监听
        document.addEventListener('click', function (e) {
            // 检查点击是否在用户下拉菜单区域之外
            if (userDropdown && !userDropdown.contains(e.target)) {
                isUserDropdownOpen = false;
            }
        });

        if (userDropdown) {
            // 监听用户下拉菜单的展开/关闭事件
            userDropdown.addEventListener('click', function () {
                isUserDropdownOpen = !isUserDropdownOpen;
            });
        }

        // 监听Bootstrap折叠菜单的show.bs.collapse事件（菜单打开时）
        navbarCollapse.addEventListener('show.bs.collapse', function () {
            // 确保不是由于下拉菜单引起的事件
            if (!isUserDropdownOpen) {
                // 平滑过渡到X形状
                console.log('导航菜单打开');
            }
        });

        // 监听Bootstrap折叠菜单的hidden.bs.collapse事件（菜单关闭后）
        navbarCollapse.addEventListener('hidden.bs.collapse', function () {
            // 确保不是由于下拉菜单引起的事件
            if (!isUserDropdownOpen) {
                // 平滑过渡回汉堡形状
                console.log('导航菜单关闭');
            }
        });

        // 解决汉堡菜单和下拉菜单的冲突
        if (userDropdown) {
            // 防止用户下拉菜单关闭导航菜单
            userDropdown.addEventListener('show.bs.dropdown', function (e) {
                if (window.innerWidth < 992) {
                    e.stopPropagation();
                }
            });

            userDropdown.addEventListener('hide.bs.dropdown', function (e) {
                if (window.innerWidth < 992) {
                    e.stopPropagation();
                }
            });

            // 禁用Bootstrap下拉菜单在移动设备上的默认行为
            if (window.innerWidth < 992) {
                // 移除Bootstrap的数据属性，以便我们可以完全自定义下拉菜单行为
                userDropdown.setAttribute('data-bs-toggle-disabled', userDropdown.getAttribute('data-bs-toggle'));
                userDropdown.removeAttribute('data-bs-toggle');
            }

            // 响应窗口大小变化，适当恢复或禁用Bootstrap下拉功能
            window.addEventListener('resize', function () {
                if (window.innerWidth < 992) {
                    if (userDropdown.hasAttribute('data-bs-toggle')) {
                        userDropdown.setAttribute('data-bs-toggle-disabled', userDropdown.getAttribute('data-bs-toggle'));
                        userDropdown.removeAttribute('data-bs-toggle');
                    }
                } else {
                    if (userDropdown.hasAttribute('data-bs-toggle-disabled')) {
                        userDropdown.setAttribute('data-bs-toggle', userDropdown.getAttribute('data-bs-toggle-disabled'));
                    }
                }
            });
        }
    }
}); 