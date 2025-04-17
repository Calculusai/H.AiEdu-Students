<?php
/**
 * 数据库表结构更新脚本
 * 修改courses表中difficulty字段的类型从enum改为varchar(20)
 */

// 设置错误报告
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 包含数据库配置
require_once 'db_config.php';

// 连接数据库
try {
    $dsn = "mysql:host={$host};dbname={$dbname};charset={$charset}";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    $pdo = new PDO($dsn, $user, $password, $options);
    
    // 检查courses表是否存在
    $stmt = $pdo->query("SHOW TABLES LIKE 'courses'");
    if ($stmt->rowCount() > 0) {
        // 获取当前的difficulty字段类型
        $stmt = $pdo->query("SHOW COLUMNS FROM courses LIKE 'difficulty'");
        $field = $stmt->fetch();
        
        echo "<div>当前difficulty字段类型: " . $field['Type'] . "</div>";
        
        // 如果是enum类型，修改为varchar
        if (strpos($field['Type'], 'enum') === 0) {
            // 修改字段类型
            $sql = "ALTER TABLE courses MODIFY COLUMN difficulty VARCHAR(20) NOT NULL DEFAULT 'beginner'";
            $pdo->exec($sql);
            
            echo "<div style='color: green; margin-top: 10px;'>成功修改difficulty字段类型为VARCHAR(20)</div>";
            
            // 验证修改结果
            $stmt = $pdo->query("SHOW COLUMNS FROM courses LIKE 'difficulty'");
            $field = $stmt->fetch();
            echo "<div>修改后的difficulty字段类型: " . $field['Type'] . "</div>";
        } else {
            echo "<div style='color: blue; margin-top: 10px;'>difficulty字段已经是VARCHAR类型，无需修改</div>";
        }
    } else {
        echo "<div style='color: red;'>courses表不存在！请先运行install.php创建必要的表结构</div>";
    }
    
} catch (PDOException $e) {
    echo "<div style='color: red; font-weight: bold;'>数据库操作错误: " . $e->getMessage() . "</div>";
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>更新数据库结构</title>
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
            margin-top: 20px;
        }
        div {
            margin-bottom: 10px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>更新数据库结构</h1>
        
        <div class="text-center">
            <a href="admin/courses.php" class="btn">返回课程管理</a>
            <a href="admin/index.php" class="btn">返回管理中心</a>
        </div>
    </div>
</body>
</html> 