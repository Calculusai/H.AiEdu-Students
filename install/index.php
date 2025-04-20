<?php
/**
 * 少儿编程成就展示系统 - 安装向导入口
 */

// 定义安装步骤
define('STEP_WELCOME', 'welcome');
define('STEP_REQUIREMENTS', 'requirements');
define('STEP_DATABASE', 'database');
define('STEP_ADMIN', 'admin');
define('STEP_FINISH', 'finish');

// 错误报告设置
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 引入安装程序类
require_once __DIR__ . '/installer.php';
$installer = new Installer();

// 获取当前步骤
$current_step = isset($_GET['step']) ? $_GET['step'] : STEP_WELCOME;

// 检查系统是否已安装
if ($installer->isInstalled() && $current_step != STEP_FINISH) {
    header('Location: ../index.php');
    exit;
}

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($current_step) {
        case STEP_DATABASE:
            // 处理数据库配置
            $result = $installer->setupDatabase($_POST);
            if ($result === true) {
                header('Location: index.php?step=' . STEP_ADMIN);
                exit;
            }
            $error_message = $result;
            break;
            
        case STEP_ADMIN:
            // 处理管理员设置
            $result = $installer->setupAdmin($_POST);
            if ($result === true) {
                header('Location: index.php?step=' . STEP_FINISH);
                exit;
            }
            $error_message = $result;
            break;
    }
}

// 检查系统要求
if ($current_step === STEP_REQUIREMENTS) {
    $requirements = $installer->checkRequirements();
    $requirements_met = !in_array(false, array_column($requirements, 'status'));
}

// 显示页面头部
include_once __DIR__ . '/templates/header.php';

// 显示当前步骤页面
switch ($current_step) {
    case STEP_WELCOME:
        include_once __DIR__ . '/templates/welcome.php';
        break;
        
    case STEP_REQUIREMENTS:
        include_once __DIR__ . '/templates/requirements.php';
        break;
        
    case STEP_DATABASE:
        include_once __DIR__ . '/templates/database.php';
        break;
        
    case STEP_ADMIN:
        include_once __DIR__ . '/templates/admin.php';
        break;
        
    case STEP_FINISH:
        include_once __DIR__ . '/templates/finish.php';
        break;
        
    default:
        header('Location: index.php');
        exit;
}

// 显示页面底部
include_once __DIR__ . '/templates/footer.php'; 