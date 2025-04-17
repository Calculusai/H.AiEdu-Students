<?php
/**
 * 系统配置文件
 */

// 定义网站基本信息
define('SITE_NAME', '少儿编程考级与学习规划系统');
define('SITE_URL', 'https://' . $_SERVER['HTTP_HOST']);
define('SITE_DESCRIPTION', '为青少年提供专业的编程考级与学习路径规划服务');

// 定义路径常量
define('ROOT_PATH', dirname(__DIR__));
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('TEMPLATES_PATH', ROOT_PATH . '/templates');
define('ASSETS_PATH', ROOT_PATH . '/assets');

// 时区设置
date_default_timezone_set('Asia/Shanghai');

// 错误显示设置（生产环境应设为0）
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 会话设置
session_start();

// 加载必要的文件
require_once INCLUDES_PATH . '/db.php';
require_once INCLUDES_PATH . '/functions.php';

// 系统版本
define('SYSTEM_VERSION', '1.0.0');

// 分页配置
define('ITEMS_PER_PAGE', 10); 