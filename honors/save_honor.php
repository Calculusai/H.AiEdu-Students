<?php
require_once '../includes/config.php';

// 检查是否已登录
if (!isLoggedIn()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => '您需要登录才能添加荣誉']);
    exit;
}

// 检查是否为POST请求
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => '请求方法错误']);
    exit;
}

// 获取表单数据
$studentId = isset($_POST['student_id']) ? (int)$_POST['student_id'] : 0;
$honorTitle = isset($_POST['honor_title']) ? trim($_POST['honor_title']) : '';
$honorType = isset($_POST['honor_type']) ? trim($_POST['honor_type']) : '';
$honorDate = isset($_POST['honor_date']) ? $_POST['honor_date'] : '';
$description = isset($_POST['description']) ? trim($_POST['description']) : '';

// 验证数据
if (!$studentId || !$honorTitle || !$honorType || !$honorDate) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => '荣誉名称、类型和日期都是必填的']);
    exit;
}

// 验证日期格式
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $honorDate)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => '日期格式不正确']);
    exit;
}

// 验证学生ID是否属于当前用户
$userId = $_SESSION['user_id'];
$db = Database::getInstance();

try {
    $stmt = $db->prepare("SELECT s.id FROM students s WHERE s.id = ? AND s.account_id = ?");
    $stmt->execute([$studentId, $userId]);
    if (!$stmt->fetch()) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => '您无权为此学生添加荣誉']);
        exit;
    }
} catch (PDOException $e) {
    error_log("学生验证错误: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => '系统错误，请稍后重试']);
    exit;
}

// 保存数据
try {
    $stmt = $db->prepare("INSERT INTO honors (student_id, honor_title, honor_type, honor_date, description, created_at, updated_at) 
                          VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
    $result = $stmt->execute([$studentId, $honorTitle, $honorType, $honorDate, $description]);
    
    if ($result) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => '荣誉证书添加成功']);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => '保存失败，请重试']);
    }
} catch (PDOException $e) {
    error_log("保存荣誉错误: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => '数据库错误，请稍后重试']);
}
?> 