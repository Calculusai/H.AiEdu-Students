<?php
require_once '../includes/config.php';

$pageTitle = '更新学习规划';

// 检查用户是否已登录
if (!isLoggedIn()) {
    // 记录当前页面URL，以便登录后重定向回来
    $_SESSION['redirect_after_login'] = getCurrentUrl();
    
    // 设置消息
    $_SESSION['message'] = '请先登录以更新学习规划';
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

// 定义错误变量
$errors = [];
$formData = [
    'plan_title' => '',
    'goal' => '',
    'progress' => 0,
    'result' => '',
    'status' => '',
    'start_date' => '',
    'end_date' => ''
];

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
    
    // 获取学习规划详情
    $stmt = $db->prepare("SELECT * FROM learning_plans WHERE id = ? AND student_id = ?");
    $stmt->execute([$planId, $studentId]);
    $plan = $stmt->fetch();
    
    if (!$plan) {
        $_SESSION['message'] = '找不到指定的学习规划，或者您没有权限访问';
        $_SESSION['message_type'] = 'danger';
        redirect('learning_plans.php');
    }
    
    // 填充表单数据
    $formData = [
        'plan_title' => $plan['plan_title'],
        'goal' => $plan['goal'],
        'progress' => $plan['progress'],
        'result' => $plan['result'] ?? '',
        'status' => $plan['status'] ?? '',
        'start_date' => $plan['start_date'] ? date('Y-m-d', strtotime($plan['start_date'])) : '',
        'end_date' => $plan['end_date'] ? date('Y-m-d', strtotime($plan['end_date'])) : ''
    ];
} catch (PDOException $e) {
    error_log("获取学习规划错误: " . $e->getMessage());
    $_SESSION['message'] = '获取学习规划详情时发生错误，请稍后再试';
    $_SESSION['message_type'] = 'danger';
    redirect('learning_plans.php');
}

// 处理表单提交后的自动进度计算状态
$autoProgressChecked = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $autoProgressChecked = isset($_POST['auto_progress']);
}

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 获取并验证表单数据
    $formData = [
        'plan_title' => trim($_POST['plan_title'] ?? ''),
        'goal' => trim($_POST['goal'] ?? ''),
        'progress' => (int)($_POST['progress'] ?? 0),
        'result' => trim($_POST['result'] ?? ''),
        'status' => trim($_POST['status'] ?? '进行中'),
        'start_date' => !empty($_POST['start_date']) ? $_POST['start_date'] : null,
        'end_date' => !empty($_POST['end_date']) ? $_POST['end_date'] : null
    ];
    
    $autoProgress = isset($_POST['auto_progress']) ? true : false;
    
    // 验证表单数据
    if (empty($formData['plan_title'])) {
        $errors['plan_title'] = '请输入规划标题';
    }
    
    if (empty($formData['goal'])) {
        $errors['goal'] = '请输入学习目标';
    }
    
    if ($formData['progress'] < 0 || $formData['progress'] > 100) {
        $errors['progress'] = '进度必须在0-100之间';
    }
    
    // 日期验证
    if (!empty($formData['start_date']) && !empty($formData['end_date']) && strtotime($formData['start_date']) > strtotime($formData['end_date'])) {
        $errors['date'] = '开始日期不能晚于结束日期';
    }
    
    // 如果启用了自动进度计算，并且有有效的日期范围，则重新计算进度
    if ($autoProgress && !empty($formData['start_date']) && !empty($formData['end_date'])) {
        $start = strtotime($formData['start_date']);
        $end = strtotime($formData['end_date']);
        $now = time();
        
        if ($now <= $start) {
            $formData['progress'] = 0; // 还未开始
        } else if ($now >= $end) {
            $formData['progress'] = 100; // 已经结束
        } else {
            $totalDuration = $end - $start;
            $elapsedDuration = $now - $start;
            $formData['progress'] = min(100, round(($elapsedDuration / $totalDuration) * 100));
        }
        
        // 根据自动计算的进度更新状态
        if ($formData['progress'] == 0) {
            $formData['status'] = '未开始';
        } else if ($formData['progress'] == 100) {
            $formData['status'] = '已完成';
        } else if ($now > $end) {
            $formData['status'] = '已逾期';
        } else {
            $formData['status'] = '进行中';
        }
    }
    
    // 如果没有错误，更新数据库
    if (empty($errors)) {
        try {
            // 更新学习规划
            $stmt = $db->prepare("
                UPDATE learning_plans 
                SET plan_title = ?, goal = ?, progress = ?, result = ?, 
                    status = ?, start_date = ?, end_date = ?, updated_at = NOW() 
                WHERE id = ? AND student_id = ?
            ");
            
            $result = $stmt->execute([
                $formData['plan_title'],
                $formData['goal'],
                $formData['progress'],
                $formData['result'],
                $formData['status'],
                $formData['start_date'],
                $formData['end_date'],
                $planId,
                $studentId
            ]);
            
            if (!$result) {
                throw new PDOException("Failed to update learning plan");
            }
            
            // 如果进度为100%，自动设置为已完成
            if ($formData['progress'] == 100 && $formData['status'] != '已完成') {
                $stmt = $db->prepare("UPDATE learning_plans SET status = '已完成' WHERE id = ?");
                $stmt->execute([$planId]);
            }
            
            // 设置成功消息
            $_SESSION['message'] = '学习规划更新成功！';
            $_SESSION['message_type'] = 'success';
            
            // 重定向到学习规划列表页面
            redirect('learning_plans.php');
        } catch (PDOException $e) {
            error_log("更新学习规划错误: " . $e->getMessage());
            $errors['db'] = '更新学习规划时发生错误，请稍后再试';
        }
    }
}

// 页面特定样式
$extraStyles = <<<HTML
<style>
    .page-header {
        background: linear-gradient(135deg, var(--green), var(--blue));
        color: white;
        padding: var(--space-md) 0;
        margin-bottom: var(--space-lg);
        border-radius: var(--radius-xl);
        text-align: center;
    }
    
    .form-container {
        background-color: white;
        border-radius: var(--radius-xl);
        box-shadow: 0 10px 20px var(--shadow-color);
        padding: var(--space-lg);
        margin-bottom: var(--space-lg);
    }
    
    .form-title {
        color: var(--green);
        margin-bottom: var(--space-md);
        text-align: center;
    }
    
    .form-group {
        margin-bottom: var(--space-md);
    }
    
    .form-label {
        display: block;
        margin-bottom: var(--space-xs);
        color: var(--text-primary);
        font-weight: 600;
    }
    
    .form-control {
        width: 100%;
        padding: 12px 15px;
        border-radius: var(--radius-lg);
        border: 1px solid var(--border-color);
        transition: border 0.3s ease, box-shadow 0.3s ease;
    }
    
    select.form-control {
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='none' stroke='%23888' viewBox='0 0 12 12'%3E%3Cpath d='M3 5l3 3 3-3'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 15px center;
        background-size: 12px;
        padding-right: 40px;
    }
    
    select.form-control option {
        padding: 10px;
        font-size: var(--font-normal);
    }
    
    .status-select {
        width: 100%;
        max-width: 300px;
        height: auto;
        padding: 12px 40px 12px 15px;
    }
    
    .status-select option {
        padding: 12px;
        font-size: 16px;
        line-height: 1.5;
    }
    
    .form-control:focus {
        border-color: var(--green);
        box-shadow: 0 0 0 2px rgba(0, 224, 158, 0.2);
        outline: none;
    }
    
    .date-group {
        display: flex;
        gap: var(--space-md);
    }
    
    .date-group .form-group {
        flex: 1;
    }
    
    textarea.form-control {
        min-height: 120px;
        resize: vertical;
    }
    
    .invalid-feedback {
        color: var(--red);
        font-size: var(--font-small);
        margin-top: 5px;
    }
    
    .range-container {
        display: flex;
        flex-direction: column;
    }
    
    .range-slider {
        -webkit-appearance: none;
        width: 100%;
        height: 8px;
        border-radius: 4px;  
        background: rgba(0, 224, 158, 0.1);
        outline: none;
        margin: 15px 0;
    }
    
    .range-slider::-webkit-slider-thumb {
        -webkit-appearance: none;
        appearance: none;
        width: 20px;
        height: 20px;
        border-radius: 50%; 
        background: var(--green);
        cursor: pointer;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    }
    
    .range-slider::-moz-range-thumb {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: var(--green);
        cursor: pointer;
        border: none;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    }
    
    .range-info {
        display: flex;
        justify-content: space-between;
    }
    
    .range-value {
        font-weight: 600;
        color: var(--green);
    }
    
    .form-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: var(--space-lg);
    }
    
    .delete-btn {
        background-color: var(--red-light);
        color: var(--red);
        border: 1px solid var(--red);
    }
    
    .delete-btn:hover {
        background-color: var(--red);
        color: white;
    }
    
    .action-buttons {
        display: flex;
        gap: var(--space-md);
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
        <h1>更新学习规划</h1>
        <p>修改您的学习目标和记录学习进度</p>
    </div>
</div>

<div class="container">
    <div class="form-container">
        <h2 class="form-title">更新学习规划</h2>
        
        <?php if (isset($errors['db'])): ?>
            <div class="alert alert-danger">
                <?php echo $errors['db']; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="plan_title" class="form-label">规划标题</label>
                <input 
                    type="text" 
                    id="plan_title" 
                    name="plan_title" 
                    class="form-control <?php echo isset($errors['plan_title']) ? 'is-invalid' : ''; ?>" 
                    value="<?php echo htmlspecialchars($formData['plan_title']); ?>"
                    placeholder="例如：Python基础学习计划">
                    
                <?php if (isset($errors['plan_title'])): ?>
                    <div class="invalid-feedback"><?php echo $errors['plan_title']; ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="goal" class="form-label">学习目标</label>
                <textarea 
                    id="goal" 
                    name="goal" 
                    class="form-control <?php echo isset($errors['goal']) ? 'is-invalid' : ''; ?>"
                    placeholder="描述你的学习目标..."><?php echo htmlspecialchars($formData['goal']); ?></textarea>
                    
                <?php if (isset($errors['goal'])): ?>
                    <div class="invalid-feedback"><?php echo $errors['goal']; ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="progress" class="form-label">当前进度</label>
                <div class="range-container">
                    <input 
                        type="range" 
                        id="progress" 
                        name="progress" 
                        class="range-slider <?php echo isset($errors['progress']) ? 'is-invalid' : ''; ?>" 
                        min="0" 
                        max="100" 
                        step="1" 
                        value="<?php echo htmlspecialchars($formData['progress']); ?>">
                        
                    <div class="range-info">
                        <span>0%</span>
                        <span class="range-value" id="progress-value"><?php echo htmlspecialchars($formData['progress']); ?>%</span>
                        <span>100%</span>
                    </div>
                    
                    <?php if (isset($errors['progress'])): ?>
                        <div class="invalid-feedback"><?php echo $errors['progress']; ?></div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="form-group">
                <label for="result" class="form-label">阶段成果 <small>(可选)</small></label>
                <textarea 
                    id="result" 
                    name="result" 
                    class="form-control"
                    placeholder="记录您的学习成果和收获..."><?php echo htmlspecialchars($formData['result']); ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="status" class="form-label">当前状态</label>
                <select 
                    id="status" 
                    name="status" 
                    class="form-control status-select <?php echo isset($errors['status']) ? 'is-invalid' : ''; ?>">
                    <option value="未开始" <?php echo ($formData['status'] ?? '') === '未开始' ? 'selected' : ''; ?>>未开始</option>
                    <option value="进行中" <?php echo ($formData['status'] ?? '') === '进行中' ? 'selected' : ''; ?>>进行中</option>
                    <option value="已完成" <?php echo ($formData['status'] ?? '') === '已完成' ? 'selected' : ''; ?>>已完成</option>
                    <option value="已逾期" <?php echo ($formData['status'] ?? '') === '已逾期' ? 'selected' : ''; ?>>已逾期</option>
                </select>
                
                <?php if (isset($errors['status'])): ?>
                    <div class="invalid-feedback"><?php echo $errors['status']; ?></div>
                <?php endif; ?>
            </div>
            
            <div class="date-group">
                <div class="form-group">
                    <label for="start_date" class="form-label">开始日期</label>
                    <input 
                        type="date" 
                        id="start_date" 
                        name="start_date" 
                        class="form-control <?php echo isset($errors['start_date']) ? 'is-invalid' : ''; ?>"
                        value="<?php echo htmlspecialchars($formData['start_date']); ?>">
                        
                    <?php if (isset($errors['start_date'])): ?>
                        <div class="invalid-feedback"><?php echo $errors['start_date']; ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="end_date" class="form-label">预计完成日期</label>
                    <input 
                        type="date" 
                        id="end_date" 
                        name="end_date" 
                        class="form-control <?php echo isset($errors['end_date']) ? 'is-invalid' : ''; ?>"
                        value="<?php echo htmlspecialchars($formData['end_date']); ?>">
                        
                    <?php if (isset($errors['end_date'])): ?>
                        <div class="invalid-feedback"><?php echo $errors['end_date']; ?></div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="form-group">
                <div class="checkbox-wrapper">
                    <input 
                        type="checkbox" 
                        id="auto_progress" 
                        name="auto_progress" 
                        value="1" 
                        <?php echo $autoProgressChecked ? 'checked' : ''; ?>>
                    <label for="auto_progress" class="checkbox-label">根据时间范围自动计算进度</label>
                </div>
                <div class="progress-time-info" id="progress-time-info">
                    <small>选择此项后，系统将根据开始日期和预计完成日期自动计算当前进度</small>
                </div>
            </div>
            
            <div class="form-actions">
                <a href="learning_plans.php" class="btn btn-secondary">返回列表</a>
                <div class="action-buttons">
                    <a href="delete_plan.php?id=<?php echo $planId; ?>" class="btn delete-btn" onclick="return confirm('确定要删除此学习规划吗？此操作不可恢复。')">删除规划</a>
                    <button type="submit" class="btn btn-green">保存更新</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php
// 页面特定脚本
$extraScripts = <<<'HTML'
<script>
    // 更新进度值显示
    const progressSlider = document.getElementById('progress');
    const progressValue = document.getElementById('progress-value');
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    const autoProgressCheckbox = document.getElementById('auto_progress');
    const progressTimeInfo = document.getElementById('progress-time-info');
    
    progressSlider.addEventListener('input', function() {
        progressValue.textContent = this.value + '%';
    });
    
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
        
        progressTimeInfo.innerHTML = '<div class="auto-progress-result"><strong>自动计算结果:</strong> 进度 ' 
            + calculatedProgress + '% (' + elapsedDays + '/' + totalDays + '天) - 状态: ' 
            + statusText + '</div>';
        
        return calculatedProgress;
    }
    
    // 更新进度和状态
    function updateProgressAndStatus() {
        if (!autoProgressCheckbox.checked) {
            progressTimeInfo.innerHTML = '<small>选择此项后，系统将根据开始日期和预计完成日期自动计算当前进度</small>';
            progressSlider.disabled = false;
            return;
        }
        
        const calculatedProgress = calculateProgressFromDates();
        if (calculatedProgress !== null) {
            progressSlider.value = calculatedProgress;
            progressValue.textContent = calculatedProgress + '%';
            progressSlider.disabled = true;
            
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
            progressSlider.disabled = false;
        }
    }
    
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
    
    // 状态和进度的联动
    const statusSelect = document.getElementById('status');
    statusSelect.addEventListener('change', function() {
        if (autoProgressCheckbox.checked) return; // 如果自动计算进度，则不处理
        
        if (this.value === '已完成' && parseInt(progressSlider.value) < 100) {
            progressSlider.value = 100;
            progressValue.textContent = '100%';
        } else if (this.value === '未开始' && parseInt(progressSlider.value) > 0) {
            progressSlider.value = 0;
            progressValue.textContent = '0%';
        }
    });
    
    // 初始检查
    if (autoProgressCheckbox.checked) {
        updateProgressAndStatus();
    }
</script>
HTML;

include TEMPLATES_PATH . '/footer.php';
?> 