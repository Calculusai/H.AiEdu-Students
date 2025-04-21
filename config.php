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
define('SITE_NAME', '少儿编程成就展示系统');
define('SITE_URL', 'https://students.hoshinoai.xin');
define('ADMIN_EMAIL', '2380037681@qq.com');

// 安装状态
define('SYSTEM_INSTALLED', true);

// 主题设置
define('DEFAULT_THEME', 'light'); // 默认主题

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