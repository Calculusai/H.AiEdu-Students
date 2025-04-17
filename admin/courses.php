<?php
// 包含必要的配置和函数
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// 检查用户是否已登录且是管理员
// 不要重复调用session_start，因为它已经在config.php中调用过了
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    $_SESSION['error'] = "您没有访问管理中心的权限！";
    header("Location: ../auth/login.php");
    exit();
}

// 获取数据库连接
$db = Database::getInstance();

// 处理课程操作
$message = '';
$error = '';

// 处理删除课程
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $delete_id = (int)$_GET['delete'];
    
    // 首先检查是否有学习路径依赖于此课程
    $stmt = $db->prepare("SELECT COUNT(*) FROM learning_path_courses WHERE course_id = ?");
    $stmt->execute([$delete_id]);
    $path_count = $stmt->fetchColumn();
    
    if ($path_count > 0) {
        $error = "无法删除此课程，因为它被用于一个或多个学习路径中！";
    } else {
        // 删除课程时同时删除相关的课程内容和学习记录
        try {
            $db->beginTransaction();
            
            // 删除学习记录
            $stmt = $db->prepare("DELETE FROM user_courses WHERE course_id = ?");
            $stmt->execute([$delete_id]);
            
            // 删除课程内容
            $stmt = $db->prepare("DELETE FROM course_content WHERE course_id = ?");
            $stmt->execute([$delete_id]);
            
            // 删除课程
            $stmt = $db->prepare("DELETE FROM courses WHERE id = ?");
            $stmt->execute([$delete_id]);
            
            $db->commit();
            $message = "课程已成功删除！";
        } catch (Exception $e) {
            $db->rollBack();
            $error = "删除课程时出错：" . $e->getMessage();
        }
    }
}

// 处理添加/更新课程
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_course'])) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $difficulty = $_POST['difficulty'];
    $category = $_POST['category'];
    $edit_id = isset($_POST['edit_id']) ? (int)$_POST['edit_id'] : 0;
    
    // 验证输入
    if (empty($title) || empty($description) || empty($difficulty) || empty($category)) {
        $error = "所有字段都是必填的！";
    } else {
        // 检查课程标题是否已存在
        $stmt = $db->prepare("SELECT * FROM courses WHERE title = ? AND id != ?");
        $stmt->execute([$title, $edit_id]);
        if ($stmt->rowCount() > 0) {
            $error = "课程标题已存在！";
        } else {
            if ($edit_id > 0) {
                // 更新现有课程
                $stmt = $db->prepare("UPDATE courses SET title = ?, description = ?, difficulty = ?, category = ?, updated_at = NOW() WHERE id = ?");
                if ($stmt->execute([$title, $description, $difficulty, $category, $edit_id])) {
                    $message = "课程信息已成功更新！";
                    // 更新成功后重定向到列表页面
                    header("Location: courses.php?success=update");
                    exit();
                } else {
                    $error = "更新课程信息时出错！";
                }
            } else {
                // 添加新课程
                $stmt = $db->prepare("INSERT INTO courses (title, description, difficulty, category, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())");
                if ($stmt->execute([$title, $description, $difficulty, $category])) {
                    $message = "新课程已成功添加！";
                    // 添加成功后重定向到列表页面
                    header("Location: courses.php?success=add");
                    exit();
                } else {
                    $error = "添加新课程时出错！";
                }
            }
        }
    }
}

// 处理成功消息
if (isset($_GET['success'])) {
    if ($_GET['success'] == 'add') {
        $message = "新课程已成功添加！";
    } elseif ($_GET['success'] == 'update') {
        $message = "课程信息已成功更新！";
    }
}

// 获取要编辑的课程信息
$edit_course = null;
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $db->prepare("SELECT * FROM courses WHERE id = ?");
    $stmt->execute([$edit_id]);
    $edit_course = $stmt->fetch(PDO::FETCH_ASSOC);
}

// 获取所有课程列表，默认按ID排序
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'id';
$order = isset($_GET['order']) ? $_GET['order'] : 'asc';

// 验证排序参数
$allowed_sort = ['id', 'title', 'difficulty', 'category', 'created_at'];
if (!in_array($sort, $allowed_sort)) {
    $sort = 'id';
}
$allowed_order = ['asc', 'desc'];
if (!in_array($order, $allowed_order)) {
    $order = 'asc';
}

// 构建排序参数
$sort_order = "$sort $order";

// 搜索参数
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$where_clause = '';
$params = [];

if (!empty($search)) {
    $where_clause = " WHERE title LIKE ? OR description LIKE ? OR category LIKE ?";
    $search_param = "%$search%";
    $params = [$search_param, $search_param, $search_param];
}

// 分页
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// 获取总记录数
$count_sql = "SELECT COUNT(*) FROM courses" . $where_clause;
$stmt = $db->prepare($count_sql);
if (!empty($params)) {
    $stmt->execute($params);
} else {
    $stmt->execute();
}
$total_records = $stmt->fetchColumn();
$total_pages = ceil($total_records / $per_page);

// 获取课程列表
$sql = "SELECT c.*, 
        (SELECT COUNT(*) FROM course_content WHERE course_id = c.id) AS content_count,
        (SELECT COUNT(*) FROM course_stats WHERE course_id = c.id) AS student_count,
        (SELECT COUNT(*) FROM course_stats WHERE course_id = c.id AND complete_status = 1) AS completion_count,
        (SELECT AVG(progress) FROM course_stats WHERE course_id = c.id) AS avg_progress
        FROM courses c" 
        . $where_clause . " ORDER BY $sort_order LIMIT $offset, $per_page";

$stmt = $db->prepare($sql);
if (!empty($params)) {
    $stmt->execute($params);
} else {
    $stmt->execute();
}
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 获取课程类别列表（用于表单）
$stmt = $db->query("SELECT DISTINCT category FROM courses ORDER BY category");
$categories = $stmt->fetchAll(PDO::FETCH_COLUMN);

// 处理课程详情查看
if (isset($_GET['view']) && !empty($_GET['view'])) {
    $view_id = (int)$_GET['view'];
    
    // 获取课程信息和统计数据
    $stmt = $db->prepare("
        SELECT c.*, 
        (SELECT COUNT(*) FROM course_stats WHERE course_id = c.id) AS student_count,
        (SELECT COUNT(*) FROM course_stats WHERE course_id = c.id AND complete_status = 1) AS completion_count,
        (SELECT AVG(progress) FROM course_stats WHERE course_id = c.id) AS avg_progress
        FROM courses c
        WHERE c.id = ?
    ");
    $stmt->execute([$view_id]);
    $course_detail = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$course_detail) {
        // 课程不存在，显示错误
        $error = "未找到该课程";
    } else {
        // 获取课程内容
        $stmt = $db->prepare("SELECT * FROM course_content WHERE course_id = ? ORDER BY sequence");
        $stmt->execute([$view_id]);
        $course_contents = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 获取学习该课程的学生
        $stmt = $db->prepare("
            SELECT a.username, a.email, s.name, cs.progress, 
                   uc.start_date, 
                   uc.complete_date,
                   DATE_FORMAT(uc.start_date, '%Y-%m-%d') as start_date_formatted, 
                   DATE_FORMAT(uc.complete_date, '%Y-%m-%d') as complete_date_formatted,
                   cs.complete_status
            FROM course_stats cs
            JOIN accounts a ON cs.user_id = a.id
            LEFT JOIN students s ON a.id = s.account_id
            LEFT JOIN user_courses uc ON uc.user_id = cs.user_id AND uc.course_id = cs.course_id
            WHERE cs.course_id = ?
            ORDER BY cs.progress DESC, uc.start_date DESC
            LIMIT 50
        ");
        $stmt->execute([$view_id]);
        $course_students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// 页面标题
$page_title = "课程管理";
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
        .difficulty-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-align: center;
            color: white;
            display: inline-block;
        }
        
        /* 难度级别颜色 */
        .difficulty-beginner { background: linear-gradient(to right, var(--green), #2ECC71); }
        .difficulty-intermediate { background: linear-gradient(to right, var(--orange), #F39C12); }
        .difficulty-advanced { background: linear-gradient(to right, var(--error), #E74C3C); }
        
        /* 电子协会图形化级别样式 */
        .difficulty-electronicsGrade1, .difficulty-electronicsGrade2, 
        .difficulty-electronicsGrade3, .difficulty-electronicsGrade4 {
            background: linear-gradient(to right, var(--blue), #3498DB);
        }
        
        /* Python级别样式 */
        .difficulty-pythonGrade1, .difficulty-pythonGrade2, .difficulty-pythonGrade3,
        .difficulty-pythonGrade4, .difficulty-pythonGrade5, .difficulty-pythonGrade6 {
            background: linear-gradient(to right, var(--purple), #9B59B6);
        }
        
        /* C++级别样式 */
        .difficulty-cppGrade1, .difficulty-cppGrade2, .difficulty-cppGrade3,
        .difficulty-cppGrade4, .difficulty-cppGrade5, .difficulty-cppGrade6,
        .difficulty-cppGrade7, .difficulty-cppGrade8 {
            background: linear-gradient(to right, #34495E, #2C3E50);
        }
        
        .course-stats {
            font-size: 14px;
            color: var(--text-secondary);
        }
        
        /* 添加课程统计样式 */
        .course-stats-detail {
            margin-top: 5px;
            font-size: 12px;
        }
        
        .completion-bar {
            height: 4px;
            background-color: #eee;
            border-radius: 2px;
            margin: 3px 0;
            overflow: hidden;
        }
        
        .completion-fill {
            height: 100%;
            background: linear-gradient(to right, var(--green), var(--blue));
            border-radius: 2px;
        }
        
        .completion-text {
            font-size: 11px;
            color: var(--text-secondary);
        }

        /* 添加课程详情页样式 */
        .course-info-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 20px;
        }

        .course-info-item {
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 8px;
        }

        .info-label {
            display: block;
            font-size: 0.85rem;
            color: var(--text-secondary);
            margin-bottom: 5px;
        }

        .info-value {
            font-size: 1.1rem;
            font-weight: 500;
            color: var(--text-primary);
        }

        .course-description {
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 8px;
            margin-top: 20px;
        }

        .course-description h3 {
            margin-top: 0;
            color: var(--primary);
            margin-bottom: 10px;
            font-size: 1.1rem;
        }

        .stats-dashboard {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }

        .stat-card {
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary);
        }

        .stat-label {
            font-size: 0.9rem;
            color: var(--text-secondary);
            margin-top: 5px;
        }

        .completion-stats {
            margin-top: 20px;
        }

        .completion-stats h3 {
            font-size: 1.1rem;
            color: var(--primary);
            margin-bottom: 10px;
        }

        .completion-bar-large {
            height: 24px;
            background-color: #f0f0f0;
            border-radius: 12px;
            overflow: hidden;
            position: relative;
        }

        .completion-bar-large .completion-fill {
            height: 100%;
            background: linear-gradient(to right, var(--green), var(--blue));
        }

        .completion-percentage {
            position: absolute;
            top: 0;
            right: 10px;
            line-height: 24px;
            color: white;
            font-weight: 500;
            text-shadow: 0 0 2px rgba(0,0,0,0.5);
        }

        .progress-container {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .progress-bar {
            flex-grow: 1;
            height: 8px;
            background-color: #f0f0f0;
            border-radius: 4px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(to right, var(--primary), var(--blue));
        }

        .progress-text {
            font-size: 0.9rem;
            font-weight: 500;
            color: var(--text-primary);
            min-width: 40px;
            text-align: right;
        }

        .admin-card-subtitle {
            font-size: 0.8rem;
            color: var(--text-secondary);
            margin-left: 10px;
        }

        @media screen and (max-width: 768px) {
            .course-info-grid, .stats-dashboard {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media screen and (max-width: 480px) {
            .course-info-grid, .stats-dashboard {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include 'admin_sidebar.php'; ?>
        
        <div class="admin-content">
            <div class="admin-content-header">
                <h1>课程管理</h1>
                <div>
                    <a href="courses.php" class="admin-btn admin-btn-primary"><i class="fas fa-list"></i> 所有课程</a>
                    <a href="courses.php?action=add" class="admin-btn admin-btn-outline"><i class="fas fa-plus"></i> 添加课程</a>
                </div>
            </div>
            
            <?php if (!empty($message)): ?>
                <div class="message success"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <?php if (!empty($error)): ?>
                <div class="message error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <!-- 课程表单 -->
            <?php if (isset($_GET['action']) && $_GET['action'] == 'add' || isset($_GET['edit'])): ?>
            <div class="admin-form">
                <h2><?php echo $edit_course ? '编辑课程' : '添加新课程'; ?></h2>
                <form method="post" action="courses.php">
                    <?php if ($edit_course): ?>
                        <input type="hidden" name="edit_id" value="<?php echo $edit_course['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="title">课程标题</label>
                        <input type="text" id="title" name="title" class="form-control" value="<?php echo $edit_course ? htmlspecialchars($edit_course['title']) : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">课程描述</label>
                        <textarea id="description" name="description" class="form-control" required><?php echo $edit_course ? htmlspecialchars($edit_course['description']) : ''; ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="difficulty">难度级别</label>
                        <select id="difficulty" name="difficulty" class="form-control" required>
                            <option value="">-- 选择级别 --</option>
                            <!-- 通用级别 -->
                            <option value="beginner" class="level-all <?php echo ($edit_course && $edit_course['difficulty'] == 'beginner') ? 'selected' : ''; ?>">初级</option>
                            <option value="intermediate" class="level-all <?php echo ($edit_course && $edit_course['difficulty'] == 'intermediate') ? 'selected' : ''; ?>">中级</option>
                            <option value="advanced" class="level-all <?php echo ($edit_course && $edit_course['difficulty'] == 'advanced') ? 'selected' : ''; ?>">高级</option>
                            
                            <!-- 电子协会考级图形化级别 -->
                            <option value="electronicsGrade1" class="level-electronicsGraphics <?php echo ($edit_course && $edit_course['difficulty'] == 'electronicsGrade1') ? 'selected' : ''; ?>">电子协会图形化一级</option>
                            <option value="electronicsGrade2" class="level-electronicsGraphics <?php echo ($edit_course && $edit_course['difficulty'] == 'electronicsGrade2') ? 'selected' : ''; ?>">电子协会图形化二级</option>
                            <option value="electronicsGrade3" class="level-electronicsGraphics <?php echo ($edit_course && $edit_course['difficulty'] == 'electronicsGrade3') ? 'selected' : ''; ?>">电子协会图形化三级</option>
                            <option value="electronicsGrade4" class="level-electronicsGraphics <?php echo ($edit_course && $edit_course['difficulty'] == 'electronicsGrade4') ? 'selected' : ''; ?>">电子协会图形化四级</option>
                            
                            <!-- Python级别 -->
                            <option value="pythonGrade1" class="level-python <?php echo ($edit_course && $edit_course['difficulty'] == 'pythonGrade1') ? 'selected' : ''; ?>">Python一级</option>
                            <option value="pythonGrade2" class="level-python <?php echo ($edit_course && $edit_course['difficulty'] == 'pythonGrade2') ? 'selected' : ''; ?>">Python二级</option>
                            <option value="pythonGrade3" class="level-python <?php echo ($edit_course && $edit_course['difficulty'] == 'pythonGrade3') ? 'selected' : ''; ?>">Python三级</option>
                            <option value="pythonGrade4" class="level-python <?php echo ($edit_course && $edit_course['difficulty'] == 'pythonGrade4') ? 'selected' : ''; ?>">Python四级</option>
                            <option value="pythonGrade5" class="level-python <?php echo ($edit_course && $edit_course['difficulty'] == 'pythonGrade5') ? 'selected' : ''; ?>">Python五级</option>
                            <option value="pythonGrade6" class="level-python <?php echo ($edit_course && $edit_course['difficulty'] == 'pythonGrade6') ? 'selected' : ''; ?>">Python六级</option>
                            
                            <!-- C++级别 -->
                            <option value="cppGrade1" class="level-cpp <?php echo ($edit_course && $edit_course['difficulty'] == 'cppGrade1') ? 'selected' : ''; ?>">C++一级</option>
                            <option value="cppGrade2" class="level-cpp <?php echo ($edit_course && $edit_course['difficulty'] == 'cppGrade2') ? 'selected' : ''; ?>">C++二级</option>
                            <option value="cppGrade3" class="level-cpp <?php echo ($edit_course && $edit_course['difficulty'] == 'cppGrade3') ? 'selected' : ''; ?>">C++三级</option>
                            <option value="cppGrade4" class="level-cpp <?php echo ($edit_course && $edit_course['difficulty'] == 'cppGrade4') ? 'selected' : ''; ?>">C++四级</option>
                            <option value="cppGrade5" class="level-cpp <?php echo ($edit_course && $edit_course['difficulty'] == 'cppGrade5') ? 'selected' : ''; ?>">C++五级</option>
                            <option value="cppGrade6" class="level-cpp <?php echo ($edit_course && $edit_course['difficulty'] == 'cppGrade6') ? 'selected' : ''; ?>">C++六级</option>
                            <option value="cppGrade7" class="level-cpp <?php echo ($edit_course && $edit_course['difficulty'] == 'cppGrade7') ? 'selected' : ''; ?>">C++七级</option>
                            <option value="cppGrade8" class="level-cpp <?php echo ($edit_course && $edit_course['difficulty'] == 'cppGrade8') ? 'selected' : ''; ?>">C++八级</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="category">课程类别</label>
                        <select id="category" name="category" class="form-control" required>
                            <option value="">-- 选择类别 --</option>
                            <option value="电子协会考级图形化" <?php echo ($edit_course && $edit_course['category'] == '电子协会考级图形化') ? 'selected' : ''; ?>>电子协会考级图形化</option>
                            <option value="Python" <?php echo ($edit_course && $edit_course['category'] == 'Python') ? 'selected' : ''; ?>>Python</option>
                            <option value="C++" <?php echo ($edit_course && $edit_course['category'] == 'C++') ? 'selected' : ''; ?>>C++</option>
                            <option value="Scratch" <?php echo ($edit_course && $edit_course['category'] == 'Scratch') ? 'selected' : ''; ?>>Scratch</option>
                            <option value="JavaScript" <?php echo ($edit_course && $edit_course['category'] == 'JavaScript') ? 'selected' : ''; ?>>JavaScript</option>
                            <option value="HTML/CSS" <?php echo ($edit_course && $edit_course['category'] == 'HTML/CSS') ? 'selected' : ''; ?>>HTML/CSS</option>
                            <option value="算法" <?php echo ($edit_course && $edit_course['category'] == '算法') ? 'selected' : ''; ?>>算法</option>
                            <option value="机器人编程" <?php echo ($edit_course && $edit_course['category'] == '机器人编程') ? 'selected' : ''; ?>>机器人编程</option>
                            <option value="游戏开发" <?php echo ($edit_course && $edit_course['category'] == '游戏开发') ? 'selected' : ''; ?>>游戏开发</option>
                            <option value="人工智能" <?php echo ($edit_course && $edit_course['category'] == '人工智能') ? 'selected' : ''; ?>>人工智能</option>
                            <?php foreach ($categories as $category): ?>
                                <?php if (!in_array($category, ['Scratch', 'Python', 'JavaScript', 'HTML/CSS', '算法', '机器人编程', '游戏开发', '人工智能']) && !empty($category)): ?>
                                    <option value="<?php echo htmlspecialchars($category); ?>" <?php echo ($edit_course && $edit_course['category'] == $category) ? 'selected' : ''; ?>><?php echo htmlspecialchars($category); ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                            <option value="其他" <?php echo ($edit_course && $edit_course['category'] == '其他') ? 'selected' : ''; ?>>其他</option>
                        </select>
                    </div>
                    
                    <div class="form-buttons">
                        <button type="submit" name="save_course" class="admin-btn admin-btn-primary"><?php echo $edit_course ? '更新课程' : '添加课程'; ?></button>
                        <a href="courses.php" class="admin-btn admin-btn-outline">取消</a>
                    </div>
                </form>
            </div>
            <?php endif; ?>
            
            <!-- 课程列表部分 -->
            <?php if (!isset($_GET['action']) && !isset($_GET['edit'])): ?>
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2 class="admin-card-title">课程列表</h2>
                    <form class="search-form" method="get" action="courses.php" style="display: flex; gap: 10px; width: 300px;">
                        <input type="text" name="search" class="form-control" placeholder="搜索课程..." value="<?php echo htmlspecialchars($search); ?>" style="margin: 0;">
                        <button type="submit" class="admin-btn admin-btn-secondary" style="padding: 10px; margin: 0;"><i class="fas fa-search"></i></button>
                    </form>
                </div>
                
                <!-- 课程列表 -->
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>
                                    <a href="?sort=id&order=<?php echo $sort == 'id' && $order == 'asc' ? 'desc' : 'asc'; ?>&search=<?php echo urlencode($search); ?>">
                                        ID <?php echo $sort == 'id' ? ($order == 'asc' ? '↑' : '↓') : ''; ?>
                                    </a>
                                </th>
                                <th>
                                    <a href="?sort=title&order=<?php echo $sort == 'title' && $order == 'asc' ? 'desc' : 'asc'; ?>&search=<?php echo urlencode($search); ?>">
                                        课程标题 <?php echo $sort == 'title' ? ($order == 'asc' ? '↑' : '↓') : ''; ?>
                                    </a>
                                </th>
                                <th>
                                    <a href="?sort=category&order=<?php echo $sort == 'category' && $order == 'asc' ? 'desc' : 'asc'; ?>&search=<?php echo urlencode($search); ?>">
                                        类别 <?php echo $sort == 'category' ? ($order == 'asc' ? '↑' : '↓') : ''; ?>
                                    </a>
                                </th>
                                <th>
                                    <a href="?sort=difficulty&order=<?php echo $sort == 'difficulty' && $order == 'asc' ? 'desc' : 'asc'; ?>&search=<?php echo urlencode($search); ?>">
                                        难度 <?php echo $sort == 'difficulty' ? ($order == 'asc' ? '↑' : '↓') : ''; ?>
                                    </a>
                                </th>
                                <th>内容数量</th>
                                <th>学生数量</th>
                                <th>
                                    <a href="?sort=created_at&order=<?php echo $sort == 'created_at' && $order == 'asc' ? 'desc' : 'asc'; ?>&search=<?php echo urlencode($search); ?>">
                                        创建时间 <?php echo $sort == 'created_at' ? ($order == 'asc' ? '↑' : '↓') : ''; ?>
                                    </a>
                                </th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($courses) > 0): ?>
                                <?php foreach ($courses as $course): ?>
                                    <tr>
                                        <td><?php echo $course['id']; ?></td>
                                        <td><?php echo htmlspecialchars($course['title']); ?></td>
                                        <td><?php echo htmlspecialchars($course['category']); ?></td>
                                        <td>
                                            <?php
                                            $difficulty_text = '';
                                            $difficulty_class = 'difficulty-' . $course['difficulty'];
                                            
                                            switch ($course['difficulty']) {
                                                case 'beginner':
                                                    $difficulty_text = '初级';
                                                    break;
                                                case 'intermediate':
                                                    $difficulty_text = '中级';
                                                    break;
                                                case 'advanced':
                                                    $difficulty_text = '高级';
                                                    break;
                                                default:
                                                    if (strpos($course['difficulty'], 'electronicsGrade') === 0) {
                                                        $grade = substr($course['difficulty'], -1);
                                                        $difficulty_text = '图形化' . $grade . '级';
                                                    } elseif (strpos($course['difficulty'], 'pythonGrade') === 0) {
                                                        $grade = substr($course['difficulty'], -1);
                                                        $difficulty_text = 'Python' . $grade . '级';
                                                    } elseif (strpos($course['difficulty'], 'cppGrade') === 0) {
                                                        $grade = substr($course['difficulty'], -1);
                                                        $difficulty_text = 'C++' . $grade . '级';
                                                    } else {
                                                        $difficulty_text = $course['difficulty'];
                                                    }
                                            }
                                            ?>
                                            <span class="difficulty-badge <?php echo $difficulty_class; ?>"><?php echo $difficulty_text; ?></span>
                                        </td>
                                        <td class="course-stats">
                                            <?php echo (int)$course['content_count']; ?> 个内容
                                        </td>
                                        <td class="course-stats">
                                            <div><?php echo (int)$course['student_count']; ?> 名学生</div>
                                            <?php if ((int)$course['student_count'] > 0): ?>
                                                <div class="course-stats-detail">
                                                    <div class="completion-bar" title="完成率: <?php echo round($course['completion_count'] / $course['student_count'] * 100); ?>%">
                                                        <div class="completion-fill" style="width: <?php echo round($course['completion_count'] / $course['student_count'] * 100); ?>%"></div>
                                                    </div>
                                                    <span class="completion-text"><?php echo (int)$course['completion_count']; ?> 已完成</span>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo date('Y-m-d', strtotime($course['created_at'])); ?></td>
                                        <td class="actions">
                                            <a href="?view=<?php echo $course['id']; ?>" class="admin-btn admin-btn-info" style="padding: 5px 10px; font-size: 12px;">
                                                <i class="fas fa-chart-bar"></i> 详情
                                            </a>
                                            <a href="course_content.php?course_id=<?php echo $course['id']; ?>" class="admin-btn admin-btn-primary" style="padding: 5px 10px; font-size: 12px;">
                                                <i class="fas fa-book-open"></i> 内容
                                            </a>
                                            <a href="?edit=<?php echo $course['id']; ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>&page=<?php echo $page; ?>" class="admin-btn admin-btn-secondary" style="padding: 5px 10px; font-size: 12px;">
                                                <i class="fas fa-edit"></i> 编辑
                                            </a>
                                            <a href="?delete=<?php echo $course['id']; ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>&page=<?php echo $page; ?>" class="admin-btn" style="padding: 5px 10px; font-size: 12px; background: linear-gradient(to right, var(--admin-danger), #FF6B6B); color: white;" onclick="return confirm('确定要删除此课程吗？这将同时删除所有课程内容和学习记录！')">
                                                <i class="fas fa-trash-alt"></i> 删除
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" style="text-align: center;">没有找到课程</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- 分页 -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=1&search=<?php echo urlencode($search); ?>&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>"><i class="fas fa-angle-double-left"></i></a>
                            <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>"><i class="fas fa-angle-left"></i></a>
                        <?php endif; ?>
                        
                        <?php
                        $start_page = max(1, $page - 2);
                        $end_page = min($total_pages, $page + 2);
                        
                        for ($i = $start_page; $i <= $end_page; $i++):
                        ?>
                            <?php if ($i == $page): ?>
                                <span class="active"><?php echo $i; ?></span>
                            <?php else: ?>
                                <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>"><?php echo $i; ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>"><i class="fas fa-angle-right"></i></a>
                            <a href="?page=<?php echo $total_pages; ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>"><i class="fas fa-angle-double-right"></i></a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- 添加课程详情页面 -->
            <?php if (isset($_GET['view']) && !empty($_GET['view']) && isset($course_detail)): ?>
            <div class="admin-content">
                <div class="admin-content-header">
                    <h1><i class="fas fa-book"></i> 课程详情</h1>
                    <div>
                        <a href="courses.php" class="admin-btn admin-btn-outline"><i class="fas fa-arrow-left"></i> 返回课程列表</a>
                        <a href="course_content.php?course_id=<?php echo $course_detail['id']; ?>" class="admin-btn admin-btn-primary"><i class="fas fa-book-open"></i> 管理课程内容</a>
                        <a href="?edit=<?php echo $course_detail['id']; ?>" class="admin-btn admin-btn-secondary"><i class="fas fa-edit"></i> 编辑课程</a>
                    </div>
                </div>
                
                <?php if (!empty($error)): ?>
                    <div class="message error"><?php echo $error; ?></div>
                <?php else: ?>
                    <!-- 课程基本信息 -->
                    <div class="admin-card">
                        <div class="admin-card-header">
                            <h2 class="admin-card-title"><?php echo htmlspecialchars($course_detail['title']); ?></h2>
                            <span class="difficulty-badge difficulty-<?php echo $course_detail['difficulty']; ?>">
                                <?php
                                $difficulty_text = '';
                                switch ($course_detail['difficulty']) {
                                    case 'beginner': $difficulty_text = '初级'; break;
                                    case 'intermediate': $difficulty_text = '中级'; break;
                                    case 'advanced': $difficulty_text = '高级'; break;
                                    default:
                                        if (strpos($course_detail['difficulty'], 'electronicsGrade') === 0) {
                                            $grade = substr($course_detail['difficulty'], -1);
                                            $difficulty_text = '图形化' . $grade . '级';
                                        } elseif (strpos($course_detail['difficulty'], 'pythonGrade') === 0) {
                                            $grade = substr($course_detail['difficulty'], -1);
                                            $difficulty_text = 'Python' . $grade . '级';
                                        } elseif (strpos($course_detail['difficulty'], 'cppGrade') === 0) {
                                            $grade = substr($course_detail['difficulty'], -1);
                                            $difficulty_text = 'C++' . $grade . '级';
                                        } else {
                                            $difficulty_text = $course_detail['difficulty'];
                                        }
                                }
                                echo $difficulty_text;
                                ?>
                            </span>
                        </div>
                        
                        <div class="admin-card-body">
                            <div class="course-info-grid">
                                <div class="course-info-item">
                                    <span class="info-label">类别</span>
                                    <span class="info-value"><?php echo htmlspecialchars($course_detail['category']); ?></span>
                                </div>
                                <div class="course-info-item">
                                    <span class="info-label">创建时间</span>
                                    <span class="info-value"><?php echo date('Y-m-d', strtotime($course_detail['created_at'])); ?></span>
                                </div>
                                <div class="course-info-item">
                                    <span class="info-label">最后更新</span>
                                    <span class="info-value"><?php echo date('Y-m-d', strtotime($course_detail['updated_at'])); ?></span>
                                </div>
                            </div>
                            
                            <div class="course-description">
                                <h3>课程描述</h3>
                                <p><?php echo nl2br(htmlspecialchars($course_detail['description'])); ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- 课程统计信息 -->
                    <div class="admin-card">
                        <div class="admin-card-header">
                            <h2 class="admin-card-title">课程统计</h2>
                        </div>
                        
                        <div class="admin-card-body">
                            <div class="stats-dashboard">
                                <div class="stat-card">
                                    <div class="stat-value"><?php echo (int)$course_detail['student_count']; ?></div>
                                    <div class="stat-label">学生数量</div>
                                </div>
                                <div class="stat-card">
                                    <div class="stat-value"><?php echo (int)$course_detail['completion_count']; ?></div>
                                    <div class="stat-label">完成人数</div>
                                </div>
                                <div class="stat-card">
                                    <div class="stat-value"><?php echo round($course_detail['avg_progress'], 1); ?>%</div>
                                    <div class="stat-label">平均进度</div>
                                </div>
                                <div class="stat-card">
                                    <div class="stat-value"><?php echo count($course_contents); ?></div>
                                    <div class="stat-label">内容数量</div>
                                </div>
                            </div>
                            
                            <?php if ($course_detail['student_count'] > 0): ?>
                            <div class="completion-stats">
                                <h3>完成率</h3>
                                <div class="completion-bar-large">
                                    <div class="completion-fill" style="width: <?php echo round($course_detail['completion_count'] / $course_detail['student_count'] * 100); ?>%"></div>
                                    <div class="completion-percentage"><?php echo round($course_detail['completion_count'] / $course_detail['student_count'] * 100); ?>%</div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- 学生列表 -->
                    <?php if (!empty($course_students)): ?>
                    <div class="admin-card">
                        <div class="admin-card-header">
                            <h2 class="admin-card-title">学习此课程的学生</h2>
                            <span class="admin-card-subtitle">显示前50名学生</span>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>学生姓名</th>
                                        <th>用户名</th>
                                        <th>邮箱</th>
                                        <th>进度</th>
                                        <th>开始时间</th>
                                        <th>完成时间</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($course_students as $student): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($student['name'] ?: '未设置'); ?></td>
                                        <td><?php echo htmlspecialchars($student['username']); ?></td>
                                        <td><?php echo htmlspecialchars($student['email'] ?: '未设置'); ?></td>
                                        <td>
                                            <div class="progress-container">
                                                <div class="progress-bar">
                                                    <div class="progress-fill" style="width: <?php echo (int)$student['progress']; ?>%"></div>
                                                </div>
                                                <span class="progress-text"><?php echo (int)$student['progress']; ?>%</span>
                                            </div>
                                        </td>
                                        <td><?php echo $student['start_date_formatted'] ?: '未记录'; ?></td>
                                        <td><?php echo $student['complete_date_formatted'] ?: '-'; ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // 根据课程类别过滤难度级别选项
        function filterDifficultyOptions() {
            var category = document.getElementById('category').value;
            var difficultySelect = document.getElementById('difficulty');
            var options = difficultySelect.getElementsByTagName('option');
            
            // 首先隐藏所有级别选项，只保留通用级别
            for (var i = 0; i < options.length; i++) {
                var option = options[i];
                if (option.classList.contains('level-electronicsGraphics') ||
                    option.classList.contains('level-python') ||
                    option.classList.contains('level-cpp')) {
                    option.style.display = 'none';
                } else {
                    option.style.display = '';
                }
            }
            
            // 根据选择的类别显示对应的级别选项
            if (category === '电子协会考级图形化') {
                showOptionsByClass('level-electronicsGraphics');
            } else if (category === 'Python') {
                showOptionsByClass('level-python');
            } else if (category === 'C++') {
                showOptionsByClass('level-cpp');
            }
            
            // 重置难度选择（如果当前选中的选项被隐藏）
            var currentOption = difficultySelect.options[difficultySelect.selectedIndex];
            if (currentOption.style.display === 'none') {
                difficultySelect.value = '';
            }
        }
        
        // 显示指定类的选项
        function showOptionsByClass(className) {
            var difficultySelect = document.getElementById('difficulty');
            var options = difficultySelect.getElementsByTagName('option');
            
            for (var i = 0; i < options.length; i++) {
                if (options[i].classList.contains(className)) {
                    options[i].style.display = '';
                }
            }
        }
        
        var categorySelect = document.getElementById('category');
        if (categorySelect) {
            // 初始过滤
            filterDifficultyOptions();
            
            // 监听类别变更
            categorySelect.addEventListener('change', filterDifficultyOptions);
            
            <?php if ($edit_course): ?>
            // 如果是编辑模式，确保正确的选项被显示
            showOptionsByClass('level-all');
            <?php if ($edit_course['category'] === '电子协会考级图形化'): ?>
            showOptionsByClass('level-electronicsGraphics');
            <?php elseif ($edit_course['category'] === 'Python'): ?>
            showOptionsByClass('level-python');
            <?php elseif ($edit_course['category'] === 'C++'): ?>
            showOptionsByClass('level-cpp');
            <?php endif; ?>
            <?php endif; ?>
        }
        
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
            mobileHeader.appendChild(document.createElement('div')).innerHTML = '<h1>课程管理</h1>';
            
            adminContent.insertBefore(mobileHeader, adminContent.firstChild);
        }
    });
    </script>
</body>
</html> 