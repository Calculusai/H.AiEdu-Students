<?php
/**
 * 数据库安装脚本
 * 执行此脚本将创建必要的数据库表
 */

// 检查是否已经安装
session_start();
$installed = false;

// 设置错误报告
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 包含数据库配置
require_once 'db_config.php';

// 连接数据库
try {
    $dsn = "mysql:host={$host};charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    $pdo = new PDO($dsn, $user, $password, $options);
    
    // 读取SQL文件
    $sqlFile = file_get_contents('sql/init.sql');
    
    // 分割SQL语句
    $queries = explode(';', $sqlFile);
    
    // 执行每条SQL语句
    foreach ($queries as $query) {
        $query = trim($query);
        if (!empty($query)) {
            try {
                $pdo->exec($query);
                echo "<div style='color: green;'>成功执行: " . htmlspecialchars(substr($query, 0, 100)) . "...</div>";
            } catch (PDOException $e) {
                echo "<div style='color: orange;'>注意: " . $e->getMessage() . " <br>在执行: " . htmlspecialchars(substr($query, 0, 100)) . "...</div>";
            }
        }
    }
    
    $installed = true;
    $_SESSION['message'] = '数据库表已成功安装！';
    $_SESSION['message_type'] = 'success';
    
} catch (PDOException $e) {
    echo "<div style='color: red; font-weight: bold;'>数据库连接错误: " . $e->getMessage() . "</div>";
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>儿童编程教育平台 - 安装</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f7fa;
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #3498db;
            text-align: center;
        }
        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .btn {
            display: inline-block;
            background-color: #3498db;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>儿童编程教育平台 - 安装</h1>
        
        <?php if ($installed): ?>
            <div class="success-message">
                <p>数据库表已成功安装！现在您可以使用系统了。</p>
            </div>
            <div class="text-center">
                <a href="index.php" class="btn">前往首页</a>
                <a href="admin/index.php" class="btn">进入管理中心</a>
            </div>
        <?php else: ?>
            <div class="error-message">
                <p>安装过程中出现错误，请检查错误消息并修复问题后重试。</p>
            </div>
            <div class="text-center">
                <a href="install.php" class="btn">重试安装</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html> 