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

// 处理用户操作
$message = '';
$error = '';

// 处理删除成绩记录
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $delete_id = (int)$_GET['delete'];
    
    $stmt = $db->prepare("DELETE FROM exam_scores WHERE id = ?");
    if ($stmt->execute([$delete_id])) {
        $message = "成绩记录已成功删除！";
    } else {
        $error = "删除成绩记录时出错！";
    }
}

// 处理添加/更新成绩记录
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_score'])) {
    $student_id = (int)$_POST['student_id'];
    $exam_level_id = (int)$_POST['exam_level_id'];
    $score = (float)$_POST['score'];
    $exam_date = $_POST['exam_date'];
    $edit_id = isset($_POST['edit_id']) ? (int)$_POST['edit_id'] : 0;
    
    // 验证输入
    if ($student_id <= 0 || $exam_level_id <= 0 || $score < 0 || $score > 100 || empty($exam_date)) {
        $error = "请填写所有字段，分数必须在0-100之间！";
    } else {
        if ($edit_id > 0) {
            // 更新现有成绩记录
            $stmt = $db->prepare("UPDATE exam_scores SET student_id = ?, exam_level_id = ?, score = ?, exam_date = ? WHERE id = ?");
            $result = $stmt->execute([$student_id, $exam_level_id, $score, $exam_date, $edit_id]);
            
            if ($result) {
                $message = "成绩记录已成功更新！";
                // 更新成功后重定向到列表页面
                header("Location: exam_scores.php?success=update");
                exit();
            } else {
                $error = "更新成绩记录时出错！";
            }
        } else {
            // 添加新成绩记录
            $stmt = $db->prepare("INSERT INTO exam_scores (student_id, exam_level_id, score, exam_date, created_at) VALUES (?, ?, ?, ?, NOW())");
            if ($stmt->execute([$student_id, $exam_level_id, $score, $exam_date])) {
                $message = "新成绩记录已成功添加！";
                // 添加成功后重定向到列表页面
                header("Location: exam_scores.php?success=add");
                exit();
            } else {
                $error = "添加新成绩记录时出错！";
            }
        }
    }
}

// 处理成功消息
if (isset($_GET['success'])) {
    if ($_GET['success'] == 'add') {
        $message = "新成绩记录已成功添加！";
    } elseif ($_GET['success'] == 'update') {
        $message = "成绩记录已成功更新！";
    }
}

// 获取要编辑的成绩记录信息
$edit_score = null;
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $db->prepare("SELECT * FROM exam_scores WHERE id = ?");
    $stmt->execute([$edit_id]);
    $edit_score = $stmt->fetch(PDO::FETCH_ASSOC);
}

// 获取所有成绩记录列表，默认按ID排序
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'id';
$order = isset($_GET['order']) ? $_GET['order'] : 'desc';

// 验证排序参数
$allowed_sort = ['id', 'student_name', 'category_name', 'level_name', 'score', 'exam_date'];
if (!in_array($sort, $allowed_sort)) {
    $sort = 'id';
}
$allowed_order = ['asc', 'desc'];
if (!in_array($order, $allowed_order)) {
    $order = 'desc';
}

// 处理排序参数
$sort_map = [
    'id' => 'es.id',
    'student_name' => 's.name',
    'category_name' => 'ec.category_name',
    'level_name' => 'el.level_name',
    'score' => 'es.score',
    'exam_date' => 'es.exam_date'
];

$sort_column = isset($sort_map[$sort]) ? $sort_map[$sort] : 'es.id';
$sort_order = "$sort_column $order";

// 搜索参数
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category_filter = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$where_clause = '';
$params = [];

if (!empty($search) || $category_filter > 0) {
    $where_clause = " WHERE ";
    $conditions = [];
    
    if (!empty($search)) {
        $conditions[] = "(s.name LIKE ? OR ec.category_name LIKE ? OR el.level_name LIKE ?)";
        $search_param = "%$search%";
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
    }
    
    if ($category_filter > 0) {
        $conditions[] = "ec.id = ?";
        $params[] = $category_filter;
    }
    
    $where_clause .= implode(" AND ", $conditions);
}

// 分页
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// 获取考试类别列表（用于筛选）
$stmt = $db->prepare("SELECT * FROM exam_categories ORDER BY category_name");
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 获取总记录数
$count_sql = "SELECT COUNT(*) FROM exam_scores es
              JOIN students s ON es.student_id = s.id
              JOIN exam_levels el ON es.exam_level_id = el.id
              JOIN exam_categories ec ON el.category_id = ec.id" . $where_clause;
$stmt = $db->prepare($count_sql);
if (!empty($params)) {
    $stmt->execute($params);
} else {
    $stmt->execute();
}
$total_records = $stmt->fetchColumn();
$total_pages = ceil($total_records / $per_page);

// 获取成绩列表
$sql = "SELECT es.*, s.name as student_name, el.level_name, ec.category_name, ec.id as category_id
        FROM exam_scores es
        JOIN students s ON es.student_id = s.id
        JOIN exam_levels el ON es.exam_level_id = el.id
        JOIN exam_categories ec ON el.category_id = ec.id" . $where_clause . 
       " ORDER BY $sort_order LIMIT $offset, $per_page";
$stmt = $db->prepare($sql);
if (!empty($params)) {
    $stmt->execute($params);
} else {
    $stmt->execute();
}
$scores = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 获取所有学生列表（用于下拉选择）
$stmt = $db->prepare("SELECT id, name FROM students ORDER BY name");
$stmt->execute();
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 获取所有考级列表（用于下拉选择）
$stmt = $db->prepare("SELECT el.id, CONCAT(ec.category_name, ' - ', el.level_name) as level_full_name 
                      FROM exam_levels el
                      JOIN exam_categories ec ON el.category_id = ec.id
                      ORDER BY ec.category_name, el.level_order");
$stmt->execute();
$exam_levels = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 页面标题
$page_title = "成绩管理";

// 获取当前用户信息
$user_id = $_SESSION['user_id'];
$stmt = $db->prepare("SELECT * FROM accounts WHERE id = ?");
$stmt->execute([$user_id]);
$current_user = $stmt->fetch();
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
        .score-status {
            padding: 3px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
            text-align: center;
        }
        .score-pass {
            background-color: #E3F9E5;
            color: #31A24C;
        }
        .score-fail {
            background-color: #FFEFEF;
            color: #FF5757;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include 'admin_sidebar.php'; ?>
        
        <div class="admin-content">
            <div class="admin-content-header">
                <h1>成绩管理</h1>
                <div>
                    <a href="exam_scores.php" class="admin-btn admin-btn-primary"><i class="fas fa-graduation-cap"></i> 所有成绩</a>
                    <a href="exam_scores.php?action=add" class="admin-btn admin-btn-outline"><i class="fas fa-plus"></i> 添加成绩</a>
                </div>
            </div>
            
            <?php if (!empty($message)): ?>
                <div class="message success"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <?php if (!empty($error)): ?>
                <div class="message error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <!-- 成绩表单 -->
            <?php if (isset($_GET['action']) && $_GET['action'] == 'add' || isset($_GET['edit'])): ?>
            <div class="admin-form">
                <h2><?php echo $edit_score ? '编辑成绩记录' : '添加新成绩记录'; ?></h2>
                <form method="post" action="exam_scores.php">
                    <?php if ($edit_score): ?>
                        <input type="hidden" name="edit_id" value="<?php echo $edit_score['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="student_id">学生</label>
                        <select id="student_id" name="student_id" class="form-control" required>
                            <option value="">-- 选择学生 --</option>
                            <?php foreach ($students as $student): ?>
                                <option value="<?php echo $student['id']; ?>" <?php echo ($edit_score && $edit_score['student_id'] == $student['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($student['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="exam_level_id">考试级别</label>
                        <select id="exam_level_id" name="exam_level_id" class="form-control" required>
                            <option value="">-- 选择考试级别 --</option>
                            <?php foreach ($exam_levels as $level): ?>
                                <option value="<?php echo $level['id']; ?>" <?php echo ($edit_score && $edit_score['exam_level_id'] == $level['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($level['level_full_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="score">分数</label>
                        <input type="number" id="score" name="score" class="form-control" min="0" max="100" step="0.01" value="<?php echo $edit_score ? htmlspecialchars($edit_score['score']) : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="exam_date">考试日期</label>
                        <input type="date" id="exam_date" name="exam_date" class="form-control" value="<?php echo $edit_score ? htmlspecialchars($edit_score['exam_date']) : date('Y-m-d'); ?>" required>
                    </div>
                    
                    <div class="form-buttons">
                        <button type="submit" name="save_score" class="admin-btn admin-btn-primary"><?php echo $edit_score ? '更新成绩记录' : '添加成绩记录'; ?></button>
                        <a href="exam_scores.php" class="admin-btn admin-btn-outline">取消</a>
                    </div>
                </form>
            </div>
            <?php endif; ?>
            
            <!-- 成绩搜索和筛选 -->
            <?php if (!isset($_GET['action']) && !isset($_GET['edit'])): ?>
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2 class="admin-card-title">成绩列表</h2>
                    <div style="display: flex; gap: 10px;">
                        <form class="search-form" method="get" action="exam_scores.php" style="display: flex; gap: 10px; width: 500px;">
                            <select name="category" class="form-control" style="margin: 0; width: 200px;">
                                <option value="0">所有类别</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>" <?php echo $category_filter == $category['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['category_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <input type="text" name="search" class="form-control" placeholder="搜索学生或考级..." value="<?php echo htmlspecialchars($search); ?>" style="margin: 0;">
                            <button type="submit" class="admin-btn admin-btn-secondary" style="padding: 10px; margin: 0;"><i class="fas fa-search"></i></button>
                        </form>
                    </div>
                </div>
                
                <!-- 成绩列表 -->
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>
                                    <a href="?sort=id&order=<?php echo $sort == 'id' && $order == 'asc' ? 'desc' : 'asc'; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo $category_filter; ?>" class="<?php echo $sort == 'id' ? $order : ''; ?>">
                                        ID <?php echo $sort == 'id' ? ($order == 'asc' ? '↑' : '↓') : ''; ?>
                                    </a>
                                </th>
                                <th>
                                    <a href="?sort=student_name&order=<?php echo $sort == 'student_name' && $order == 'asc' ? 'desc' : 'asc'; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo $category_filter; ?>" class="<?php echo $sort == 'student_name' ? $order : ''; ?>">
                                        学生姓名 <?php echo $sort == 'student_name' ? ($order == 'asc' ? '↑' : '↓') : ''; ?>
                                    </a>
                                </th>
                                <th>
                                    <a href="?sort=category_name&order=<?php echo $sort == 'category_name' && $order == 'asc' ? 'desc' : 'asc'; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo $category_filter; ?>" class="<?php echo $sort == 'category_name' ? $order : ''; ?>">
                                        考试类别 <?php echo $sort == 'category_name' ? ($order == 'asc' ? '↑' : '↓') : ''; ?>
                                    </a>
                                </th>
                                <th>
                                    <a href="?sort=level_name&order=<?php echo $sort == 'level_name' && $order == 'asc' ? 'desc' : 'asc'; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo $category_filter; ?>" class="<?php echo $sort == 'level_name' ? $order : ''; ?>">
                                        考试级别 <?php echo $sort == 'level_name' ? ($order == 'asc' ? '↑' : '↓') : ''; ?>
                                    </a>
                                </th>
                                <th>
                                    <a href="?sort=score&order=<?php echo $sort == 'score' && $order == 'asc' ? 'desc' : 'asc'; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo $category_filter; ?>" class="<?php echo $sort == 'score' ? $order : ''; ?>">
                                        分数 <?php echo $sort == 'score' ? ($order == 'asc' ? '↑' : '↓') : ''; ?>
                                    </a>
                                </th>
                                <th>
                                    <a href="?sort=exam_date&order=<?php echo $sort == 'exam_date' && $order == 'asc' ? 'desc' : 'asc'; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo $category_filter; ?>" class="<?php echo $sort == 'exam_date' ? $order : ''; ?>">
                                        考试日期 <?php echo $sort == 'exam_date' ? ($order == 'asc' ? '↑' : '↓') : ''; ?>
                                    </a>
                                </th>
                                <th>状态</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($scores) > 0): ?>
                                <?php foreach ($scores as $score): ?>
                                    <tr>
                                        <td><?php echo $score['id']; ?></td>
                                        <td><?php echo htmlspecialchars($score['student_name']); ?></td>
                                        <td><?php echo htmlspecialchars($score['category_name']); ?></td>
                                        <td><?php echo htmlspecialchars($score['level_name']); ?></td>
                                        <td>
                                            <span class="<?php echo $score['score'] >= 60 ? 'text-success' : 'text-danger'; ?>">
                                                <?php echo number_format($score['score'], 2); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('Y-m-d', strtotime($score['exam_date'])); ?></td>
                                        <td>
                                            <span class="score-status <?php echo $score['score'] >= 60 ? 'score-pass' : 'score-fail'; ?>">
                                                <?php echo $score['score'] >= 60 ? '通过' : '未通过'; ?>
                                            </span>
                                        </td>
                                        <td class="actions">
                                            <a href="?edit=<?php echo $score['id']; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo $category_filter; ?>&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>&page=<?php echo $page; ?>" class="admin-btn admin-btn-secondary" style="padding: 5px 10px; font-size: 12px;">
                                                <i class="fas fa-edit"></i> 编辑
                                            </a>
                                            <a href="?delete=<?php echo $score['id']; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo $category_filter; ?>&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>&page=<?php echo $page; ?>" class="admin-btn" style="padding: 5px 10px; font-size: 12px; background: linear-gradient(to right, var(--admin-danger), #FF6B6B); color: white;" onclick="return confirm('确定要删除此成绩记录吗？')">
                                                <i class="fas fa-trash-alt"></i> 删除
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" style="text-align: center;">没有找到成绩记录</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- 分页 -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=1&search=<?php echo urlencode($search); ?>&category=<?php echo $category_filter; ?>&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>"><i class="fas fa-angle-double-left"></i></a>
                            <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo $category_filter; ?>&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>"><i class="fas fa-angle-left"></i></a>
                        <?php endif; ?>
                        
                        <?php
                        $start_page = max(1, $page - 2);
                        $end_page = min($total_pages, $page + 2);
                        
                        for ($i = $start_page; $i <= $end_page; $i++):
                        ?>
                            <?php if ($i == $page): ?>
                                <span class="active"><?php echo $i; ?></span>
                            <?php else: ?>
                                <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo $category_filter; ?>&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>"><?php echo $i; ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo $category_filter; ?>&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>"><i class="fas fa-angle-right"></i></a>
                            <a href="?page=<?php echo $total_pages; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo $category_filter; ?>&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>"><i class="fas fa-angle-double-right"></i></a>
                        <?php endif; ?>
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
            mobileHeader.appendChild(document.createElement('div')).innerHTML = '<h1>成绩管理</h1>';
            
            adminContent.insertBefore(mobileHeader, adminContent.firstChild);
        }
    });
    </script>
</body>
</html> 