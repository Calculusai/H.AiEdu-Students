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

// 处理用户操作
$message = '';
$error = '';

// 处理删除用户
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $delete_id = (int)$_GET['delete'];
    
    // 不允许删除自己
    if ($delete_id == $_SESSION['user_id']) {
        $error = "您不能删除自己的账户！";
    } else {
        $stmt = $db->prepare("DELETE FROM accounts WHERE id = ?");
        if ($stmt->execute([$delete_id])) {
            $message = "用户已成功删除！";
        } else {
            $error = "删除用户时出错！";
        }
    }
}

// 处理添加/更新用户
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_user'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $role = $_POST['role'];
    $edit_id = isset($_POST['edit_id']) ? (int)$_POST['edit_id'] : 0;
    
    // 验证输入
    if (empty($username) || empty($email) || (empty($password) && $edit_id == 0) || empty($role)) {
        $error = "所有字段都是必填的！";
    } else {
        // 检查用户名和邮箱是否已存在
        $stmt = $db->prepare("SELECT * FROM accounts WHERE (username = ? OR email = ?) AND id != ?");
        $stmt->execute([$username, $email, $edit_id]);
        if ($stmt->rowCount() > 0) {
            $error = "用户名或邮箱已存在！";
        } else {
            if ($edit_id > 0) {
                // 更新现有用户
                if (!empty($password)) {
                    // 如果提供了新密码，则更新密码
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $db->prepare("UPDATE accounts SET username = ?, email = ?, password = ?, role = ? WHERE id = ?");
                    $result = $stmt->execute([$username, $email, $hashed_password, $role, $edit_id]);
                } else {
                    // 不更新密码
                    $stmt = $db->prepare("UPDATE accounts SET username = ?, email = ?, role = ? WHERE id = ?");
                    $result = $stmt->execute([$username, $email, $role, $edit_id]);
                }
                
                if ($result) {
                    $message = "用户信息已成功更新！";
                    // 更新成功后重定向到列表页面
                    header("Location: users.php?success=update");
                    exit();
                } else {
                    $error = "更新用户信息时出错！";
                }
            } else {
                // 添加新用户
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("INSERT INTO accounts (username, email, password, role, created_at) VALUES (?, ?, ?, ?, NOW())");
                if ($stmt->execute([$username, $email, $hashed_password, $role])) {
                    $message = "新用户已成功添加！";
                    // 添加成功后重定向到列表页面
                    header("Location: users.php?success=add");
                    exit();
                } else {
                    $error = "添加新用户时出错！";
                }
            }
        }
    }
}

// 处理成功消息
if (isset($_GET['success'])) {
    if ($_GET['success'] == 'add') {
        $message = "新用户已成功添加！";
    } elseif ($_GET['success'] == 'update') {
        $message = "用户信息已成功更新！";
    }
}

// 获取要编辑的用户信息
$edit_user = null;
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $db->prepare("SELECT * FROM accounts WHERE id = ?");
    $stmt->execute([$edit_id]);
    $edit_user = $stmt->fetch(PDO::FETCH_ASSOC);
}

// 获取所有用户列表，默认按ID排序
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'id';
$order = isset($_GET['order']) ? $_GET['order'] : 'asc';

// 验证排序参数
$allowed_sort = ['id', 'username', 'email', 'role', 'created_at'];
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
    $where_clause = " WHERE username LIKE ? OR email LIKE ? OR role LIKE ?";
    $search_param = "%$search%";
    $params = [$search_param, $search_param, $search_param];
}

// 分页
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// 获取总记录数
$count_sql = "SELECT COUNT(*) FROM accounts" . $where_clause;
$stmt = $db->prepare($count_sql);
if (!empty($params)) {
    $stmt->execute($params);
} else {
    $stmt->execute();
}
$total_records = $stmt->fetchColumn();
$total_pages = ceil($total_records / $per_page);

// 获取用户列表
$sql = "SELECT * FROM accounts" . $where_clause . " ORDER BY $sort_order LIMIT $offset, $per_page";
$stmt = $db->prepare($sql);
if (!empty($params)) {
    $stmt->execute($params);
} else {
    $stmt->execute();
}
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 页面标题
$page_title = "用户管理";

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
</head>
<body>
    <div class="admin-container">
        <?php include 'admin_sidebar.php'; ?>
        
        <div class="admin-content">
            <div class="admin-content-header">
                <h1>用户管理</h1>
                <div>
                    <a href="users.php" class="admin-btn admin-btn-primary"><i class="fas fa-users"></i> 所有用户</a>
                    <a href="users.php?action=add" class="admin-btn admin-btn-outline"><i class="fas fa-user-plus"></i> 添加用户</a>
                </div>
            </div>
            
            <?php if (!empty($message)): ?>
                <div class="message success"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <?php if (!empty($error)): ?>
                <div class="message error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <!-- 用户表单 -->
            <?php if (isset($_GET['action']) && $_GET['action'] == 'add' || isset($_GET['edit'])): ?>
            <div class="admin-form">
                <h2><?php echo $edit_user ? '编辑用户' : '添加新用户'; ?></h2>
                <form method="post" action="users.php">
                    <?php if ($edit_user): ?>
                        <input type="hidden" name="edit_id" value="<?php echo $edit_user['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="username">用户名</label>
                        <input type="text" id="username" name="username" class="form-control" value="<?php echo $edit_user ? htmlspecialchars($edit_user['username']) : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">电子邮箱</label>
                        <input type="email" id="email" name="email" class="form-control" value="<?php echo $edit_user ? htmlspecialchars($edit_user['email']) : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">密码 <?php echo $edit_user ? '(留空则保持不变)' : ''; ?></label>
                        <input type="password" id="password" name="password" class="form-control" <?php echo $edit_user ? '' : 'required'; ?>>
                    </div>
                    
                    <div class="form-group">
                        <label for="role">角色</label>
                        <select id="role" name="role" class="form-control" required>
                            <option value="student" <?php echo ($edit_user && $edit_user['role'] == 'student') ? 'selected' : ''; ?>>学生</option>
                            <option value="teacher" <?php echo ($edit_user && $edit_user['role'] == 'teacher') ? 'selected' : ''; ?>>教师</option>
                            <option value="admin" <?php echo ($edit_user && $edit_user['role'] == 'admin') ? 'selected' : ''; ?>>管理员</option>
                        </select>
                    </div>
                    
                    <div class="form-buttons">
                        <button type="submit" name="save_user" class="admin-btn admin-btn-primary"><?php echo $edit_user ? '更新用户' : '添加用户'; ?></button>
                        <a href="users.php" class="admin-btn admin-btn-outline">取消</a>
                    </div>
                </form>
            </div>
            <?php endif; ?>
            
            <!-- 用户搜索 -->
            <?php if (!isset($_GET['action']) && !isset($_GET['edit'])): ?>
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2 class="admin-card-title">用户列表</h2>
                    <form class="search-form" method="get" action="users.php" style="display: flex; gap: 10px; width: 300px;">
                        <input type="text" name="search" class="form-control" placeholder="搜索用户..." value="<?php echo htmlspecialchars($search); ?>" style="margin: 0;">
                        <button type="submit" class="admin-btn admin-btn-secondary" style="padding: 10px; margin: 0;"><i class="fas fa-search"></i></button>
                    </form>
                </div>
                
                <!-- 用户列表 -->
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>
                                    <a href="?sort=id&order=<?php echo $sort == 'id' && $order == 'asc' ? 'desc' : 'asc'; ?>&search=<?php echo urlencode($search); ?>" class="<?php echo $sort == 'id' ? $order : ''; ?>">
                                        ID <?php echo $sort == 'id' ? ($order == 'asc' ? '↑' : '↓') : ''; ?>
                                    </a>
                                </th>
                                <th>
                                    <a href="?sort=username&order=<?php echo $sort == 'username' && $order == 'asc' ? 'desc' : 'asc'; ?>&search=<?php echo urlencode($search); ?>" class="<?php echo $sort == 'username' ? $order : ''; ?>">
                                        用户名 <?php echo $sort == 'username' ? ($order == 'asc' ? '↑' : '↓') : ''; ?>
                                    </a>
                                </th>
                                <th>
                                    <a href="?sort=email&order=<?php echo $sort == 'email' && $order == 'asc' ? 'desc' : 'asc'; ?>&search=<?php echo urlencode($search); ?>" class="<?php echo $sort == 'email' ? $order : ''; ?>">
                                        电子邮箱 <?php echo $sort == 'email' ? ($order == 'asc' ? '↑' : '↓') : ''; ?>
                                    </a>
                                </th>
                                <th>
                                    <a href="?sort=role&order=<?php echo $sort == 'role' && $order == 'asc' ? 'desc' : 'asc'; ?>&search=<?php echo urlencode($search); ?>" class="<?php echo $sort == 'role' ? $order : ''; ?>">
                                        角色 <?php echo $sort == 'role' ? ($order == 'asc' ? '↑' : '↓') : ''; ?>
                                    </a>
                                </th>
                                <th>
                                    <a href="?sort=created_at&order=<?php echo $sort == 'created_at' && $order == 'asc' ? 'desc' : 'asc'; ?>&search=<?php echo urlencode($search); ?>" class="<?php echo $sort == 'created_at' ? $order : ''; ?>">
                                        注册时间 <?php echo $sort == 'created_at' ? ($order == 'asc' ? '↑' : '↓') : ''; ?>
                                    </a>
                                </th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($users) > 0): ?>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?php echo $user['id']; ?></td>
                                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td>
                                            <span class="user-role <?php echo $user['role']; ?>">
                                                <?php 
                                                    if ($user['role'] == 'admin') echo '管理员';
                                                    elseif ($user['role'] == 'teacher') echo '教师';
                                                    else echo '学生';
                                                ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('Y-m-d H:i', strtotime($user['created_at'])); ?></td>
                                        <td class="actions">
                                            <a href="?edit=<?php echo $user['id']; ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>&page=<?php echo $page; ?>" class="admin-btn admin-btn-secondary" style="padding: 5px 10px; font-size: 12px;">
                                                <i class="fas fa-edit"></i> 编辑
                                            </a>
                                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                <a href="?delete=<?php echo $user['id']; ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>&page=<?php echo $page; ?>" class="admin-btn" style="padding: 5px 10px; font-size: 12px; background: linear-gradient(to right, var(--admin-danger), #FF6B6B); color: white;" onclick="return confirm('确定要删除此用户吗？')">
                                                    <i class="fas fa-trash-alt"></i> 删除
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" style="text-align: center;">没有找到用户记录</td>
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
            mobileHeader.appendChild(document.createElement('div')).innerHTML = '<h1>用户管理</h1>';
            
            adminContent.insertBefore(mobileHeader, adminContent.firstChild);
        }
    });
    </script>
</body>
</html> 