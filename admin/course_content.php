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

// 验证课程ID参数
if (!isset($_GET['course_id']) || empty($_GET['course_id'])) {
    $_SESSION['error'] = "缺少课程ID参数！";
    header("Location: courses.php");
    exit();
}

$course_id = (int)$_GET['course_id'];

// 获取课程信息
$stmt = $db->prepare("SELECT * FROM courses WHERE id = ?");
$stmt->execute([$course_id]);
$course = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$course) {
    $_SESSION['error'] = "找不到指定的课程！";
    header("Location: courses.php");
    exit();
}

// 处理消息
$message = '';
$error = '';

// 处理删除内容
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $delete_id = (int)$_GET['delete'];
    
    // 删除前先检查该内容是否有学习记录
    $stmt = $db->prepare("SELECT COUNT(*) FROM user_progress WHERE content_id = ?");
    $stmt->execute([$delete_id]);
    $progress_count = $stmt->fetchColumn();
    
    if ($progress_count > 0) {
        // 如果有学习记录，显示警告但仍允许删除
        try {
            $db->beginTransaction();
            
            // 删除相关学习记录
            $stmt = $db->prepare("DELETE FROM user_progress WHERE content_id = ?");
            $stmt->execute([$delete_id]);
            
            // 删除内容
            $stmt = $db->prepare("DELETE FROM course_content WHERE id = ? AND course_id = ?");
            $stmt->execute([$delete_id, $course_id]);
            
            // 重新排序序号
            reorderContentSequence($db, $course_id);
            
            $db->commit();
            $message = "课程内容已成功删除！（已同时删除 $progress_count 条学习记录）";
        } catch (Exception $e) {
            $db->rollBack();
            $error = "删除课程内容时出错：" . $e->getMessage();
        }
    } else {
        // 如果没有学习记录，直接删除
        $stmt = $db->prepare("DELETE FROM course_content WHERE id = ? AND course_id = ?");
        if ($stmt->execute([$delete_id, $course_id])) {
            // 重新排序序号
            reorderContentSequence($db, $course_id);
            $message = "课程内容已成功删除！";
        } else {
            $error = "删除课程内容时出错！";
        }
    }
}

// 重排序内容
if (isset($_POST['action']) && $_POST['action'] == 'reorder') {
    $content_ids = $_POST['content_order'];
    $sequence = 1;
    
    try {
        $db->beginTransaction();
        
        foreach ($content_ids as $content_id) {
            $stmt = $db->prepare("UPDATE course_content SET sequence = ? WHERE id = ? AND course_id = ?");
            $stmt->execute([$sequence, $content_id, $course_id]);
            $sequence++;
        }
        
        $db->commit();
        $message = "课程内容顺序已成功更新！";
    } catch (Exception $e) {
        $db->rollBack();
        $error = "更新课程内容顺序时出错：" . $e->getMessage();
    }
    
    // 返回JSON响应（用于AJAX请求）
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
        echo json_encode(['success' => empty($error), 'message' => empty($error) ? $message : $error]);
        exit;
    }
}

// 处理添加/更新内容
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_content']) && (!isset($_POST['action']) || $_POST['action'] != 'reorder')) {
    $title = trim($_POST['title']);
    $content_type = $_POST['content_type'];
    $content = trim($_POST['content']);
    $edit_id = isset($_POST['edit_id']) ? (int)$_POST['edit_id'] : 0;
    
    // 验证输入
    if (empty($title) || empty($content)) {
        $error = "标题和内容都是必填的！";
    } else {
        if ($edit_id > 0) {
            // 更新现有内容
            $stmt = $db->prepare("UPDATE course_content SET title = ?, content_type = ?, content = ?, updated_at = NOW() WHERE id = ? AND course_id = ?");
            if ($stmt->execute([$title, $content_type, $content, $edit_id, $course_id])) {
                $message = "课程内容已成功更新！";
                // 更新成功后重定向到内容列表页面
                header("Location: course_content.php?course_id=$course_id&success=update");
                exit();
            } else {
                $error = "更新课程内容时出错！";
            }
        } else {
            // 获取最大序号
            $stmt = $db->prepare("SELECT MAX(sequence) FROM course_content WHERE course_id = ?");
            $stmt->execute([$course_id]);
            $max_sequence = $stmt->fetchColumn();
            $sequence = $max_sequence ? $max_sequence + 1 : 1;
            
            // 添加新内容
            $stmt = $db->prepare("INSERT INTO course_content (course_id, title, content_type, content, sequence, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
            if ($stmt->execute([$course_id, $title, $content_type, $content, $sequence])) {
                $message = "新课程内容已成功添加！";
                // 添加成功后重定向到内容列表页面
                header("Location: course_content.php?course_id=$course_id&success=add");
                exit();
            } else {
                $error = "添加新课程内容时出错！";
            }
        }
    }
}

// 处理成功消息
if (isset($_GET['success'])) {
    if ($_GET['success'] == 'add') {
        $message = "新课程内容已成功添加！";
    } elseif ($_GET['success'] == 'update') {
        $message = "课程内容已成功更新！";
    }
}

// 获取要编辑的内容信息
$edit_content = null;
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $db->prepare("SELECT * FROM course_content WHERE id = ? AND course_id = ?");
    $stmt->execute([$edit_id, $course_id]);
    $edit_content = $stmt->fetch(PDO::FETCH_ASSOC);
}

// 获取课程所有内容
$stmt = $db->prepare("SELECT * FROM course_content WHERE course_id = ? ORDER BY sequence");
$stmt->execute([$course_id]);
$contents = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 帮助函数：重排序内容序号
function reorderContentSequence($db, $course_id) {
    $stmt = $db->prepare("SELECT id FROM course_content WHERE course_id = ? ORDER BY sequence");
    $stmt->execute([$course_id]);
    $content_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $sequence = 1;
    foreach ($content_ids as $content_id) {
        $stmt = $db->prepare("UPDATE course_content SET sequence = ? WHERE id = ?");
        $stmt->execute([$sequence, $content_id]);
        $sequence++;
    }
}

// 页面标题
$page_title = "管理课程内容 - " . htmlspecialchars($course['title']);
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
        .content-form {
            background: var(--admin-card-bg);
            border-radius: var(--admin-card-radius);
            padding: var(--space-md);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
            margin-bottom: var(--space-lg);
        }
        
        .content-list {
            background: var(--admin-card-bg);
            border-radius: var(--admin-card-radius);
            overflow: hidden;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
        }
        
        .content-item {
            padding: 15px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: move;
        }
        
        .content-item:last-child {
            border-bottom: none;
        }
        
        .content-item:hover {
            background-color: rgba(0, 0, 0, 0.02);
        }
        
        .content-info {
            flex: 1;
            padding: 0 15px;
        }
        
        .content-title {
            font-weight: bold;
            margin-bottom: 5px;
            color: var(--text-primary);
        }
        
        .content-meta {
            font-size: 14px;
            color: var(--text-secondary);
        }
        
        .content-type {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 15px;
            font-size: 12px;
            margin-right: 10px;
            color: white;
            font-weight: 600;
        }
        
        .content-type-text {
            background: linear-gradient(to right, var(--blue), #3498db);
        }
        
        .content-type-markdown {
            background: linear-gradient(to right, var(--purple), #9b59b6);
        }
        
        .content-type-code {
            background: linear-gradient(to right, #2c3e50, #34495e);
        }
        
        .content-type-quiz {
            background: linear-gradient(to right, var(--orange), #f39c12);
        }
        
        .content-type-video {
            background: linear-gradient(to right, var(--error), #e74c3c);
        }
        
        .content-handle {
            cursor: move;
            padding: 0 10px;
            color: var(--text-secondary);
            display: flex;
            align-items: center;
        }
        
        .content-handle:hover {
            color: var(--text-primary);
        }
        
        .empty-content {
            padding: 30px;
            text-align: center;
            color: var(--text-secondary);
        }
        
        .course-info {
            background: rgba(62, 198, 255, 0.1);
            padding: var(--space-md);
            border-radius: var(--radius-lg);
            margin-bottom: var(--space-md);
            border-left: 4px solid var(--blue);
        }
        
        .course-info h2 {
            color: var(--blue);
            margin-top: 0;
            margin-bottom: var(--space-xs);
        }
        
        .code-editor {
            font-family: monospace;
            background-color: #f8f9fa;
        }
        
        .form-tips {
            margin-top: 5px;
            font-size: 14px;
            color: var(--text-secondary);
            background: rgba(255, 179, 0, 0.1);
            padding: 8px 12px;
            border-radius: var(--radius-md);
            border-left: 3px solid var(--orange);
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include 'admin_sidebar.php'; ?>
        
        <div class="admin-content">
            <div class="admin-content-header">
                <h1>管理课程内容</h1>
                <div>
                    <a href="courses.php" class="admin-btn admin-btn-outline"><i class="fas fa-arrow-left"></i> 返回课程列表</a>
                </div>
            </div>
            
            <div class="course-info">
                <h2><?php echo htmlspecialchars($course['title']); ?></h2>
                <p><strong>类别:</strong> <?php echo htmlspecialchars($course['category']); ?></p>
                <p><strong>难度:</strong> 
                    <?php
                    switch ($course['difficulty']) {
                        case 'beginner':
                            echo '初级';
                            break;
                        case 'intermediate':
                            echo '中级';
                            break;
                        case 'advanced':
                            echo '高级';
                            break;
                        default:
                            echo htmlspecialchars($course['difficulty']);
                    }
                    ?>
                </p>
                <p><?php echo htmlspecialchars($course['description']); ?></p>
            </div>
            
            <?php if (!empty($message)): ?>
                <div class="message success"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <?php if (!empty($error)): ?>
                <div class="message error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <!-- 内容表单 -->
            <?php if (isset($_GET['action']) && $_GET['action'] == 'add' || isset($_GET['edit'])): ?>
            <div class="admin-form">
                <h2><?php echo $edit_content ? '编辑课程内容' : '添加新课程内容'; ?></h2>
                <form method="post" action="course_content.php?course_id=<?php echo $course_id; ?>">
                    <?php if ($edit_content): ?>
                        <input type="hidden" name="edit_id" value="<?php echo $edit_content['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="title">标题</label>
                        <input type="text" id="title" name="title" class="form-control" value="<?php echo $edit_content ? htmlspecialchars($edit_content['title']) : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="content_type">内容类型</label>
                        <select id="content_type" name="content_type" class="form-control" required>
                            <option value="text" <?php echo ($edit_content && $edit_content['content_type'] == 'text') ? 'selected' : ''; ?>>文本内容</option>
                            <option value="markdown" <?php echo ($edit_content && $edit_content['content_type'] == 'markdown') ? 'selected' : ''; ?>>Markdown格式</option>
                            <option value="code" <?php echo ($edit_content && $edit_content['content_type'] == 'code') ? 'selected' : ''; ?>>代码示例</option>
                            <option value="quiz" <?php echo ($edit_content && $edit_content['content_type'] == 'quiz') ? 'selected' : ''; ?>>测验题目</option>
                            <option value="video" <?php echo ($edit_content && $edit_content['content_type'] == 'video') ? 'selected' : ''; ?>>视频链接</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="content">内容</label>
                        <textarea id="content" name="content" class="form-control <?php echo ($edit_content && $edit_content['content_type'] == 'code') ? 'code-editor' : ''; ?>" style="min-height: 300px;" required><?php echo $edit_content ? htmlspecialchars($edit_content['content']) : ''; ?></textarea>
                        <div id="content-tips" class="form-tips">
                            <!-- 内容提示将通过JavaScript显示 -->
                        </div>
                    </div>
                    
                    <div class="form-buttons">
                        <button type="submit" name="save_content" class="admin-btn admin-btn-primary"><?php echo $edit_content ? '更新内容' : '添加内容'; ?></button>
                        <a href="course_content.php?course_id=<?php echo $course_id; ?>" class="admin-btn admin-btn-outline">取消</a>
                    </div>
                </form>
            </div>
            <?php endif; ?>
            
            <!-- 内容列表 -->
            <?php if (!isset($_GET['action']) && !isset($_GET['edit'])): ?>
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2 class="admin-card-title">课程内容列表</h2>
                    <div>
                        <a href="course_content.php?course_id=<?php echo $course_id; ?>&action=add" class="admin-btn admin-btn-outline"><i class="fas fa-plus"></i> 添加内容</a>
                        <span class="badge" style="background-color: rgba(62, 198, 255, 0.1); color: var(--blue); padding: 8px 12px; border-radius: 20px; font-size: 14px; margin-left: 10px;">
                            <i class="fas fa-info-circle"></i> 拖拽内容项可以调整顺序，更改后会自动保存
                        </span>
                    </div>
                </div>
                
                <div class="content-list" id="sortable-list">
                    <?php if (count($contents) > 0): ?>
                        <?php foreach ($contents as $content): ?>
                            <div class="content-item" data-id="<?php echo $content['id']; ?>">
                                <div class="content-handle">
                                    <i class="fas fa-grip-lines" style="font-size: 18px;"></i>
                                </div>
                                <div class="content-info">
                                    <div class="content-title"><?php echo htmlspecialchars($content['title']); ?></div>
                                    <div class="content-meta">
                                        <span class="content-type content-type-<?php echo $content['content_type']; ?>">
                                            <?php
                                            switch ($content['content_type']) {
                                                case 'text':
                                                    echo '<i class="fas fa-file-alt"></i> 文本内容';
                                                    break;
                                                case 'markdown':
                                                    echo '<i class="fab fa-markdown"></i> Markdown';
                                                    break;
                                                case 'code':
                                                    echo '<i class="fas fa-code"></i> 代码示例';
                                                    break;
                                                case 'quiz':
                                                    echo '<i class="fas fa-question-circle"></i> 测验题目';
                                                    break;
                                                case 'video':
                                                    echo '<i class="fas fa-video"></i> 视频链接';
                                                    break;
                                                default:
                                                    echo htmlspecialchars($content['content_type']);
                                            }
                                            ?>
                                        </span>
                                        <span><i class="far fa-clock"></i> 最后更新: <?php echo date('Y-m-d H:i', strtotime($content['updated_at'])); ?></span>
                                    </div>
                                </div>
                                <div class="content-actions">
                                    <a href="course_content.php?course_id=<?php echo $course_id; ?>&edit=<?php echo $content['id']; ?>" class="admin-btn admin-btn-secondary" style="padding: 5px 10px; font-size: 12px; margin-right: 5px;">
                                        <i class="fas fa-edit"></i> 编辑
                                    </a>
                                    <a href="course_content.php?course_id=<?php echo $course_id; ?>&delete=<?php echo $content['id']; ?>" class="admin-btn" style="padding: 5px 10px; font-size: 12px; background: linear-gradient(to right, var(--admin-danger), #FF6B6B); color: white;" onclick="return confirm('确定要删除此内容吗？如果有学生已学习此内容，相关学习记录也将被删除！')">
                                        <i class="fas fa-trash-alt"></i> 删除
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-content">
                            <p><i class="fas fa-info-circle" style="font-size: 40px; margin-bottom: 10px; color: var(--blue);"></i></p>
                            <p>此课程还没有添加任何内容。点击"添加内容"按钮开始创建！</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // 内容类型提示更新
        const contentTypeSelect = document.getElementById('content_type');
        const contentTips = document.getElementById('content-tips');
        const contentTextarea = document.getElementById('content');
        
        // 只有在表单显示时才初始化内容类型提示
        if (contentTypeSelect && contentTips && contentTextarea) {
            function updateContentTips() {
                const contentType = contentTypeSelect.value;
                let tipText = '';
                let iconClass = '';
                
                switch (contentType) {
                    case 'text':
                        iconClass = 'fas fa-file-alt';
                        tipText = '纯文本内容，支持基本HTML标签，用于课程说明和讲解。';
                        contentTextarea.classList.remove('code-editor');
                        break;
                    case 'markdown':
                        iconClass = 'fab fa-markdown';
                        tipText = '使用Markdown格式编写内容，将自动转换为格式化文本。';
                        contentTextarea.classList.remove('code-editor');
                        break;
                    case 'code':
                        iconClass = 'fas fa-code';
                        tipText = '代码示例，将以代码格式显示，支持语法高亮。可使用```语言名称来标记代码块。';
                        contentTextarea.classList.add('code-editor');
                        break;
                    case 'quiz':
                        iconClass = 'fas fa-question-circle';
                        tipText = '使用JSON格式定义测验题目，格式: {"questions":[{"type":"单选/多选/判断/填空","question":"题目","options":["选项1","选项2"],"answer":"正确答案","explanation":"解释"}]}';
                        contentTextarea.classList.add('code-editor');
                        break;
                    case 'video':
                        iconClass = 'fas fa-video';
                        tipText = '输入视频链接(支持优酷、哔哩哔哩等)或嵌入代码。如需自定义显示，可以使用HTML格式。';
                        contentTextarea.classList.remove('code-editor');
                        break;
                }
                
                contentTips.innerHTML = `<i class="${iconClass}" style="margin-right: 5px;"></i> ${tipText}`;
            }
            
            contentTypeSelect.addEventListener('change', updateContentTips);
            updateContentTips(); // 初始化提示
        }
        
        // 拖拽排序功能
        const sortableList = document.getElementById('sortable-list');
        
        // 只有当列表元素存在且已引入Sortable库时才初始化拖拽功能
        if (typeof Sortable !== 'undefined' && sortableList) {
            Sortable.create(sortableList, {
                handle: '.content-handle',
                animation: 150,
                onEnd: function(evt) {
                    const items = Array.from(sortableList.querySelectorAll('.content-item'));
                    const contentIds = items.map(item => item.getAttribute('data-id'));
                    
                    // 获取课程ID
                    const urlParams = new URLSearchParams(window.location.search);
                    const courseId = urlParams.get('course_id');
                    
                    // 发送AJAX请求保存新顺序
                    const formData = new FormData();
                    formData.append('action', 'reorder');
                    contentIds.forEach(id => {
                        formData.append('content_order[]', id);
                    });
                    
                    fetch(`course_content.php?course_id=${courseId}`, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // 成功处理
                            const messageDiv = document.createElement('div');
                            messageDiv.className = 'message success';
                            messageDiv.innerHTML = `<i class="fas fa-check-circle"></i> ${data.message}`;
                            
                            const contentDiv = document.querySelector('.admin-content');
                            const adminCard = document.querySelector('.admin-card');
                            if (contentDiv && adminCard) {
                                contentDiv.insertBefore(messageDiv, adminCard);
                                
                                // 3秒后移除消息
                                setTimeout(() => {
                                    messageDiv.remove();
                                }, 3000);
                            }
                        } else {
                            alert('保存顺序失败: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('保存顺序时发生错误');
                    });
                }
            });
        } else if (!window.Sortable && sortableList) {
            console.warn('Sortable.js 未加载，拖拽排序功能不可用');
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
            mobileHeader.appendChild(document.createElement('div')).innerHTML = '<h1>课程内容管理</h1>';
            
            adminContent.insertBefore(mobileHeader, adminContent.firstChild);
        }
    });
    </script>
    
    <!-- 加载Sortable.js库用于拖拽排序 -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.14.0/Sortable.min.js"></script>
</body>
</html> 