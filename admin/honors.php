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

// 处理荣誉记录操作
$message = '';
$error = '';

// 处理删除荣誉记录
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $delete_id = (int)$_GET['delete'];
    
    try {
        // 删除荣誉记录
        $stmt = $db->prepare("DELETE FROM honors WHERE id = ?");
        $stmt->execute([$delete_id]);
        
        $message = "荣誉记录已成功删除！";
    } catch (Exception $e) {
        $error = "删除荣誉记录时出错：" . $e->getMessage();
    }
}

// 处理添加/更新荣誉记录
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_honor'])) {
    $student_id = (int)$_POST['student_id'];
    $honor_title = trim($_POST['honor_title']);
    $honor_type = trim($_POST['honor_type']);
    $honor_date = trim($_POST['honor_date']);
    $description = trim($_POST['description']);
    $edit_id = isset($_POST['edit_id']) ? (int)$_POST['edit_id'] : 0;
    
    // 验证输入
    if (empty($student_id) || empty($honor_title) || empty($honor_type) || empty($honor_date)) {
        $error = "学生、荣誉名称、荣誉类型和获得日期都是必填的！";
    } else {
        if ($edit_id > 0) {
            // 更新现有荣誉记录
            $stmt = $db->prepare("UPDATE honors SET student_id = ?, honor_title = ?, honor_type = ?, honor_date = ?, description = ?, updated_at = NOW() WHERE id = ?");
            if ($stmt->execute([$student_id, $honor_title, $honor_type, $honor_date, $description, $edit_id])) {
                $message = "荣誉记录已成功更新！";
            } else {
                $error = "更新荣誉记录时出错！";
            }
        } else {
            // 添加新荣誉记录
            $stmt = $db->prepare("INSERT INTO honors (student_id, honor_title, honor_type, honor_date, description, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
            if ($stmt->execute([$student_id, $honor_title, $honor_type, $honor_date, $description])) {
                $message = "新荣誉记录已成功添加！";
            } else {
                $error = "添加新荣誉记录时出错！";
            }
        }
    }
}

// 获取要编辑的荣誉记录信息
$edit_honor = null;
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $db->prepare("SELECT * FROM honors WHERE id = ?");
    $stmt->execute([$edit_id]);
    $edit_honor = $stmt->fetch(PDO::FETCH_ASSOC);
}

// 获取所有荣誉记录
$stmt = $db->prepare("SELECT h.*, s.name as student_name 
                     FROM honors h
                     JOIN students s ON h.student_id = s.id
                     ORDER BY h.honor_date DESC, h.honor_type");
$stmt->execute();
$honors = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 获取所有学生
$stmt = $db->query("SELECT id, name FROM students ORDER BY name");
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 获取所有荣誉类型
$stmt = $db->query("SELECT DISTINCT honor_type FROM honors ORDER BY honor_type");
$honor_types = $stmt->fetchAll(PDO::FETCH_COLUMN);

// 页面标题
$page_title = "学生荣誉管理";
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
        .honor-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
        }
        
        .honor-card-icon {
            font-size: 36px;
            margin-right: 20px;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--admin-primary);
        }
        
        .honor-card-content {
            flex: 1;
        }
        
        .honor-card-title {
            font-weight: bold;
            margin-bottom: 5px;
            font-size: 18px;
        }
        
        .honor-card-student {
            font-weight: bold;
            color: var(--admin-primary);
        }
        
        .honor-card-type {
            display: inline-block;
            padding: 3px 10px;
            font-size: 12px;
            border-radius: 15px;
            background: var(--blue-light);
            color: var(--blue);
            margin-bottom: 10px;
        }
        
        .honor-card-date {
            color: var(--text-secondary);
            margin-bottom: 5px;
            font-size: 14px;
        }
        
        .honor-card-description {
            color: var(--text-secondary);
            font-style: italic;
        }
        
        .honor-card-actions {
            display: flex;
            flex-direction: column;
            gap: 5px;
            margin-left: 15px;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include 'admin_sidebar.php'; ?>
        
        <div class="admin-content">
            <div class="admin-content-header">
                <h1>学生荣誉管理</h1>
                <div>
                    <a href="honors.php" class="admin-btn admin-btn-primary"><i class="fas fa-list"></i> 所有荣誉记录</a>
                    <a href="honors.php?action=add" class="admin-btn admin-btn-outline"><i class="fas fa-plus"></i> 添加荣誉记录</a>
                </div>
            </div>
            
            <?php if (!empty($message)): ?>
                <div class="message success"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <?php if (!empty($error)): ?>
                <div class="message error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <!-- 荣誉记录表单 -->
            <?php if (isset($_GET['action']) && $_GET['action'] == 'add' || isset($_GET['edit'])): ?>
            <div class="admin-form">
                <h2><?php echo $edit_honor ? '编辑荣誉记录' : '添加新荣誉记录'; ?></h2>
                
                <form method="post" action="honors.php">
                    <?php if ($edit_honor): ?>
                        <input type="hidden" name="edit_id" value="<?php echo $edit_honor['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="student_id">学生</label>
                        <select id="student_id" name="student_id" class="form-control" required>
                            <option value="">-- 选择学生 --</option>
                            <?php foreach ($students as $student): ?>
                                <option value="<?php echo $student['id']; ?>" <?php echo ($edit_honor && $edit_honor['student_id'] == $student['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($student['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="honor_title">荣誉名称</label>
                        <input type="text" id="honor_title" name="honor_title" class="form-control" value="<?php echo $edit_honor ? htmlspecialchars($edit_honor['honor_title']) : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="honor_type">荣誉类型</label>
                        <select id="honor_type" name="honor_type" class="form-control" required>
                            <option value="">-- 选择类型 --</option>
                            <option value="学术奖项" <?php echo ($edit_honor && $edit_honor['honor_type'] == '学术奖项') ? 'selected' : ''; ?>>学术奖项</option>
                            <option value="竞赛获奖" <?php echo ($edit_honor && $edit_honor['honor_type'] == '竞赛获奖') ? 'selected' : ''; ?>>竞赛获奖</option>
                            <option value="技能认证" <?php echo ($edit_honor && $edit_honor['honor_type'] == '技能认证') ? 'selected' : ''; ?>>技能认证</option>
                            <option value="项目成就" <?php echo ($edit_honor && $edit_honor['honor_type'] == '项目成就') ? 'selected' : ''; ?>>项目成就</option>
                            <?php foreach ($honor_types as $type): ?>
                                <?php if (!in_array($type, ['学术奖项', '竞赛获奖', '技能认证', '项目成就']) && !empty($type)): ?>
                                    <option value="<?php echo htmlspecialchars($type); ?>" <?php echo ($edit_honor && $edit_honor['honor_type'] == $type) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($type); ?>
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                            <option value="其他" <?php echo ($edit_honor && $edit_honor['honor_type'] == '其他') ? 'selected' : ''; ?>>其他</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="honor_date">获得日期</label>
                        <input type="date" id="honor_date" name="honor_date" class="form-control" value="<?php echo $edit_honor ? $edit_honor['honor_date'] : date('Y-m-d'); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">描述</label>
                        <textarea id="description" name="description" class="form-control" rows="3"><?php echo $edit_honor ? htmlspecialchars($edit_honor['description']) : ''; ?></textarea>
                    </div>
                    
                    <div class="form-buttons">
                        <button type="submit" name="save_honor" class="admin-btn admin-btn-primary"><?php echo $edit_honor ? '更新荣誉记录' : '添加荣誉记录'; ?></button>
                        <a href="honors.php" class="admin-btn admin-btn-outline">取消</a>
                    </div>
                </form>
            </div>
            <?php endif; ?>
            
            <!-- 荣誉记录列表 -->
            <?php if (!isset($_GET['action']) && !isset($_GET['edit'])): ?>
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2 class="admin-card-title">荣誉记录列表</h2>
                </div>
                
                <?php if (count($honors) > 0): ?>
                    <?php
                    // 按荣誉类型分组
                    $grouped_honors = [];
                    foreach ($honors as $honor) {
                        $grouped_honors[$honor['honor_type']][] = $honor;
                    }
                    
                    foreach ($grouped_honors as $type => $type_honors):
                    ?>
                        <h3 style="margin: 20px 0 10px; padding-bottom: 5px; border-bottom: 1px solid var(--border-color);">
                            <?php echo htmlspecialchars($type); ?>
                        </h3>
                        
                        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(400px, 1fr)); gap: 20px; margin-bottom: 30px;">
                            <?php foreach ($type_honors as $honor): ?>
                                <div class="honor-card">
                                    <div class="honor-card-icon">
                                        <?php 
                                        $icon = 'fas fa-award';
                                        switch($honor['honor_type']) {
                                            case '学术奖项':
                                                $icon = 'fas fa-graduation-cap';
                                                break;
                                            case '竞赛获奖':
                                                $icon = 'fas fa-trophy';
                                                break;
                                            case '技能认证':
                                                $icon = 'fas fa-certificate';
                                                break;
                                            case '项目成就':
                                                $icon = 'fas fa-project-diagram';
                                                break;
                                        }
                                        ?>
                                        <i class="<?php echo $icon; ?>"></i>
                                    </div>
                                    <div class="honor-card-content">
                                        <div class="honor-card-title"><?php echo htmlspecialchars($honor['honor_title']); ?></div>
                                        <div class="honor-card-student">
                                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($honor['student_name']); ?>
                                        </div>
                                        <div class="honor-card-type"><?php echo htmlspecialchars($honor['honor_type']); ?></div>
                                        <div class="honor-card-date">
                                            <i class="far fa-calendar-alt"></i> 获得日期: <?php echo date('Y年m月d日', strtotime($honor['honor_date'])); ?>
                                        </div>
                                        <?php if (!empty($honor['description'])): ?>
                                        <div class="honor-card-description"><?php echo htmlspecialchars($honor['description']); ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="honor-card-actions">
                                        <a href="?edit=<?php echo $honor['id']; ?>" class="admin-btn admin-btn-secondary" style="padding: 5px 10px; font-size: 12px;">
                                            <i class="fas fa-edit"></i> 编辑
                                        </a>
                                        <a href="?delete=<?php echo $honor['id']; ?>" class="admin-btn" style="padding: 5px 10px; font-size: 12px; background: linear-gradient(to right, var(--admin-danger), #FF6B6B); color: white;" onclick="return confirm('确定要删除此荣誉记录吗？')">
                                            <i class="fas fa-trash-alt"></i> 删除
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="text-align: center; padding: 30px;">
                        <p><i class="fas fa-info-circle" style="font-size: 40px; margin-bottom: 15px; color: var(--blue);"></i></p>
                        <p>还没有添加任何荣誉记录。点击"添加荣誉记录"按钮开始创建！</p>
                    </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
        </div>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // 日期选择器初始化
        const honorDateInput = document.getElementById('honor_date');
        if (honorDateInput) {
            // 如果需要额外的日期选择器初始化可以在这里添加
        }
    });
    </script>
</body>
</html> 