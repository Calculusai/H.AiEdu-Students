<?php
/**
 * 少儿编程成就展示系统 - 入口文件
 */

// 错误报告设置
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 加载配置文件
require_once 'config.php';

// 判断系统是否已安装
if (!SYSTEM_INSTALLED && !strpos($_SERVER['REQUEST_URI'], '/install')) {
    // 重定向到安装页面
    header('Location: /install/');
    exit;
}

// 加载核心函数
require_once CORE_PATH . '/helper.php';

// 自动加载类
spl_autoload_register(function ($class) {
    // 将命名空间分隔符转换为目录分隔符
    $class = str_replace('\\', '/', $class);
    
    // 检查核心类
    if (file_exists(CORE_PATH . '/' . $class . '.php')) {
        require_once CORE_PATH . '/' . $class . '.php';
        return;
    }
    
    // 检查控制器
    if (file_exists(CONTROLLER_PATH . '/' . $class . '.php')) {
        require_once CONTROLLER_PATH . '/' . $class . '.php';
        return;
    }
    
    // 检查模型
    if (file_exists(MODEL_PATH . '/' . $class . '.php')) {
        require_once MODEL_PATH . '/' . $class . '.php';
        return;
    }
});

// 启动会话
session_start();

// 创建数据库连接
require_once CORE_PATH . '/Database.php';
$db = new Database();

// 路由处理
require_once CORE_PATH . '/Router.php';
$app = new Router();

// 加载路由配置
require_once 'routes.php';

// 分发请求处理
$app->dispatch(); 