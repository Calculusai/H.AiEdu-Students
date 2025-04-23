<?php
/**
 * 少儿编程成就展示系统 - 配置文件
 */

// 调试模式
define('DEBUG_MODE', false);

// 数据库配置
define('DB_HOST', 'localhost'); // 数据库主机
define('DB_NAME', 'students_hoshino'); // 数据库名
define('DB_USER', 'students_hoshino'); // 数据库用户名
define('DB_PASS', 'students_hoshino'); // 数据库密码
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', 'utf8mb4_unicode_ci');
define('TABLE_PREFIX', 'ach_');

// 网站设置
define('SITE_NAME', 'H.AiStudents');
define('SITE_DESCRIPTION', '记录和展示少儿编程学习成就'); // 网站描述
define('SITE_URL', 'https://students.hoshinoai.xin');
define('ADMIN_EMAIL', '2380037681@qq.com');

// 安装状态
define('SYSTEM_INSTALLED', true);

// 主题设置
define('DEFAULT_THEME', 'light'); // 默认主题（light/dark）
define('ALLOW_USER_THEME', true); // 允许用户自定义主题
define('PRIMARY_COLOR', '#1e65c2'); // 主色调 - 从settings中获取
define('SECONDARY_COLOR', '#690fc2'); // 次要色调 - 从settings中获取
define('THEME_COLORS', [
    'primary' => PRIMARY_COLOR, // 使用定义的变量
    'secondary' => SECONDARY_COLOR, // 使用定义的变量
    'accent' => '#f59e0b', // 强调色
    'success' => '#10b981', // 成功色
    'info' => '#3b82f6', // 信息色
    'warning' => '#f59e0b', // 警告色
    'danger' => '#ef4444' // 危险色
]); // 主题颜色配置

// 内容设置
define('ENABLE_ACHIEVEMENTS_PUBLIC', true); // 允许访客查看成就页面
define('FOOTER_COPYRIGHT_SUFFIX', 'HshinoAi'); // 页脚版权后缀

// 外观设置
define('ENABLE_ANIMATIONS', true); // 启用界面动画效果
define('CARD_STYLE', 'dopamine'); // 修改默认卡片样式为多巴胺风格
define('BORDER_RADIUS', '1rem'); // 添加边框圆角设置
define('SHADOW_INTENSITY', 'medium'); // 添加阴影强度设置：light, medium, strong

// 安全设置
define('ALLOW_REGISTRATION', false); // 允许前台注册（不建议开启）

// 文件路径
define('BASE_PATH', __DIR__);
define('CORE_PATH', BASE_PATH . '/core');
define('CONTROLLER_PATH', BASE_PATH . '/controllers');
define('MODEL_PATH', BASE_PATH . '/models');
define('VIEW_PATH', BASE_PATH . '/views');
define('ASSET_PATH', BASE_PATH . '/assets');
define('UPLOAD_PATH', BASE_PATH . '/uploads');
define('LIB_PATH', BASE_PATH . '/lib');

// 安全设置
define('AUTH_KEY', '65867009065ae38380b78837e9dc8183');
define('SECURE_AUTH_KEY', '9bd83cab7a3f8399883b9893ca818030');
define('COOKIE_DOMAIN', '');
define('COOKIE_PATH', '/'); 