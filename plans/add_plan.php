<?php
require_once '../includes/config.php';

// 检查用户是否已登录
if (!isLoggedIn()) {
    $_SESSION['redirect_after_login'] = getCurrentUrl();
    $_SESSION['message'] = '请先登录以创建学习规划';
    $_SESSION['message_type'] = 'info';
    redirect('/auth/login.php');
}

$userId = $_SESSION['user_id'];
$db = Database::getInstance();
$pageTitle = '创建学习规划';

// 获取学生ID
$studentId = 0;
try {
    $stmt = $db->prepare("SELECT id FROM students WHERE account_id = ?");
    $stmt->execute([$userId]);
    $student = $stmt->fetch();
    
    if ($student) {
        $studentId = $student['id'];
    } else {
        throw new Exception("未找到学生信息");
    }
} catch (Exception $e) {
    setMessage('error', "获取学生信息失败：" . $e->getMessage());
    redirect('/dashboard.php');
}

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['plan_title'] ?? '');
    $goal = trim($_POST['goal'] ?? '');
    $progress = isset($_POST['progress']) ? intval($_POST['progress']) : 0;
    $result = trim($_POST['result'] ?? '');
    $status = trim($_POST['status'] ?? '未开始');
    $startDate = !empty($_POST['start_date']) ? $_POST['start_date'] : null;
    $endDate = !empty($_POST['end_date']) ? $_POST['end_date'] : null;
    $autoProgress = isset($_POST['auto_progress']) ? true : false;
    
    // 表单验证
    $errors = [];
    
    if (empty($title)) {
        $errors[] = "规划标题不能为空";
    }
    
    if (empty($goal)) {
        $errors[] = "学习目标不能为空";
    }
    
    if ($progress < 0 || $progress > 100) {
        $errors[] = "进度必须在0-100之间";
    }
    
    // 日期验证
    if (!empty($startDate) && !empty($endDate) && strtotime($startDate) > strtotime($endDate)) {
        $errors[] = "开始日期不能晚于结束日期";
    }
    
    // 如果启用了自动进度计算，并且有有效的日期范围，则重新计算进度
    if ($autoProgress && !empty($startDate) && !empty($endDate)) {
        $start = strtotime($startDate);
        $end = strtotime($endDate);
        $now = time();
        
        if ($now <= $start) {
            $progress = 0; // 还未开始
        } else if ($now >= $end) {
            $progress = 100; // 已经结束
        } else {
            $totalDuration = $end - $start;
            $elapsedDuration = $now - $start;
            $progress = min(100, round(($elapsedDuration / $totalDuration) * 100));
        }
        
        // 根据自动计算的进度更新状态
        if ($progress == 0) {
            $status = '未开始';
        } else if ($progress == 100) {
            $status = '已完成';
        } else {
            $status = '进行中';
        }
    }
    
    // 如果没有错误，创建规划
    if (empty($errors)) {
        try {
            $stmt = $db->prepare("
                INSERT INTO learning_plans (student_id, plan_title, goal, progress, result, status, start_date, end_date, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ");
            
            $stmt->execute([$studentId, $title, $goal, $progress, $result, $status, $startDate, $endDate]);
            
            setMessage('success', "学习规划创建成功！");
            redirect('/plans/learning_plans.php');
        } catch (PDOException $e) {
            $errors[] = "创建学习规划时发生错误：" . $e->getMessage();
        }
    }
}

// 页面特定样式
$extraStyles = <<<HTML
<style>
    .form-container {
        max-width: 800px;
        margin: 0 auto;
        background-color: white;
        border-radius: var(--radius-xl);
        box-shadow: 0 10px 20px var(--shadow-color);
        padding: var(--space-lg);
        margin-bottom: var(--space-lg);
    }
    
    .page-header {
        background: linear-gradient(135deg, var(--green), var(--blue));
        color: white;
        padding: var(--space-md) 0;
        margin-bottom: var(--space-lg);
        border-radius: var(--radius-xl);
        text-align: center;
    }
    
    .form-title {
        color: var(--green);
        margin-bottom: var(--space-md);
        font-weight: 700;
        text-align: center;
    }
    
    .form-group {
        margin-bottom: var(--space-md);
    }
    
    label {
        display: block;
        margin-bottom: var(--space-xs);
        color: var(--text-primary);
        font-weight: 600;
    }
    
    .date-group {
        display: flex;
        gap: var(--space-md);
    }
    
    .date-group .form-group {
        flex: 1;
    }
    
    input[type="text"],
    input[type="date"],
    textarea,
    select,
    .progress-input {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        font-size: var(--font-normal);
        color: var(--text-primary);
        transition: all 0.3s ease;
    }
    
    input[type="text"]:focus,
    input[type="date"]:focus,
    textarea:focus,
    select:focus {
        border-color: var(--green);
        box-shadow: 0 0 0 2px rgba(0, 224, 158, 0.2);
        outline: none;
    }
    
    textarea {
        min-height: 120px;
        resize: vertical;
    }
    
    .progress-container {
        display: flex;
        align-items: center;
        gap: var(--space-sm);
    }
    
    .progress-input {
        max-width: 100px;
        text-align: center;
    }
    
    .progress-bar-container {
        flex: 1;
        height: 12px;
        background-color: rgba(0, 224, 158, 0.1);
        border-radius: 6px;
        overflow: hidden;
    }
    
    .progress-bar-fill {
        height: 100%;
        background: linear-gradient(to right, var(--green), var(--blue));
        border-radius: 6px;
        transition: width 0.5s ease;
    }
    
    .form-footer {
        display: flex;
        justify-content: space-between;
        margin-top: var(--space-lg);
    }
    
    .error-message {
        color: var(--color-error);
        margin-bottom: var(--space-md);
        padding: var(--space-sm);
        background-color: rgba(244, 67, 54, 0.1);
        border-radius: var(--radius-md);
    }
    
    .checkbox-wrapper {
        display: flex;
        align-items: center;
        margin-bottom: 5px;
    }
    
    .checkbox-wrapper input[type="checkbox"] {
        margin-right: 10px;
    }
    
    .checkbox-label {
        display: inline;
        font-weight: 500;
        color: var(--text-primary);
    }
    
    .progress-time-info {
        color: var(--text-secondary);
        margin-left: 25px;
    }
    
    .auto-progress-result {
        margin-top: 5px;
        padding: 5px 10px;
        background-color: rgba(0, 224, 158, 0.1);
        border-radius: var(--radius-md);
        font-size: var(--font-small);
        color: var(--green);
    }
</style>
HTML;

include TEMPLATES_PATH . '/header.php';
?>

<!-- 页面标题 -->
<div class="page-header">
    <div class="container">
        <h1>创建学习规划</h1>
        <p>制定您的学习目标和计划</p>
    </div>
</div>

<!-- 表单容器 -->
<div class="form-container">
    <h2 class="form-title">新建学习规划</h2>
    
    <?php if (isset($errors) && !empty($errors)): ?>
        <div class="error-message">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="add_plan.php">
        <div class="form-group">
            <label for="plan_title">规划标题</label>
            <input type="text" id="plan_title" name="plan_title" value="<?php echo isset($_POST['plan_title']) ? htmlspecialchars($_POST['plan_title']) : ''; ?>" placeholder="例如：Python编程基础学习" required>
        </div>
        
        <div class="form-group">
            <label for="goal">学习目标</label>
            <textarea id="goal" name="goal" placeholder="请详细描述您的学习目标和计划..." required><?php echo isset($_POST['goal']) ? htmlspecialchars($_POST['goal']) : ''; ?></textarea>
        </div>
        
        <div class="date-group">
            <div class="form-group">
                <label for="start_date">开始日期</label>
                <input type="date" id="start_date" name="start_date" value="<?php echo isset($_POST['start_date']) ? htmlspecialchars($_POST['start_date']) : date('Y-m-d'); ?>">
            </div>
            
            <div class="form-group">
                <label for="end_date">预计完成日期</label>
                <input type="date" id="end_date" name="end_date" value="<?php echo isset($_POST['end_date']) ? htmlspecialchars($_POST['end_date']) : ''; ?>">
            </div>
        </div>
        
        <div class="form-group">
            <div class="checkbox-wrapper">
                <input type="checkbox" id="auto_progress" name="auto_progress" value="1" <?php echo isset($_POST['auto_progress']) ? 'checked' : ''; ?>>
                <label for="auto_progress" class="checkbox-label">根据时间范围自动计算进度</label>
            </div>
            <div class="progress-time-info" id="progress-time-info">
                <small>选择此项后，系统将根据开始日期和预计完成日期自动计算当前进度</small>
            </div>
        </div>
        
        <div class="form-group">
            <label for="status">当前状态</label>
            <select id="status" name="status">
                <option value="未开始" <?php echo (isset($_POST['status']) && $_POST['status'] === '未开始') || !isset($_POST['status']) ? 'selected' : ''; ?>>未开始</option>
                <option value="进行中" <?php echo isset($_POST['status']) && $_POST['status'] === '进行中' ? 'selected' : ''; ?>>进行中</option>
                <option value="已完成" <?php echo isset($_POST['status']) && $_POST['status'] === '已完成' ? 'selected' : ''; ?>>已完成</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="progress">完成进度</label>
            <div class="progress-container">
                <input type="number" id="progress" name="progress" class="progress-input" 
                       value="<?php echo isset($_POST['progress']) ? intval($_POST['progress']) : 0; ?>" min="0" max="100" required>
                <span>%</span>
                <div class="progress-bar-container">
                    <div class="progress-bar-fill" style="width: <?php echo isset($_POST['progress']) ? intval($_POST['progress']) : 0; ?>%;"></div>
                </div>
            </div>
        </div>
        
        <div class="form-group">
            <label for="result">阶段成果（如已有）</label>
            <textarea id="result" name="result" placeholder="如果已经有一些成果，可以在这里记录..."><?php echo isset($_POST['result']) ? htmlspecialchars($_POST['result']) : ''; ?></textarea>
        </div>
        
        <div class="form-footer">
            <a href="/plans/learning_plans.php" class="btn btn-secondary">返回列表</a>
            <button type="submit" class="btn btn-learning">创建规划</button>
        </div>
    </form>
</div>

<script>
    // 进度条交互脚本
    document.addEventListener('DOMContentLoaded', function() {
        const progressInput = document.getElementById('progress');
        const progressBarFill = document.querySelector('.progress-bar-fill');
        const startDateInput = document.getElementById('start_date');
        const endDateInput = document.getElementById('end_date');
        const autoProgressCheckbox = document.getElementById('auto_progress');
        const progressTimeInfo = document.getElementById('progress-time-info');
        
        // 初始化进度条
        function updateProgressBar(value) {
            progressBarFill.style.width = value + '%';
        }
        
        // 根据日期范围计算进度
        function calculateProgressFromDates() {
            const startDate = new Date(startDateInput.value);
            const endDate = new Date(endDateInput.value);
            const today = new Date();
            
            if (isNaN(startDate.getTime()) || isNaN(endDate.getTime())) {
                progressTimeInfo.innerHTML = '<small class="text-danger">请设置有效的开始和结束日期</small>';
                return null;
            }
            
            if (startDate > endDate) {
                progressTimeInfo.innerHTML = '<small class="text-danger">开始日期不能晚于结束日期</small>';
                return null;
            }
            
            // 计算进度百分比
            const totalDays = Math.floor((endDate - startDate) / (1000 * 60 * 60 * 24)) + 1;
            let elapsedDays = Math.floor((today - startDate) / (1000 * 60 * 60 * 24)) + 1;
            
            if (elapsedDays < 0) elapsedDays = 0;
            if (elapsedDays > totalDays) elapsedDays = totalDays;
            
            const calculatedProgress = Math.floor((elapsedDays / totalDays) * 100);
            
            // 显示计算结果
            let statusText = '';
            if (calculatedProgress === 0) {
                statusText = '未开始';
            } else if (calculatedProgress === 100) {
                statusText = '已完成';
            } else if (today > endDate) {
                statusText = '已逾期';
            } else {
                statusText = '进行中';
            }
            
            progressTimeInfo.innerHTML = `
                <div class="auto-progress-result">
                    <strong>自动计算结果:</strong> 进度 ${calculatedProgress}% (${elapsedDays}/${totalDays}天) - 状态: ${statusText}
                </div>
            `;
            
            return calculatedProgress;
        }
        
        // 更新进度和状态
        function updateProgressAndStatus() {
            if (!autoProgressCheckbox.checked) {
                progressTimeInfo.innerHTML = '<small>选择此项后，系统将根据开始日期和预计完成日期自动计算当前进度</small>';
                progressInput.disabled = false;
                return;
            }
            
            const calculatedProgress = calculateProgressFromDates();
            if (calculatedProgress !== null) {
                progressInput.value = calculatedProgress;
                updateProgressBar(calculatedProgress);
                progressInput.disabled = true;
                
                // 更新状态
                const statusSelect = document.getElementById('status');
                if (calculatedProgress === 0) {
                    statusSelect.value = '未开始';
                } else if (calculatedProgress === 100) {
                    statusSelect.value = '已完成';
                } else {
                    const today = new Date();
                    const endDate = new Date(endDateInput.value);
                    if (today > endDate) {
                        statusSelect.value = '已逾期';
                    } else {
                        statusSelect.value = '进行中';
                    }
                }
            } else {
                progressInput.disabled = false;
            }
        }
        
        // 监听输入变化
        progressInput.addEventListener('input', function() {
            let value = parseInt(this.value);
            
            if (isNaN(value)) value = 0;
            if (value < 0) value = 0;
            if (value > 100) value = 100;
            
            this.value = value;
            updateProgressBar(value);
            
            // 如果进度为100%，自动设置状态为已完成
            const statusSelect = document.getElementById('status');
            if (value === 100 && statusSelect.value !== '已完成') {
                statusSelect.value = '已完成';
            }
        });
        
        // 状态和进度的联动
        const statusSelect = document.getElementById('status');
        statusSelect.addEventListener('change', function() {
            if (autoProgressCheckbox.checked) return; // 如果自动计算进度，则不处理
            
            if (this.value === '已完成' && parseInt(progressInput.value) < 100) {
                progressInput.value = 100;
                updateProgressBar(100);
            } else if (this.value === '未开始' && parseInt(progressInput.value) > 0) {
                progressInput.value = 0;
                updateProgressBar(0);
            }
        });
        
        // 日期和自动计算进度复选框事件监听
        startDateInput.addEventListener('change', function() {
            if (autoProgressCheckbox.checked) {
                updateProgressAndStatus();
            }
        });
        
        endDateInput.addEventListener('change', function() {
            if (autoProgressCheckbox.checked) {
                updateProgressAndStatus();
            }
        });
        
        autoProgressCheckbox.addEventListener('change', updateProgressAndStatus);
        
        // 初始检查
        if (autoProgressCheckbox.checked) {
            updateProgressAndStatus();
        }
    });
</script>

<?php include TEMPLATES_PATH . '/footer.php'; ?> 