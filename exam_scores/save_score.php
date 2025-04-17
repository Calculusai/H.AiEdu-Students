<?php
require_once '../includes/config.php';

// 检查是否已登录
if (!isLoggedIn()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => '您需要登录才能添加成绩']);
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
$levelId = isset($_POST['level_id']) ? (int)$_POST['level_id'] : 0;
$score = isset($_POST['score']) ? (float)$_POST['score'] : 0;
$examDate = isset($_POST['exam_date']) ? $_POST['exam_date'] : '';

// 验证数据
if (!$studentId || !$levelId || !$score || !$examDate) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => '所有字段都是必填的']);
    exit;
}

// 验证分数范围
if ($score < 0 || $score > 100) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => '分数必须在0-100之间']);
    exit;
}

// 验证日期格式
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $examDate)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => '日期格式不正确']);
    exit;
}

// 保存数据
try {
    $db = Database::getInstance();
    
    // 检查学生ID是否存在
    $stmt = $db->prepare("SELECT id FROM students WHERE id = ?");
    $stmt->execute([$studentId]);
    if (!$stmt->fetch()) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => '学生ID不存在']);
        exit;
    }
    
    // 检查级别ID是否存在
    $stmt = $db->prepare("SELECT id FROM exam_levels WHERE id = ?");
    $stmt->execute([$levelId]);
    if (!$stmt->fetch()) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => '级别ID不存在']);
        exit;
    }
    
    // 检查是否已存在相同的考试记录
    $stmt = $db->prepare("SELECT id FROM exam_scores WHERE student_id = ? AND exam_level_id = ?");
    $stmt->execute([$studentId, $levelId]);
    if ($stmt->fetch()) {
        // 更新已有记录
        $stmt = $db->prepare("UPDATE exam_scores SET score = ?, exam_date = ?, updated_at = NOW() 
                              WHERE student_id = ? AND exam_level_id = ?");
        $stmt->execute([$score, $examDate, $studentId, $levelId]);
    } else {
        // 插入新记录
        $stmt = $db->prepare("INSERT INTO exam_scores (student_id, exam_level_id, score, exam_date) 
                              VALUES (?, ?, ?, ?)");
        $stmt->execute([$studentId, $levelId, $score, $examDate]);
    }
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => '成绩保存成功']);
} catch (PDOException $e) {
    error_log("保存成绩错误: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => '数据库错误，请稍后重试']);
}
?> 