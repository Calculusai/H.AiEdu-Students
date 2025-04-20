/**
 * 少儿编程成就展示系统 - 主题切换器
 */
(function () {
    // 获取当前主题
    const getCurrentTheme = () => {
        // 优先使用本地存储的主题设置
        const storedTheme = localStorage.getItem('theme');
        if (storedTheme) {
            return storedTheme;
        }

        // 检查系统主题首选项
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            return 'dark';
        }

        // 默认浅色主题
        return 'light';
    };

    // 应用主题
    const applyTheme = (theme) => {
        if (theme === 'dark') {
            document.documentElement.setAttribute('data-theme', 'dark');
            if (document.getElementById('theme-switcher')) {
                document.getElementById('theme-switcher').checked = true;
            }
        } else {
            document.documentElement.removeAttribute('data-theme');
            if (document.getElementById('theme-switcher')) {
                document.getElementById('theme-switcher').checked = false;
            }
        }

        // 保存主题设置到本地存储
        localStorage.setItem('theme', theme);

        // 如果用户已登录，可以通过AJAX保存主题偏好到用户配置
        saveThemePreference(theme);
    };

    // 保存主题偏好到用户配置（需要用户登录状态）
    const saveThemePreference = (theme) => {
        // 检查是否已登录
        const userLoggedIn = document.body.getAttribute('data-logged-in') === 'true';

        if (userLoggedIn) {
            // 发送AJAX请求保存主题设置
            fetch('/api/save-theme-preference', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                },
                body: JSON.stringify({ theme })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        console.log('主题偏好已保存');
                    }
                })
                .catch(error => {
                    console.error('保存主题偏好失败:', error);
                });
        }
    };

    // 切换主题
    const toggleTheme = () => {
        const currentTheme = getCurrentTheme();
        const newTheme = currentTheme === 'light' ? 'dark' : 'light';
        applyTheme(newTheme);
    };

    // 初始化主题
    const initTheme = () => {
        // 应用当前主题
        applyTheme(getCurrentTheme());

        // 监听系统主题变化
        if (window.matchMedia) {
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
                const storedTheme = localStorage.getItem('theme');
                if (!storedTheme || storedTheme === 'auto') {
                    applyTheme(e.matches ? 'dark' : 'light');
                }
            });
        }

        // 初始化主题切换器
        initThemeSwitcher();
    };

    // 初始化主题切换器
    const initThemeSwitcher = () => {
        const themeSwitcher = document.getElementById('theme-switcher');

        // 如果页面上存在主题切换器
        if (themeSwitcher) {
            // 设置初始状态
            themeSwitcher.checked = getCurrentTheme() === 'dark';

            // 添加切换事件
            themeSwitcher.addEventListener('change', function () {
                applyTheme(this.checked ? 'dark' : 'light');
            });
        }

        // 添加主题切换按钮（如果不存在）
        if (!document.getElementById('theme-toggle-btn')) {
            addThemeToggleButton();
        }
    };

    // 添加主题切换按钮
    const addThemeToggleButton = () => {
        const header = document.querySelector('header') || document.querySelector('.navbar');

        if (header) {
            const button = document.createElement('button');
            button.id = 'theme-toggle-btn';
            button.title = '切换主题';
            button.className = 'theme-toggle-btn';
            button.innerHTML = `
                <svg class="sun-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="5"></circle>
                    <line x1="12" y1="1" x2="12" y2="3"></line>
                    <line x1="12" y1="21" x2="12" y2="23"></line>
                    <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                    <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                    <line x1="1" y1="12" x2="3" y2="12"></line>
                    <line x1="21" y1="12" x2="23" y2="12"></line>
                    <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
                    <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
                </svg>
                <svg class="moon-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                </svg>
            `;

            button.addEventListener('click', toggleTheme);

            // 添加CSS样式
            const style = document.createElement('style');
            style.textContent = `
                .theme-toggle-btn {
                    background: none;
                    border: none;
                    cursor: pointer;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    padding: 8px;
                    border-radius: 50%;
                    margin-left: 10px;
                    color: var(--body-color);
                    background-color: rgba(0, 0, 0, 0.1);
                }
                
                [data-theme="dark"] .theme-toggle-btn {
                    background-color: rgba(255, 255, 255, 0.1);
                }
                
                .theme-toggle-btn:hover {
                    background-color: rgba(0, 0, 0, 0.2);
                }
                
                [data-theme="dark"] .theme-toggle-btn:hover {
                    background-color: rgba(255, 255, 255, 0.2);
                }
                
                .sun-icon, .moon-icon {
                    transition: opacity 0.3s, transform 0.3s;
                    position: absolute;
                }
                
                .sun-icon {
                    opacity: 0;
                    transform: scale(0.7);
                }
                
                .moon-icon {
                    opacity: 1;
                    transform: scale(1);
                }
                
                [data-theme="dark"] .sun-icon {
                    opacity: 1;
                    transform: scale(1);
                }
                
                [data-theme="dark"] .moon-icon {
                    opacity: 0;
                    transform: scale(0.7);
                }
            `;

            document.head.appendChild(style);

            // 在适当位置添加按钮
            const navItems = header.querySelector('.navbar-nav') || header.querySelector('nav');
            if (navItems) {
                const li = document.createElement('li');
                li.className = 'nav-item';
                li.appendChild(button);
                navItems.appendChild(li);
            } else {
                header.appendChild(button);
            }
        }
    };

    // 当DOM加载完成后初始化
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTheme);
    } else {
        initTheme();
    }
})(); 