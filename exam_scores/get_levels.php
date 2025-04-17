<?php
require_once '../includes/config.php';

// 检查是否已登录
if (!isLoggedIn()) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// 获取类别ID
$categoryId = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;

if (!$categoryId) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Missing category_id']);
    exit;
}

// 查询级别数据
$levels = [];
try {
    $db = Database::getInstance();
    $stmt = $db->prepare("SELECT * FROM exam_levels WHERE category_id = ? ORDER BY level_order");
    $stmt->execute([$categoryId]);
    $levels = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("级别查询错误: " . $e->getMessage());
}

// 返回JSON数据
header('Content-Type: application/json');
echo json_encode($levels);
?> 