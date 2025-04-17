<?php
require_once '../includes/config.php';

// 检查用户是否已登录
if (!isLoggedIn()) {
    // 设置消息
    $_SESSION['message'] = '请先登录以管理学习规划';
    $_SESSION['message_type'] = 'info';
    
    // 重定向到登录页面
    redirect('../auth/login.php');
}

// 获取学习规划ID
$planId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($planId <= 0) {
    $_SESSION['message'] = '无效的学习规划ID';
    $_SESSION['message_type'] = 'danger';
    redirect('learning_plans.php');
}

// 从数据库获取学习规划
$userId = $_SESSION['user_id'];
$db = Database::getInstance();

try {
    // 获取学生ID
    $stmt = $db->prepare("SELECT id FROM students WHERE account_id = ?");
    $stmt->execute([$userId]);
    $student = $stmt->fetch();
    
    if (!$student) {
        $_SESSION['message'] = '找不到您的学生信息';
        $_SESSION['message_type'] = 'danger';
        redirect('learning_plans.php');
    }
    
    $studentId = $student['id'];
    
    // 确认学习规划存在且属于当前用户
    $stmt = $db->prepare("SELECT id FROM learning_plans WHERE id = ? AND student_id = ?");
    $stmt->execute([$planId, $studentId]);
    $plan = $stmt->fetch();
    
    if (!$plan) {
        $_SESSION['message'] = '找不到指定的学习规划，或者您没有权限删除';
        $_SESSION['message_type'] = 'danger';
        redirect('learning_plans.php');
    }
    
    // 删除学习规划
    $stmt = $db->prepare("DELETE FROM learning_plans WHERE id = ? AND student_id = ?");
    $result = $stmt->execute([$planId, $studentId]);
    
    if (!$result) {
        throw new PDOException("Failed to delete learning plan");
    }
    
    if ($stmt->rowCount() == 0) {
        $_SESSION['message'] = '学习规划删除失败，请确认该记录是否存在';
        $_SESSION['message_type'] = 'warning';
    } else {
        // 设置成功消息
        $_SESSION['message'] = '学习规划已成功删除';
        $_SESSION['message_type'] = 'success';
    }
    
} catch (PDOException $e) {
    error_log("删除学习规划错误: " . $e->getMessage());
    $_SESSION['message'] = '删除学习规划时发生错误，请稍后再试';
    $_SESSION['message_type'] = 'danger';
}

// 重定向到学习规划列表页面
redirect('learning_plans.php');
?> 