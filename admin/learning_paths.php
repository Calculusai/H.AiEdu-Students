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

// 处理学习路径操作
$message = '';
$error = '';

// 处理删除学习路径
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $delete_id = (int)$_GET['delete'];
    
    try {
        $db->beginTransaction();
        
        // 首先删除学习路径与课程的关联
        $stmt = $db->prepare("DELETE FROM learning_path_courses WHERE path_id = ?");
        $stmt->execute([$delete_id]);
        
        // 然后删除学习路径
        $stmt = $db->prepare("DELETE FROM learning_paths WHERE id = ?");
        $stmt->execute([$delete_id]);
        
        $db->commit();
        $message = "学习路径已成功删除！";
    } catch (Exception $e) {
        $db->rollBack();
        $error = "删除学习路径时出错：" . $e->getMessage();
    }
}

// 处理添加/更新学习路径
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_path'])) {
    $student_id = (int)$_POST['student_id'];
    $path_name = trim($_POST['path_name']);
    $description = trim($_POST['description']);
    $edit_id = isset($_POST['edit_id']) ? (int)$_POST['edit_id'] : 0;
    $course_ids = isset($_POST['course_ids']) ? $_POST['course_ids'] : [];
    $completed_courses = isset($_POST['completed_courses']) ? $_POST['completed_courses'] : [];
    
    // 验证输入
    if (empty($student_id) || empty($path_name) || empty($description)) {
        $error = "学生、路径名称和描述都是必填的！";
    } elseif (empty($course_ids)) {
        $error = "学习路径必须包含至少一个课程！";
    } else {
        try {
            $db->beginTransaction();
            
            // 获取学生关联的用户账号ID
            $stmt = $db->prepare("SELECT account_id FROM students WHERE id = ?");
            $stmt->execute([$student_id]);
            $account_id = $stmt->fetchColumn();
            
            if (!$account_id) {
                throw new Exception("无法获取学生关联的账号ID");
            }
            
            if ($edit_id > 0) {
                // 更新现有学习路径
                $stmt = $db->prepare("UPDATE learning_paths SET student_id = ?, path_name = ?, description = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$student_id, $path_name, $description, $edit_id]);
                
                // 获取现有课程关联
                $stmt = $db->prepare("SELECT course_id FROM learning_path_courses WHERE path_id = ?");
                $stmt->execute([$edit_id]);
                $existing_courses = $stmt->fetchAll(PDO::FETCH_COLUMN);
                
                // 删除现有课程关联
                $stmt = $db->prepare("DELETE FROM learning_path_courses WHERE path_id = ?");
                $stmt->execute([$edit_id]);
                
                // 添加新的课程关联
                $stmt = $db->prepare("INSERT INTO learning_path_courses (path_id, course_id, `order`) VALUES (?, ?, ?)");
                foreach ($course_ids as $sequence => $course_id) {
                    $stmt->execute([$edit_id, $course_id, $sequence + 1]);
                    
                    // 确保course_stats表中有对应的记录
                    $stmt_check = $db->prepare("SELECT id FROM course_stats WHERE course_id = ? AND user_id = ?");
                    $stmt_check->execute([$course_id, $account_id]);
                    
                    // 检查该课程是否被标记为已完成
                    $is_completed = in_array($course_id, $completed_courses) ? 1 : 0;
                    $progress = $is_completed ? 100 : 0;
                    
                    if ($stmt_check->fetchColumn()) {
                        // 如果记录存在，更新完成状态
                        $stmt_update = $db->prepare("UPDATE course_stats SET progress = ?, complete_status = ? WHERE course_id = ? AND user_id = ?");
                        $stmt_update->execute([$progress, $is_completed, $course_id, $account_id]);
                    } else {
                        // 如果记录不存在，添加新记录
                        $stmt_insert = $db->prepare("INSERT INTO course_stats (course_id, user_id, progress, complete_status) VALUES (?, ?, ?, ?)");
                        $stmt_insert->execute([$course_id, $account_id, $progress, $is_completed]);
                    }
                    
                    // 确保user_courses表中有对应的记录
                    $stmt_check = $db->prepare("SELECT id FROM user_courses WHERE course_id = ? AND user_id = ?");
                    $stmt_check->execute([$course_id, $account_id]);
                    
                    if ($stmt_check->fetchColumn()) {
                        // 如果记录存在，更新完成状态和日期
                        $complete_date = $is_completed ? "NOW()" : "NULL";
                        $stmt_update = $db->prepare("UPDATE user_courses SET progress = ?, complete_date = " . ($is_completed ? "NOW()" : "NULL") . " WHERE course_id = ? AND user_id = ?");
                        $stmt_update->execute([$progress, $course_id, $account_id]);
                    } else {
                        // 如果记录不存在，添加新记录
                        $stmt_insert = $db->prepare("INSERT INTO user_courses (user_id, course_id, progress, start_date, complete_date) VALUES (?, ?, ?, NOW(), " . ($is_completed ? "NOW()" : "NULL") . ")");
                        $stmt_insert->execute([$account_id, $course_id, $progress]);
                    }
                }
                
                // 检查被移除的课程
                $removed_courses = array_diff($existing_courses, $course_ids);
                // 这里可以选择是否保留被移除课程的统计记录，本例中选择保留
                
                $message = "学习路径已成功更新！";
            } else {
                // 添加新学习路径
                $stmt = $db->prepare("INSERT INTO learning_paths (student_id, path_name, description, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
                $stmt->execute([$student_id, $path_name, $description]);
                $path_id = $db->lastInsertId();
                
                // 添加课程关联
                $stmt = $db->prepare("INSERT INTO learning_path_courses (path_id, course_id, `order`) VALUES (?, ?, ?)");
                foreach ($course_ids as $sequence => $course_id) {
                    $stmt->execute([$path_id, $course_id, $sequence + 1]);
                    
                    // 检查该课程是否被标记为已完成
                    $is_completed = in_array($course_id, $completed_courses) ? 1 : 0;
                    $progress = $is_completed ? 100 : 0;
                    
                    // 确保course_stats表中有对应的记录
                    $stmt_check = $db->prepare("SELECT id FROM course_stats WHERE course_id = ? AND user_id = ?");
                    $stmt_check->execute([$course_id, $account_id]);
                    
                    if ($stmt_check->fetchColumn()) {
                        // 如果记录存在，更新完成状态
                        $stmt_update = $db->prepare("UPDATE course_stats SET progress = ?, complete_status = ? WHERE course_id = ? AND user_id = ?");
                        $stmt_update->execute([$progress, $is_completed, $course_id, $account_id]);
                    } else {
                        // 如果记录不存在，添加新记录
                        $stmt_insert = $db->prepare("INSERT INTO course_stats (course_id, user_id, progress, complete_status) VALUES (?, ?, ?, ?)");
                        $stmt_insert->execute([$course_id, $account_id, $progress, $is_completed]);
                    }
                    
                    // 确保user_courses表中有对应的记录
                    $stmt_check = $db->prepare("SELECT id FROM user_courses WHERE course_id = ? AND user_id = ?");
                    $stmt_check->execute([$course_id, $account_id]);
                    
                    if ($stmt_check->fetchColumn()) {
                        // 如果记录存在，更新完成状态和日期
                        $stmt_update = $db->prepare("UPDATE user_courses SET progress = ?, complete_date = " . ($is_completed ? "NOW()" : "NULL") . " WHERE course_id = ? AND user_id = ?");
                        $stmt_update->execute([$progress, $course_id, $account_id]);
                    } else {
                        // 如果记录不存在，添加新记录
                        $stmt_insert = $db->prepare("INSERT INTO user_courses (user_id, course_id, progress, start_date, complete_date) VALUES (?, ?, ?, NOW(), " . ($is_completed ? "NOW()" : "NULL") . ")");
                        $stmt_insert->execute([$account_id, $course_id, $progress]);
                    }
                }
                
                $message = "新学习路径已成功添加！";
            }
            
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            $error = "保存学习路径时出错：" . $e->getMessage();
        }
    }
}

// 获取所有学生
$stmt = $db->query("SELECT id, name FROM students ORDER BY name");
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 获取要编辑的学习路径信息
$edit_path = null;
$path_courses = [];
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $db->prepare("SELECT * FROM learning_paths WHERE id = ?");
    $stmt->execute([$edit_id]);
    $edit_path = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($edit_path) {
        // 获取学生关联的用户账号ID
        $stmt = $db->prepare("SELECT account_id FROM students WHERE id = ?");
        $stmt->execute([$edit_path['student_id']]);
        $account_id = $stmt->fetchColumn();
        
        // 获取学习路径关联的课程和完成状态
        $stmt = $db->prepare("SELECT lpc.*, c.title as course_title, 
                             COALESCE(cs.complete_status, 0) as complete_status,
                             COALESCE(cs.progress, 0) as progress
                             FROM learning_path_courses lpc 
                             JOIN courses c ON lpc.course_id = c.id 
                             LEFT JOIN course_stats cs ON lpc.course_id = cs.course_id AND cs.user_id = ?
                             WHERE lpc.path_id = ? 
                             ORDER BY lpc.`order`");
        $stmt->execute([$account_id, $edit_id]);
        $path_courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// 获取所有课程供选择
$stmt = $db->prepare("SELECT id, title, category FROM courses ORDER BY category, title");
$stmt->execute();
$all_courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 获取所有学习路径
$stmt = $db->prepare("SELECT lp.*, s.name as student_name,
                      (SELECT COUNT(*) FROM learning_path_courses WHERE path_id = lp.id) as course_count
                      FROM learning_paths lp
                      JOIN students s ON lp.student_id = s.id
                      ORDER BY s.name, lp.path_name");
$stmt->execute();
$learning_paths = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 页面标题
$page_title = "学习路径管理";
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
        
        .course-item {
            background: rgba(0,0,0,0.03);
            padding: 10px 15px;
            margin-bottom: 10px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            cursor: move;
        }
        
        .course-item .handle {
            margin-right: 10px;
            color: var(--text-secondary);
        }
        
        .course-item .course-title {
            flex: 1;
        }
        
        .course-item .remove-btn {
            cursor: pointer;
            color: var(--error);
            margin-left: 10px;
        }
        
        .course-item .complete-btn {
            cursor: pointer;
            color: var(--text-secondary);
            margin-left: 10px;
        }
        
        .course-item .complete-btn.completed {
            color: var(--green);
        }
        
        #selected-courses {
            min-height: 50px;
            border: 2px dashed var(--border-color);
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        #selected-courses:empty::before {
            content: '将课程拖放到这里添加到学习路径';
            color: var(--text-secondary);
            font-style: italic;
        }
        
        .course-select-container {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .course-list {
            flex: 1;
            max-height: 400px;
            overflow-y: auto;
            padding: 15px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include 'admin_sidebar.php'; ?>
        
        <div class="admin-content">
            <div class="admin-content-header">
                <h1>学习路径管理</h1>
                <div>
                    <a href="learning_paths.php" class="admin-btn admin-btn-primary"><i class="fas fa-list"></i> 所有学习路径</a>
                    <a href="learning_paths.php?action=add" class="admin-btn admin-btn-outline"><i class="fas fa-plus"></i> 添加学习路径</a>
                </div>
            </div>
            
            <?php if (!empty($message)): ?>
                <div class="message success"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <?php if (!empty($error)): ?>
                <div class="message error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <!-- 学习路径表单 -->
            <?php if (isset($_GET['action']) && $_GET['action'] == 'add' || isset($_GET['edit'])): ?>
            <div class="admin-form">
                <h2><?php echo $edit_path ? '编辑学习路径' : '添加新学习路径'; ?></h2>
                <form method="post" action="learning_paths.php">
                    <?php if ($edit_path): ?>
                        <input type="hidden" name="edit_id" value="<?php echo $edit_path['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="student_id">学生</label>
                        <select id="student_id" name="student_id" class="form-control" required>
                            <option value="">-- 选择学生 --</option>
                            <?php foreach ($students as $student): ?>
                                <option value="<?php echo $student['id']; ?>" <?php echo ($edit_path && $edit_path['student_id'] == $student['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($student['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="path_name">路径名称</label>
                        <input type="text" id="path_name" name="path_name" class="form-control" value="<?php echo $edit_path ? htmlspecialchars($edit_path['path_name']) : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">路径描述</label>
                        <textarea id="description" name="description" class="form-control" rows="4" required><?php echo $edit_path ? htmlspecialchars($edit_path['description']) : ''; ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>选择课程</label>
                        <p class="form-text">拖拽课程到右侧区域，排序即为学习顺序</p>
                        
                        <div class="course-select-container">
                            <div class="course-list">
                                <h3>可用课程</h3>
                                <div id="available-courses">
                                    <?php
                                    $used_course_ids = array_column($path_courses, 'course_id');
                                    $grouped_courses = [];
                                    foreach ($all_courses as $course) {
                                        if (!in_array($course['id'], $used_course_ids)) {
                                            $grouped_courses[$course['category']][] = $course;
                                        }
                                    }
                                    
                                    foreach ($grouped_courses as $category => $courses): ?>
                                        <div class="course-category">
                                            <h4><?php echo htmlspecialchars($category); ?></h4>
                                            <?php foreach ($courses as $course): ?>
                                                <div class="course-item" draggable="true" data-id="<?php echo $course['id']; ?>">
                                                    <span class="handle"><i class="fas fa-grip-lines"></i></span>
                                                    <span class="course-title"><?php echo htmlspecialchars($course['title']); ?></span>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <div class="course-list">
                                <h3>学习路径课程</h3>
                                <div id="selected-courses">
                                    <?php foreach ($path_courses as $path_course): ?>
                                        <div class="course-item" draggable="true" data-id="<?php echo $path_course['course_id']; ?>" data-completed="<?php echo $path_course['complete_status']; ?>">
                                            <span class="handle"><i class="fas fa-grip-lines"></i></span>
                                            <span class="course-title"><?php echo htmlspecialchars($path_course['course_title']); ?></span>
                                            <span class="complete-btn <?php echo $path_course['complete_status'] ? 'completed' : ''; ?>" title="标记为<?php echo $path_course['complete_status'] ? '未' : '已'; ?>完成">
                                                <i class="fas fa-check-circle"></i>
                                            </span>
                                            <span class="remove-btn" title="移除课程"><i class="fas fa-times"></i></span>
                                            <input type="hidden" name="course_ids[]" value="<?php echo $path_course['course_id']; ?>">
                                            <?php if ($path_course['complete_status']): ?>
                                                <input type="hidden" name="completed_courses[]" value="<?php echo $path_course['course_id']; ?>" class="complete-status">
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-buttons">
                        <button type="submit" name="save_path" class="admin-btn admin-btn-primary"><?php echo $edit_path ? '更新学习路径' : '添加学习路径'; ?></button>
                        <a href="learning_paths.php" class="admin-btn admin-btn-outline">取消</a>
                    </div>
                </form>
            </div>
            <?php endif; ?>
            
            <!-- 学习路径列表 -->
            <?php if (!isset($_GET['action']) && !isset($_GET['edit'])): ?>
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2 class="admin-card-title">学习路径列表</h2>
                </div>
                
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>学生</th>
                                <th>路径名称</th>
                                <th>包含课程数</th>
                                <th>创建时间</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($learning_paths) > 0): ?>
                                <?php foreach ($learning_paths as $path): ?>
                                    <tr>
                                        <td><?php echo $path['id']; ?></td>
                                        <td>
                                            <span class="student-name">
                                                <?php echo htmlspecialchars($path['student_name']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($path['path_name']); ?></td>
                                        <td><?php echo $path['course_count']; ?> 门课程</td>
                                        <td><?php echo date('Y-m-d H:i', strtotime($path['created_at'])); ?></td>
                                        <td class="actions">
                                            <a href="?edit=<?php echo $path['id']; ?>" class="admin-btn admin-btn-secondary" style="padding: 5px 10px; font-size: 12px;">
                                                <i class="fas fa-edit"></i> 编辑
                                            </a>
                                            <a href="?delete=<?php echo $path['id']; ?>" class="admin-btn" style="padding: 5px 10px; font-size: 12px; background: linear-gradient(to right, var(--admin-danger), #FF6B6B); color: white;" onclick="return confirm('确定要删除此学习路径吗？')">
                                                <i class="fas fa-trash-alt"></i> 删除
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" style="text-align: center;">没有找到学习路径记录</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
            
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.14.0/Sortable.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // 拖拽功能
        if (typeof Sortable !== 'undefined') {
            // 已选择课程列表排序
            const selectedCourses = document.getElementById('selected-courses');
            if (selectedCourses) {
                Sortable.create(selectedCourses, {
                    animation: 150,
                    ghostClass: 'course-item-ghost',
                    onEnd: function() {
                        updateCourseInputs();
                    }
                });
                
                // 为移除按钮添加事件监听
                selectedCourses.addEventListener('click', function(e) {
                    if (e.target.closest('.remove-btn')) {
                        const item = e.target.closest('.course-item');
                        item.remove();
                        updateCourseInputs();
                    }
                    
                    // 处理完成标记点击
                    if (e.target.closest('.complete-btn')) {
                        const completeBtn = e.target.closest('.complete-btn');
                        const item = completeBtn.closest('.course-item');
                        const courseId = item.dataset.id;
                        
                        // 切换完成状态
                        const isCompleted = completeBtn.classList.contains('completed');
                        completeBtn.classList.toggle('completed');
                        completeBtn.title = `标记为${isCompleted ? '已' : '未'}完成`;
                        item.dataset.completed = isCompleted ? '0' : '1';
                        
                        // 更新隐藏字段
                        let completeInput = item.querySelector('.complete-status');
                        
                        if (!isCompleted) { // 标记为已完成
                            if (!completeInput) {
                                completeInput = document.createElement('input');
                                completeInput.type = 'hidden';
                                completeInput.name = 'completed_courses[]';
                                completeInput.value = courseId;
                                completeInput.className = 'complete-status';
                                item.appendChild(completeInput);
                            }
                        } else { // 移除已完成标记
                            if (completeInput) {
                                completeInput.remove();
                            }
                        }
                    }
                });
            }
            
            // 可用课程拖拽
            const availableCourses = document.getElementById('available-courses');
            if (availableCourses) {
                const courseItems = availableCourses.querySelectorAll('.course-item');
                courseItems.forEach(item => {
                    item.addEventListener('dragstart', function(e) {
                        e.dataTransfer.setData('text/plain', JSON.stringify({
                            id: this.dataset.id,
                            title: this.querySelector('.course-title').textContent
                        }));
                    });
                });
            }
            
            // 放置区域处理，添加完成状态
            if (selectedCourses) {
                selectedCourses.addEventListener('dragover', function(e) {
                    e.preventDefault();
                    this.classList.add('drag-over');
                });
                
                selectedCourses.addEventListener('dragleave', function() {
                    this.classList.remove('drag-over');
                });
                
                selectedCourses.addEventListener('drop', function(e) {
                    e.preventDefault();
                    this.classList.remove('drag-over');
                    
                    try {
                        const data = JSON.parse(e.dataTransfer.getData('text/plain'));
                        
                        // 检查课程是否已存在
                        const exists = this.querySelector(`.course-item[data-id="${data.id}"]`);
                        if (!exists) {
                            const courseItem = document.createElement('div');
                            courseItem.className = 'course-item';
                            courseItem.setAttribute('draggable', 'true');
                            courseItem.dataset.id = data.id;
                            courseItem.dataset.completed = '0';
                            courseItem.innerHTML = `
                                <span class="handle"><i class="fas fa-grip-lines"></i></span>
                                <span class="course-title">${data.title}</span>
                                <span class="complete-btn" title="标记为已完成"><i class="fas fa-check-circle"></i></span>
                                <span class="remove-btn" title="移除课程"><i class="fas fa-times"></i></span>
                                <input type="hidden" name="course_ids[]" value="${data.id}">
                            `;
                            this.appendChild(courseItem);
                        }
                    } catch (error) {
                        console.error('拖放数据解析失败：', error);
                    }
                });
            }
            
            // 更新隐藏输入字段
            function updateCourseInputs() {
                if (selectedCourses) {
                    const items = selectedCourses.querySelectorAll('.course-item');
                    items.forEach((item, index) => {
                        let input = item.querySelector('input[name="course_ids[]"]');
                        if (!input) {
                            input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = 'course_ids[]';
                            item.appendChild(input);
                        }
                        input.value = item.dataset.id;
                        
                        // 处理完成状态输入
                        const isCompleted = item.dataset.completed === '1';
                        let completeInput = item.querySelector('.complete-status');
                        
                        if (isCompleted) {
                            if (!completeInput) {
                                completeInput = document.createElement('input');
                                completeInput.type = 'hidden';
                                completeInput.name = 'completed_courses[]';
                                completeInput.className = 'complete-status';
                                completeInput.value = item.dataset.id;
                                item.appendChild(completeInput);
                            }
                        } else {
                            if (completeInput) {
                                completeInput.remove();
                            }
                        }
                    });
                }
            }
        }
    });
    </script>
</body>
</html> 