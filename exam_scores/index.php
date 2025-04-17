<?php
require_once '../includes/config.php';

// 检查用户是否已登录
if (!isLoggedIn()) {
    redirect('../login.php');
}

$userId = $_SESSION['user_id'];
$db = Database::getInstance();

// 获取考试成绩
$examScores = [];
try {
    $stmt = $db->prepare("SELECT es.*, el.level_name, ec.category_name 
                          FROM exam_scores es
                          JOIN exam_levels el ON es.exam_level_id = el.id
                          JOIN exam_categories ec ON el.category_id = ec.id
                          WHERE es.student_id = ?");
    $stmt->execute([$userId]);
    $examScores = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("考试成绩查询错误: " . $e->getMessage());
}

include '../templates/header.php';
?>

<div class="container">
    <h1>考试成绩</h1>
    <?php if (empty($examScores)): ?>
        <p>暂无考试成绩记录。</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>类别</th>
                    <th>级别</th>
                    <th>分数</th>
                    <th>考试日期</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($examScores as $score): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($score['category_name']); ?></td>
                        <td><?php echo htmlspecialchars($score['level_name']); ?></td>
                        <td><?php echo htmlspecialchars($score['score']); ?></td>
                        <td><?php echo htmlspecialchars($score['exam_date']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php include '../templates/footer.php'; ?> 