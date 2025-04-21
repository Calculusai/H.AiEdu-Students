<?php
/**
 * 用户控制器
 */
class UserController {
    private $db;
    
    /**
     * 构造函数
     */
    public function __construct() {
        global $db;
        $this->db = $db;
    }
    
    /**
     * 处理用户登录
     */
    public function login() {
        // 检查是否已登录
        if (is_logged_in()) {
            redirect(site_url('admin/dashboard'));
        }
        
        // 检查是否是POST请求
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            require_once VIEW_PATH . '/login.php';
            return;
        }
        
        // 验证CSRF令牌
        if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
            $_SESSION['flash_message'] = '安全令牌验证失败，请重试。';
            $_SESSION['flash_type'] = 'danger';
            require_once VIEW_PATH . '/login.php';
            return;
        }
        
        // 获取并验证表单数据
        $username = isset($_POST['username']) ? sanitize_input($_POST['username']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        $remember = isset($_POST['remember']) && $_POST['remember'] == '1';
        
        if (empty($username) || empty($password)) {
            $_SESSION['flash_message'] = '请输入用户名和密码。';
            $_SESSION['flash_type'] = 'danger';
            require_once VIEW_PATH . '/login.php';
            return;
        }
        
        // 查询用户
        $user = $this->getUserByUsername($username);
        
        // 如果没找到，尝试使用学号作为用户名查找学生账户
        if (!$user) {
            $user = $this->getStudentUserByStudentId($username);
        }
        
        // 验证用户和密码
        if (!$user || !password_verify($password, $user['password'])) {
            $_SESSION['flash_message'] = '用户名或密码错误。';
            $_SESSION['flash_type'] = 'danger';
            require_once VIEW_PATH . '/login.php';
            return;
        }
        
        // 检查用户状态
        if ($user['status'] != 1) {
            $_SESSION['flash_message'] = '此账号已被禁用，请联系管理员。';
            $_SESSION['flash_type'] = 'danger';
            require_once VIEW_PATH . '/login.php';
            return;
        }
        
        // 设置会话
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['theme_preference'] = 'light'; // 始终使用浅色主题
        
        // 学生角色特殊处理
        if ($user['role'] === 'student') {
            // 获取学生信息
            $student = $this->getStudentByUserId($user['id']);
            if ($student) {
                $_SESSION['student_id'] = $student['id'];
                $_SESSION['student_name'] = $student['name'];
            }
        }
        
        // 记录登录时间和IP
        $this->recordLogin($user['id']);
        
        // 如果选择了"记住我"，设置持久会话
        if ($remember) {
            $this->createRememberMeToken($user['id']);
        }
        
        // 重定向到适当的页面
        if ($user['role'] == 'admin') {
            redirect(site_url('admin/dashboard'));
        } else if ($user['role'] == 'student') {
            // 学生角色重定向到学生个人资料页面
            redirect(site_url('student/' . $_SESSION['student_id']));
        } else {
            redirect(site_url());
        }
    }
    
    /**
     * 处理用户登出
     */
    public function logout() {
        // 清除会话数据
        session_unset();
        session_destroy();
        
        // 清除记住我Cookie
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/');
            
            // 从数据库中删除令牌
            if (SYSTEM_INSTALLED) {
                $token = sanitize_input($_COOKIE['remember_token']);
                $sql = "DELETE FROM " . TABLE_PREFIX . "sessions WHERE token = :token";
                $this->db->exec($sql, ['token' => $token]);
            }
        }
        
        // 重定向到登录页面
        $_SESSION['flash_message'] = '您已成功登出。';
        $_SESSION['flash_type'] = 'success';
        redirect(site_url('login'));
    }
    
    /**
     * 根据用户名获取用户
     *
     * @param string $username 用户名
     * @return array|bool 用户数据或false
     */
    private function getUserByUsername($username) {
        if (!SYSTEM_INSTALLED) {
            return false;
        }
        
        $sql = "SELECT * FROM " . TABLE_PREFIX . "users WHERE username = :username LIMIT 1";
        return $this->db->query($sql, ['username' => $username]);
    }
    
    /**
     * 记录用户登录
     *
     * @param int $userId 用户ID
     */
    private function recordLogin($userId) {
        if (!SYSTEM_INSTALLED) {
            return;
        }
        
        $ip = $_SERVER['REMOTE_ADDR'];
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        
        // 更新用户表的最后登录时间
        $sql = "UPDATE " . TABLE_PREFIX . "users SET last_login = NOW() WHERE id = :id";
        $this->db->exec($sql, ['id' => $userId]);
        
        // 尝试记录登录日志 - 如果表不存在会静默失败
        try {
            $sql = "INSERT INTO " . TABLE_PREFIX . "login_logs (user_id, login_status, ip_address, user_agent, login_time) 
                    VALUES (:user_id, 'success', :ip_address, :user_agent, NOW())";
            $this->db->exec($sql, [
                'user_id' => $userId,
                'ip_address' => $ip,
                'user_agent' => $userAgent
            ]);
        } catch (Exception $e) {
            // 表可能不存在，忽略错误
            error_log("登录日志记录失败: " . $e->getMessage());
        }
    }
    
    /**
     * 创建"记住我"令牌
     *
     * @param int $userId 用户ID
     */
    private function createRememberMeToken($userId) {
        if (!SYSTEM_INSTALLED) {
            return;
        }
        
        // 生成唯一令牌
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+30 days'));
        $ip = $_SERVER['REMOTE_ADDR'];
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        
        // 保存令牌到数据库
        $sql = "INSERT INTO " . TABLE_PREFIX . "sessions (user_id, token, ip_address, user_agent, expires_at) 
                VALUES (:user_id, :token, :ip_address, :user_agent, :expires_at)";
        $this->db->exec($sql, [
            'user_id' => $userId,
            'token' => $token,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'expires_at' => $expires
        ]);
        
        // 设置Cookie
        setcookie('remember_token', $token, strtotime($expires), '/', '', false, true);
    }
    
    /**
     * 检查表是否存在
     *
     * @param string $tableName 表名
     * @return bool 是否存在
     */
    private function tableExists($tableName) {
        // 使用简单直接的查询方式确保执行成功
        try {
            $stmt = $this->db->connection->prepare("SELECT 1 FROM information_schema.tables WHERE table_schema = ? AND table_name = ?");
            $dbName = DB_NAME;
            $tableName = str_replace(TABLE_PREFIX, '', $tableName);
            $stmt->execute([$dbName, $tableName]);
            return $stmt->fetchColumn() !== false;
        } catch (Exception $e) {
            error_log("检查表是否存在时出错: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 显示用户个人资料页
     */
    public function profile() {
        // 检查用户是否已登录
        if (!is_logged_in()) {
            redirect(site_url('login'));
            return;
        }
        
        // 设置页面变量
        $page_title = '个人资料';
        $active_page = 'profile';
        
        // 加载模态框样式
        $extra_css = '<link rel="stylesheet" href="' . asset_url('css/modals.css') . '">';
        
        $user_id = $_SESSION['user_id'];
        $user = $this->getUserById($user_id);
        
        if (!$user) {
            $_SESSION['flash_message'] = '无法获取用户信息。';
            $_SESSION['flash_type'] = 'danger';
            redirect(site_url());
            return;
        }
        
        // 如果是学生，获取学生详细信息
        $student = null;
        if ($user['role'] == 'student') {
            $sql = "SELECT * FROM " . TABLE_PREFIX . "students WHERE user_id = :user_id";
            $student = $this->db->query($sql, ['user_id' => $user_id]);
        }
        
        include_once VIEW_PATH . '/user_profile.php';
    }
    
    /**
     * 处理用户个人资料更新
     */
    public function updateProfile() {
        // 检查用户是否已登录
        if (!is_logged_in()) {
            redirect(site_url('login'));
            return;
        }
        
        // 验证CSRF令牌
        if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
            $_SESSION['flash_message'] = '安全令牌验证失败，请重试。';
            $_SESSION['flash_type'] = 'danger';
            redirect(site_url('profile'));
            return;
        }
        
        $user_id = $_SESSION['user_id'];
        
        // 获取当前用户信息
        $user = $this->getUserById($user_id);
        if (!$user) {
            $_SESSION['flash_message'] = '无法获取用户信息，请重试。';
            $_SESSION['flash_type'] = 'danger';
            redirect(site_url('profile'));
            return;
        }
        
        // 获取并验证表单数据
        $email = isset($_POST['email']) ? sanitize_input($_POST['email']) : '';
        $current_password = isset($_POST['current_password']) ? $_POST['current_password'] : '';
        $new_password = isset($_POST['new_password']) ? $_POST['new_password'] : '';
        $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
        
        // 始终使用浅色主题
        $theme_preference = 'light';
        
        try {
            // 准备基本更新数据
            $updateSQL = "UPDATE " . TABLE_PREFIX . "users SET theme_preference = :theme_preference";
            $params = [':theme_preference' => $theme_preference, ':user_id' => $user_id];
            
            // 如果提供了当前密码和新密码，则更新密码
            if (!empty($current_password) && !empty($new_password)) {
                // 验证当前密码
                if (!password_verify($current_password, $user['password'])) {
                    $_SESSION['flash_message'] = '当前密码不正确。';
                    $_SESSION['flash_type'] = 'danger';
                    redirect(site_url('profile'));
                    return;
                }
                
                // 验证新密码
                if ($new_password !== $confirm_password) {
                    $_SESSION['flash_message'] = '两次输入的新密码不一致。';
                    $_SESSION['flash_type'] = 'danger';
                    redirect(site_url('profile'));
                    return;
                }
                
                if (strlen($new_password) < 6) {
                    $_SESSION['flash_message'] = '新密码长度必须至少为6个字符。';
                    $_SESSION['flash_type'] = 'danger';
                    redirect(site_url('profile'));
                    return;
                }
                
                // 追加密码更新字段
                $updateSQL .= ", password = :password";
                $params[':password'] = password_hash($new_password, PASSWORD_DEFAULT);
                
                // 追加password_change字段，如果用户表中有这个字段
                if (isset($user['require_password_change'])) {
                    $updateSQL .= ", require_password_change = 0";
                }
            }
            
            // 始终尝试更新email字段 - 即使用户之前没有email值
            $updateSQL .= ", email = :email";
            $params[':email'] = $email;
            
            // 完成SQL语句
            $updateSQL .= " WHERE id = :user_id";
            
            // 记录SQL和参数，方便调试
            error_log("执行SQL: " . $updateSQL);
            error_log("参数: " . json_encode($params));
            
            // 执行更新
            $result = $this->db->exec($updateSQL, $params);
            
            if ($result) {
                // 更新会话中的主题偏好
                $_SESSION['theme_preference'] = $theme_preference;
                
                $_SESSION['flash_message'] = '个人资料已成功更新。';
                $_SESSION['flash_type'] = 'success';
            } else {
                // 记录错误信息
                $errorInfo = $this->db->getErrorInfo();
                error_log("数据库更新失败: " . json_encode($errorInfo));
                
                $_SESSION['flash_message'] = '更新个人资料失败，请重试。';
                $_SESSION['flash_type'] = 'danger';
            }
        } catch (Exception $e) {
            // 捕获并记录异常
            error_log('个人资料更新失败: ' . $e->getMessage());
            error_log('异常堆栈: ' . $e->getTraceAsString());
            
            $_SESSION['flash_message'] = '更新个人资料时发生错误，请稍后重试。';
            $_SESSION['flash_type'] = 'danger';
        }
        
        // 重定向到个人资料页面
        redirect(site_url('profile'));
    }
    
    /**
     * 根据ID获取用户
     *
     * @param int $userId 用户ID
     * @return array|bool 用户数据或false
     */
    private function getUserById($userId) {
        if (!SYSTEM_INSTALLED) {
            return false;
        }
        
        $sql = "SELECT * FROM " . TABLE_PREFIX . "users WHERE id = :id LIMIT 1";
        return $this->db->query($sql, ['id' => $userId]);
    }
    
    /**
     * 根据学号获取学生用户
     *
     * @param string $studentId 学号
     * @return array|bool 用户数据或false
     */
    private function getStudentUserByStudentId($studentId) {
        if (!SYSTEM_INSTALLED) {
            return false;
        }
        
        $sql = "SELECT u.* FROM " . TABLE_PREFIX . "users u 
                JOIN " . TABLE_PREFIX . "students s ON u.id = s.user_id 
                WHERE s.student_id = :student_id LIMIT 1";
        return $this->db->query($sql, ['student_id' => $studentId]);
    }
    
    /**
     * 根据用户ID获取学生信息
     *
     * @param int $userId 用户ID
     * @return array|bool 学生数据或false
     */
    private function getStudentByUserId($userId) {
        if (!SYSTEM_INSTALLED) {
            return false;
        }
        
        $sql = "SELECT * FROM " . TABLE_PREFIX . "students WHERE user_id = :user_id LIMIT 1";
        return $this->db->query($sql, ['user_id' => $userId]);
    }
} 