<?php
// 包含必要的配置和函数
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// 检查用户是否已登录且是管理员
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    $_SESSION['error'] = "您没有访问管理中心的权限！";
    header("Location: ../auth/login.php");
    exit();
}

// 获取数据库连接
$db = Database::getInstance();

// 处理学习规划操作
$message = '';
$error = '';

// 处理删除学习规划
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $delete_id = (int)$_GET['delete'];
    
    try {
        // 删除学习规划
        $stmt = $db->prepare("DELETE FROM learning_plans WHERE id = ?");
        $stmt->execute([$delete_id]);
        
        $message = "学习规划已成功删除！";
    } catch (Exception $e) {
        $error = "删除学习规划时出错：" . $e->getMessage();
    }
}

// 处理添加/更新学习规划
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_plan'])) {
    $student_id = (int)$_POST['student_id'];
    $plan_title = trim($_POST['plan_title']);
    $goal = trim($_POST['goal']);
    $progress = isset($_POST['progress']) ? (int)$_POST['progress'] : 0;
    $result = isset($_POST['result']) ? trim($_POST['result']) : '';
    $start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : null;
    $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;
    $status = $_POST['status'];
    $edit_id = isset($_POST['edit_id']) ? (int)$_POST['edit_id'] : 0;
    
    // 验证输入
    if (empty($student_id) || empty($plan_title) || empty($goal)) {
        $error = "学生、规划标题和阶段目标都是必填的！";
    } else {
        try {
            // 确保进度值在0-100之间
            if ($progress < 0) $progress = 0;
            if ($progress > 100) $progress = 100;
            
            if ($edit_id > 0) {
                // 更新现有学习规划
                $stmt = $db->prepare("UPDATE learning_plans SET student_id = ?, plan_title = ?, goal = ?, progress = ?, result = ?, start_date = ?, end_date = ?, status = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$student_id, $plan_title, $goal, $progress, $result, $start_date, $end_date, $status, $edit_id]);
                
                $message = "学习规划已成功更新！";
                // 更新成功后重定向到列表页面
                header("Location: learning_plans.php?success=update");
                exit();
            } else {
                // 添加新学习规划
                $stmt = $db->prepare("INSERT INTO learning_plans (student_id, plan_title, goal, progress, result, start_date, end_date, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
                $stmt->execute([$student_id, $plan_title, $goal, $progress, $result, $start_date, $end_date, $status]);
                
                $message = "新学习规划已成功添加！";
                // 添加成功后重定向到列表页面
                header("Location: learning_plans.php?success=add");
                exit();
            }
        } catch (Exception $e) {
            $error = "保存学习规划时出错：" . $e->getMessage();
        }
    }
}

// 处理成功消息
if (isset($_GET['success'])) {
    if ($_GET['success'] == 'add') {
        $message = "新学习规划已成功添加！";
    } elseif ($_GET['success'] == 'update') {
        $message = "学习规划已成功更新！";
    }
}

// 获取所有学生
$stmt = $db->query("SELECT id, name FROM students ORDER BY name");
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 获取要编辑的学习规划信息
$edit_plan = null;
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $db->prepare("SELECT * FROM learning_plans WHERE id = ?");
    $stmt->execute([$edit_id]);
    $edit_plan = $stmt->fetch(PDO::FETCH_ASSOC);
}

// 获取所有学习规划
$stmt = $db->prepare("SELECT lp.*, s.name as student_name, 
                      DATE_FORMAT(lp.created_at, '%Y-%m-%d') as formatted_date,
                      lp.start_date as start_date,
                      lp.end_date as end_date,
                      lp.status as status
                      FROM learning_plans lp
                      JOIN students s ON lp.student_id = s.id
                      ORDER BY s.name, lp.created_at DESC");
$stmt->execute();
$learning_plans = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 辅助函数：根据状态获取对应的CSS类
function getStatusClass($status) {
    switch ($status) {
        case '已完成':
            return 'success';
        case '进行中':
            return 'primary';
        case '未开始':
            return 'info';
        case '已逾期':
            return 'danger';
        default:
            return 'secondary';
    }
}

// 自动更新学习规划状态
function updatePlanStatus() {
    global $db;
    $today = date('Y-m-d');
    
    // 更新已逾期的计划
    $db->exec("UPDATE learning_plans SET status = '已逾期' 
              WHERE end_date < '$today' AND progress < 100 AND status != '已完成'");
              
    // 更新进行中的计划
    $db->exec("UPDATE learning_plans SET status = '进行中' 
              WHERE start_date <= '$today' AND (end_date >= '$today' OR end_date IS NULL) 
              AND status = '未开始'");
              
    // 如果进度为100%，自动设置为已完成
    $db->exec("UPDATE learning_plans SET status = '已完成' 
              WHERE progress = 100 AND status != '已完成'");
}

// 执行自动状态更新
updatePlanStatus();

// 页面标题
$page_title = "学习规划管理";
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - 儿童编程教育平台</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin-style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        .student-name {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            color: white;
            font-weight: bold;
            background: linear-gradient(to right, var(--admin-primary), #3498DB);
        }
        
        .progress-bar {
            width: 100%;
            height: 15px;
            background-color: #eee;
            border-radius: 10px;
            overflow: hidden;
            margin-top: 5px;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(to right, var(--admin-primary), #3498DB);
            transition: width 0.3s ease;
        }
        
        .progress-text {
            font-size: 14px;
            font-weight: bold;
            color: var(--admin-primary);
            margin-bottom: 3px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
            text-align: center;
        }
        
        .status-success {
            background-color: #E3F9E5;
            color: #31A24C;
        }
        
        .status-primary {
            background-color: #E1F0FF;
            color: #1976D2;
        }
        
        .status-info {
            background-color: #E8F4FD;
            color: #0288D1;
        }
        
        .status-danger {
            background-color: #FFEFEF;
            color: #F44336;
        }
        
        .status-secondary {
            background-color: #F5F5F5;
            color: #757575;
        }

        .date-info {
            display: flex;
            align-items: center;
            font-size: 13px;
            color: #666;
        }
        
        .date-info i {
            margin-right: 5px;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include 'admin_sidebar.php'; ?>
        
        <div class="admin-content">
            <div class="admin-content-header">
                <h1>学习规划管理</h1>
                <div>
                    <a href="learning_plans.php" class="admin-btn admin-btn-primary"><i class="fas fa-list"></i> 所有规划</a>
                    <a href="learning_plans.php?action=add" class="admin-btn admin-btn-outline"><i class="fas fa-plus"></i> 添加规划</a>
                </div>
            </div>
            
            <?php if (!empty($message)): ?>
                <div class="message success"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <?php if (!empty($error)): ?>
                <div class="message error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <!-- 学习规划表单 -->
            <?php if (isset($_GET['action']) && $_GET['action'] == 'add' || isset($_GET['edit'])): ?>
            <div class="admin-form">
                <h2><?php echo $edit_plan ? '编辑学习规划' : '添加新学习规划'; ?></h2>
                <form method="post" action="learning_plans.php">
                    <?php if ($edit_plan): ?>
                        <input type="hidden" name="edit_id" value="<?php echo $edit_plan['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="student_id">学生</label>
                        <select id="student_id" name="student_id" class="form-control" required>
                            <option value="">-- 选择学生 --</option>
                            <?php foreach ($students as $student): ?>
                                <option value="<?php echo $student['id']; ?>" <?php echo ($edit_plan && $edit_plan['student_id'] == $student['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($student['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="plan_title">规划标题</label>
                        <input type="text" id="plan_title" name="plan_title" class="form-control" value="<?php echo $edit_plan ? htmlspecialchars($edit_plan['plan_title']) : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="goal">阶段目标</label>
                        <textarea id="goal" name="goal" class="form-control" rows="4" required><?php echo $edit_plan ? htmlspecialchars($edit_plan['goal']) : ''; ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="progress">当前进度 (%)</label>
                        <input type="number" id="progress" name="progress" class="form-control" min="0" max="100" value="<?php echo $edit_plan ? htmlspecialchars($edit_plan['progress']) : '0'; ?>">
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?php echo $edit_plan ? $edit_plan['progress'] : '0'; ?>%"></div>
                        </div>
                        <div class="progress-info" id="progress-info">
                            <small>您可以手动设置进度，也可以根据时间范围自动计算</small>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="result">阶段成果</label>
                        <textarea id="result" name="result" class="form-control" rows="4"><?php echo $edit_plan ? htmlspecialchars($edit_plan['result']) : ''; ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="start_date">开始日期</label>
                        <input type="date" id="start_date" name="start_date" class="form-control" value="<?php echo $edit_plan ? htmlspecialchars($edit_plan['start_date']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="end_date">结束日期</label>
                        <input type="date" id="end_date" name="end_date" class="form-control" value="<?php echo $edit_plan ? htmlspecialchars($edit_plan['end_date']) : ''; ?>">
                        <div class="form-check" style="margin-top: 5px;">
                            <input type="checkbox" id="auto_progress" name="auto_progress" class="form-check-input" value="1">
                            <label for="auto_progress" class="form-check-label">根据时间范围自动计算进度</label>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="status">状态</label>
                        <select id="status" name="status" class="form-control" required>
                            <option value="未开始" <?php echo ($edit_plan && $edit_plan['status'] == '未开始') ? 'selected' : ''; ?>>未开始</option>
                            <option value="进行中" <?php echo ($edit_plan && $edit_plan['status'] == '进行中') ? 'selected' : ''; ?>>进行中</option>
                            <option value="已完成" <?php echo ($edit_plan && $edit_plan['status'] == '已完成') ? 'selected' : ''; ?>>已完成</option>
                            <option value="已逾期" <?php echo ($edit_plan && $edit_plan['status'] == '已逾期') ? 'selected' : ''; ?>>已逾期</option>
                        </select>
                    </div>
                    
                    <div class="form-buttons">
                        <button type="submit" name="save_plan" class="admin-btn admin-btn-primary"><?php echo $edit_plan ? '更新规划' : '添加规划'; ?></button>
                        <a href="learning_plans.php" class="admin-btn admin-btn-outline">取消</a>
                    </div>
                </form>
            </div>
            <?php endif; ?>
            
            <!-- 学习规划列表 -->
            <?php if (!isset($_GET['action']) && !isset($_GET['edit'])): ?>
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2 class="admin-card-title">学习规划列表</h2>
                </div>
                
                <?php if (empty($learning_plans)): ?>
                    <div class="empty-state">
                        <div class="empty-state-icon"><i class="fas fa-tasks"></i></div>
                        <h3>暂无学习规划</h3>
                        <p>开始为学生创建学习规划，帮助他们更好地规划学习路径。</p>
                        <a href="learning_plans.php?action=add" class="admin-btn admin-btn-primary"><i class="fas fa-plus"></i> 添加规划</a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th width="5%">ID</th>
                                    <th width="10%">学生</th>
                                    <th width="15%">规划标题</th>
                                    <th width="20%">阶段目标</th>
                                    <th width="10%">进度</th>
                                    <th width="10%">开始日期</th>
                                    <th width="10%">结束日期</th>
                                    <th width="10%">状态</th>
                                    <th width="10%">操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($learning_plans as $plan): ?>
                                    <tr>
                                        <td><?php echo $plan['id']; ?></td>
                                        <td>
                                            <span class="student-name"><?php echo htmlspecialchars($plan['student_name']); ?></span>
                                        </td>
                                        <td><?php echo htmlspecialchars($plan['plan_title']); ?></td>
                                        <td><?php echo mb_substr(htmlspecialchars($plan['goal']), 0, 100) . (mb_strlen($plan['goal']) > 100 ? '...' : ''); ?></td>
                                        <td>
                                            <div class="progress-text"><?php echo $plan['progress']; ?>%</div>
                                            <div class="progress-bar">
                                                <div class="progress-fill" style="width: <?php echo $plan['progress']; ?>%"></div>
                                            </div>
                                        </td>
                                        <td><?php echo $plan['start_date'] ? date('Y-m-d', strtotime($plan['start_date'])) : '-'; ?></td>
                                        <td><?php echo $plan['end_date'] ? date('Y-m-d', strtotime($plan['end_date'])) : '-'; ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo getStatusClass($plan['status']); ?>">
                                                <?php echo $plan['status']; ?>
                                            </span>
                                        </td>
                                        <td class="actions">
                                            <a href="learning_plans.php?edit=<?php echo $plan['id']; ?>" class="admin-btn admin-btn-secondary" style="padding: 5px 10px; font-size: 12px;">
                                                <i class="fas fa-edit"></i> 编辑
                                            </a>
                                            <a href="learning_plans.php?delete=<?php echo $plan['id']; ?>" class="admin-btn" style="padding: 5px 10px; font-size: 12px; background: linear-gradient(to right, var(--admin-danger), #FF6B6B); color: white;" onclick="return confirm('确定要删除此学习规划吗？')">
                                                <i class="fas fa-trash-alt"></i> 删除
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // 添加移动设备菜单切换功能
        const adminContent = document.querySelector('.admin-content');
        
        // 如果是移动设备，创建汉堡菜单按钮
        if (window.innerWidth <= 768) {
            const mobileHeader = document.createElement('div');
            mobileHeader.className = 'admin-mobile-header';
            
            const menuToggle = document.createElement('button');
            menuToggle.className = 'admin-sidebar-toggle';
            menuToggle.innerHTML = '<i class="fas fa-bars"></i>';
            
            menuToggle.addEventListener('click', function() {
                document.querySelector('.admin-sidebar').classList.toggle('show');
                
                // 创建遮罩层
                let overlay = document.querySelector('.admin-sidebar-overlay');
                if (!overlay) {
                    overlay = document.createElement('div');
                    overlay.className = 'admin-sidebar-overlay';
                    document.body.appendChild(overlay);
                    
                    overlay.addEventListener('click', function() {
                        document.querySelector('.admin-sidebar').classList.remove('show');
                        overlay.style.display = 'none';
                    });
                }
                
                overlay.style.display = 'block';
            });
            
            mobileHeader.appendChild(menuToggle);
            mobileHeader.appendChild(document.createElement('div')).innerHTML = '<h1>学习规划管理</h1>';
            
            adminContent.insertBefore(mobileHeader, adminContent.firstChild);
        }
        
        // 进度条更新
        const progressInput = document.getElementById('progress');
        const progressFill = document.querySelector('.progress-fill');
        const startDateInput = document.getElementById('start_date');
        const endDateInput = document.getElementById('end_date');
        const autoProgressCheckbox = document.getElementById('auto_progress');
        const progressInfo = document.getElementById('progress-info');
        
        if (progressInput && startDateInput && endDateInput) {
            // 初始进度条更新
            progressInput.addEventListener('input', function() {
                let value = parseInt(this.value);
                if (isNaN(value)) value = 0;
                if (value < 0) value = 0;
                if (value > 100) value = 100;
                
                progressFill.style.width = value + '%';
                
                // 如果进度到达100%，自动将状态设置为已完成
                if (value === 100) {
                    const statusSelect = document.getElementById('status');
                    if (statusSelect) {
                        statusSelect.value = '已完成';
                    }
                }
            });
            
            // 根据日期范围自动计算进度
            function calculateProgress() {
                if (!autoProgressCheckbox.checked) return;
                
                const startDate = new Date(startDateInput.value);
                const endDate = new Date(endDateInput.value);
                const today = new Date();
                
                if (isNaN(startDate.getTime()) || isNaN(endDate.getTime())) {
                    progressInfo.innerHTML = '<small class="text-danger">请设置有效的开始和结束日期</small>';
                    return;
                }
                
                if (startDate > endDate) {
                    progressInfo.innerHTML = '<small class="text-danger">开始日期不能晚于结束日期</small>';
                    return;
                }
                
                // 计算进度百分比
                const totalDays = Math.floor((endDate - startDate) / (1000 * 60 * 60 * 24)) + 1;
                let elapsedDays = Math.floor((today - startDate) / (1000 * 60 * 60 * 24)) + 1;
                
                if (elapsedDays < 0) elapsedDays = 0;
                if (elapsedDays > totalDays) elapsedDays = totalDays;
                
                const calculatedProgress = Math.floor((elapsedDays / totalDays) * 100);
                
                progressInput.value = calculatedProgress;
                progressFill.style.width = calculatedProgress + '%';
                
                progressInfo.innerHTML = `<small>进度自动计算: ${elapsedDays}/${totalDays}天 (${calculatedProgress}%)</small>`;
                
                // 更新状态
                const statusSelect = document.getElementById('status');
                if (statusSelect) {
                    if (calculatedProgress >= 100) {
                        statusSelect.value = '已完成';
                    } else if (today > endDate) {
                        statusSelect.value = '已逾期';
                    } else if (today >= startDate) {
                        statusSelect.value = '进行中';
                    } else {
                        statusSelect.value = '未开始';
                    }
                }
            }
            
            // 日期和自动计算复选框事件监听
            startDateInput.addEventListener('change', calculateProgress);
            endDateInput.addEventListener('change', calculateProgress);
            autoProgressCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    calculateProgress();
                } else {
                    progressInfo.innerHTML = '<small>您可以手动设置进度，也可以根据时间范围自动计算</small>';
                }
            });
            
            // 初始检查
            if (autoProgressCheckbox.checked) {
                calculateProgress();
            }
        }
    });
    </script>
</body>
</html> 