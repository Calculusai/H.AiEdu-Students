<?php
/**
 * 少儿编程成就展示系统 - 辅助函数
 */

/**
 * 获取站点URL
 * 
 * @param string $path 路径
 * @return string URL
 */
function site_url($path = '') {
    $site_url = SITE_URL ?: (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
    return $site_url . ($path ? '/' . ltrim($path, '/') : '');
}

/**
 * 获取资源URL
 * 
 * @param string $path 资源路径
 * @return string 资源URL
 */
function asset_url($path = '') {
    return site_url('assets/' . ltrim($path, '/'));
}

/**
 * 重定向
 * 
 * @param string $url 目标URL
 * @return void
 */
function redirect($url) {
    header('Location: ' . $url);
    exit;
}

/**
 * 输出JSON并退出
 * 
 * @param mixed $data 数据
 * @param int $code HTTP状态码
 * @return void
 */
function json_response($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * 过滤输入数据
 * 
 * @param string $data 输入数据
 * @return string 过滤后的数据
 */
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * 检查用户是否已登录
 * 
 * @return bool 是否已登录
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * 检查用户是否是管理员
 * 
 * @return bool 是否是管理员
 */
function is_admin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * 获取当前主题
 * 
 * @return string 主题名称
 */
function get_current_theme() {
    return 'light'; // 始终返回浅色主题
}

/**
 * 生成CSRF令牌
 * 
 * @return string CSRF令牌
 */
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * 获取CSRF令牌
 * 作为generate_csrf_token的别名
 * 
 * @return string CSRF令牌
 */
function get_csrf_token() {
    return generate_csrf_token();
}

/**
 * 验证CSRF令牌
 * 
 * @param string $token 令牌
 * @return bool 是否有效
 */
function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * 加密密码
 * 
 * @param string $password 密码
 * @return string 加密后的密码
 */
function password_hash_custom($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

/**
 * 验证密码
 * 
 * @param string $password 密码
 * @param string $hash 加密后的密码
 * @return bool 是否匹配
 */
function password_verify_custom($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * 显示错误消息
 * 
 * @param string $message 错误消息
 * @return string HTML
 */
function show_error($message) {
    return '<div class="alert alert-danger">' . $message . '</div>';
}

/**
 * 显示成功消息
 * 
 * @param string $message 成功消息
 * @return string HTML
 */
function show_success($message) {
    return '<div class="alert alert-success">' . $message . '</div>';
}

/**
 * 获取分页HTML
 * 
 * @param int $total 总记录数
 * @param int $per_page 每页显示数量
 * @param int $current_page 当前页
 * @param string $url_pattern URL模式
 * @return string 分页HTML
 */
function get_pagination($total, $per_page, $current_page, $url_pattern) {
    $total_pages = ceil($total / $per_page);
    
    if ($total_pages <= 1) {
        return '';
    }
    
    $html = '<nav aria-label="分页导航"><ul class="pagination">';
    
    // 上一页
    if ($current_page > 1) {
        $html .= '<li class="page-item"><a class="page-link" href="' . sprintf($url_pattern, $current_page - 1) . '">&laquo; 上一页</a></li>';
    } else {
        $html .= '<li class="page-item disabled"><span class="page-link">&laquo; 上一页</span></li>';
    }
    
    // 页码
    $start = max(1, $current_page - 2);
    $end = min($total_pages, $current_page + 2);
    
    if ($start > 1) {
        $html .= '<li class="page-item"><a class="page-link" href="' . sprintf($url_pattern, 1) . '">1</a></li>';
        if ($start > 2) {
            $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
    }
    
    for ($i = $start; $i <= $end; $i++) {
        if ($i == $current_page) {
            $html .= '<li class="page-item active"><span class="page-link">' . $i . '</span></li>';
        } else {
            $html .= '<li class="page-item"><a class="page-link" href="' . sprintf($url_pattern, $i) . '">' . $i . '</a></li>';
        }
    }
    
    if ($end < $total_pages) {
        if ($end < $total_pages - 1) {
            $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
        $html .= '<li class="page-item"><a class="page-link" href="' . sprintf($url_pattern, $total_pages) . '">' . $total_pages . '</a></li>';
    }
    
    // 下一页
    if ($current_page < $total_pages) {
        $html .= '<li class="page-item"><a class="page-link" href="' . sprintf($url_pattern, $current_page + 1) . '">下一页 &raquo;</a></li>';
    } else {
        $html .= '<li class="page-item disabled"><span class="page-link">下一页 &raquo;</span></li>';
    }
    
    $html .= '</ul></nav>';
    
    return $html;
}

/**
 * 获取允许的上传文件MIME类型
 * 
 * @return array 允许的MIME类型数组
 */
function get_allowed_mime_types() {
    return [
        // 图片
        'image/jpeg', 
        'image/png', 
        'image/gif', 
        
        // 文档
        'application/pdf', 
        'application/msword', // .doc
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // .docx
        'application/vnd.ms-excel', // .xls
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // .xlsx
        'application/vnd.ms-powerpoint', // .ppt
        'application/vnd.openxmlformats-officedocument.presentationml.presentation', // .pptx
        
        // 文本
        'text/plain'
    ];
}

/**
 * 获取文件MIME类型
 * 
 * @param string $file 文件路径
 * @return string MIME类型
 */
function get_mime_type($file) {
    // 尝试使用fileinfo扩展
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file);
        finfo_close($finfo);
        return $mime;
    }
    
    // 尝试使用mime_content_type函数
    if (function_exists('mime_content_type')) {
        return mime_content_type($file);
    }
    
    // 如果前两种方法都不可用，基于扩展名猜测
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    
    $mime_types = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'ppt' => 'application/vnd.ms-powerpoint',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'txt' => 'text/plain'
    ];
    
    return isset($mime_types[$ext]) ? $mime_types[$ext] : 'application/octet-stream';
}

// 确保上传目录存在
function ensure_upload_dir($dirPath = '') {
    $path = UPLOAD_PATH;
    
    if (!empty($dirPath)) {
        $path .= '/' . trim($dirPath, '/');
    }
    
    if (!is_dir($path)) {
        return mkdir($path, 0755, true);
    }
    
    return true;
}

// 检查文件类型是否允许上传
function is_allowed_file_type($filename, $allowedTypes = []) {
    if (empty($allowedTypes)) {
        // 默认允许的文件类型
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt'];
    }
    
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return in_array($extension, $allowedTypes);
}

// 获取安全的文件名
function get_safe_filename($filename) {
    // 移除特殊字符
    $filename = preg_replace('/[^\w\.-]/u', '_', $filename);
    
    // 确保文件名不超过100个字符
    if (mb_strlen($filename) > 100) {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $basename = pathinfo($filename, PATHINFO_FILENAME);
        $basename = mb_substr($basename, 0, 100 - mb_strlen($extension) - 1);
        $filename = $basename . '.' . $extension;
    }
    
    return $filename;
}

// 上传文件
function upload_file($file, $dirPath = '', $allowedTypes = []) {
    // 检查文件是否有效
    if (!isset($file) || $file['error'] != 0) {
        return false;
    }
    
    // 检查文件类型
    if (!is_allowed_file_type($file['name'], $allowedTypes)) {
        return false;
    }
    
    // 验证MIME类型
    $actualMimeType = get_mime_type($file['tmp_name']);
    $allowedMimes = get_allowed_mime_types();
    if (!in_array($actualMimeType, $allowedMimes)) {
        return false;
    }
    
    // 确保上传目录存在
    if (!ensure_upload_dir($dirPath)) {
        return false;
    }
    
    // 生成唯一文件名
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $originalName = pathinfo($file['name'], PATHINFO_FILENAME);
    $safeOriginalName = get_safe_filename($originalName);
    $newFilename = $safeOriginalName . '_' . date('YmdHis') . '_' . uniqid() . '.' . $extension;
    
    $uploadDir = UPLOAD_PATH;
    if (!empty($dirPath)) {
        $uploadDir .= '/' . trim($dirPath, '/');
    }
    
    $targetPath = $uploadDir . '/' . $newFilename;
    
    // 移动上传的文件
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return (!empty($dirPath) ? $dirPath . '/' : '') . $newFilename;
    }
    
    return false;
} 