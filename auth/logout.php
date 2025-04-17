<?php
require_once '../includes/config.php';

// 记录登出活动
if (isset($_SESSION['username'])) {
    logActivity('用户登出', "用户 {$_SESSION['username']} 退出系统");
}

// 清除会话
session_start();
$_SESSION = array();
session_destroy();

// 跳转回首页
$_SESSION['message'] = '您已成功退出登录';
$_SESSION['message_type'] = 'info';
redirect('../index.php'); 