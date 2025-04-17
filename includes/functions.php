<?php
/**
 * 工具函数文件
 */

/**
 * 安全过滤用户输入
 * @param string $input 需要过滤的输入
 * @return string 过滤后的结果
 */
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * 生成密码哈希
 * @param string $password 原始密码
 * @return string 哈希后的密码
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

/**
 * 验证密码
 * @param string $password 原始密码
 * @param string $hash 哈希密码
 * @return bool 是否匹配
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * 生成随机字符串
 * @param int $length 长度
 * @return string 随机字符串
 */
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}

/**
 * 重定向函数
 * @param string $url 目标URL
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

/**
 * 检查用户是否已登录
 * @return bool 是否已登录
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * 检查用户角色
 * @param string $role 需要检查的角色
 * @return bool 是否匹配
 */
function hasRole($role) {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
}

/**
 * 显示提示消息
 * @param string $message 消息内容
 * @param string $type 消息类型 (success/error/info)
 * @return string HTML消息框
 */
function showAlert($message, $type = 'info') {
    $typeClass = '';
    switch($type) {
        case 'success':
            $typeClass = 'success';
            break;
        case 'error':
            $typeClass = 'error';
            break;
        default:
            $typeClass = 'info';
    }
    
    return "<div class='alert alert-{$typeClass}'>{$message}</div>";
}

/**
 * 获取当前页面URL
 * @return string 当前URL
 */
function getCurrentUrl() {
    return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
}

/**
 * 获取当前用户信息
 * @return array|null 用户信息或null
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    require_once 'db.php';
    $db = Database::getInstance();
    $stmt = $db->prepare("SELECT * FROM accounts WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

/**
 * 日期格式化
 * @param string $date 日期字符串
 * @param string $format 格式
 * @return string 格式化后的日期
 */
function formatDate($date, $format = 'Y-m-d') {
    return date($format, strtotime($date));
}

/**
 * 记录系统日志
 * @param string $action 操作
 * @param string $details 详情
 */
function logActivity($action, $details = '') {
    $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
    $ip = $_SERVER['REMOTE_ADDR'];
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    
    require_once 'db.php';
    $db = Database::getInstance();
    $stmt = $db->prepare("INSERT INTO system_logs (user_id, action, details, ip, user_agent) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$userId, $action, $details, $ip, $userAgent]);
}

/**
 * 获取系统设置值
 * @param string $key 设置键名
 * @param mixed $default 默认值
 * @return mixed 设置值或默认值
 */
function getSystemSetting($key, $default = null) {
    static $settings = null;
    
    // 如果设置还未加载，则从数据库加载
    if ($settings === null) {
        try {
            require_once 'db.php';
            $db = Database::getInstance();
            
            // 检查表是否存在
            try {
                $stmt = $db->query("SELECT setting_key, setting_value FROM system_settings");
                $settings = [];
                
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $settings[$row['setting_key']] = $row['setting_value'];
                }
            } catch (PDOException $e) {
                // 表不存在或其他错误，使用默认设置
                $settings = [];
                error_log("从数据库获取系统设置失败: " . $e->getMessage());
            }
        } catch (Exception $e) {
            $settings = [];
            error_log("数据库连接错误: " . $e->getMessage());
        }
    }
    
    // 如果设置存在，返回设置值；否则返回默认值或常量值
    if (isset($settings[$key])) {
        return $settings[$key];
    }
    
    // 检查是否有同名常量
    $constantName = strtoupper($key);
    if (defined($constantName)) {
        return constant($constantName);
    }
    
    return $default;
}

/**
 * 获取网站名称
 * @return string 网站名称
 */
function getSiteName() {
    return getSystemSetting('site_name', SITE_NAME);
}

/**
 * 获取网站描述
 * @return string 网站描述
 */
function getSiteDescription() {
    return getSystemSetting('site_description', SITE_DESCRIPTION);
}

/**
 * 获取网站URL
 * @return string 网站URL
 */
function getSiteUrl() {
    return getSystemSetting('site_url', SITE_URL);
}

/**
 * 获取网站图标
 * @return string 网站图标URL
 */
function getSiteIcon() {
    return getSystemSetting('site_icon', '/assets/images/favicon.png');
} 