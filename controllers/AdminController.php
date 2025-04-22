<?php
/**
 * 管理员控制器
 */
class AdminController {
    private $db;
    private $itemsPerPage = 20;
    
    /**
     * 构造函数
     */
    public function __construct() {
        global $db;
        $this->db = $db;
        
        // 检查用户是否已登录且是管理员
        if (!is_logged_in() || !is_admin()) {
            redirect(site_url('login'));
        }
    }
    
    /**
     * 管理员首页
     */
    public function index() {
        // 重定向到仪表盘
        redirect(site_url('admin/dashboard'));
    }
    
    /**
     * 显示管理员仪表盘
     */
    public function dashboard() {
        $page_title = '管理员控制台';
        $active_page = 'admin';
        
        // 获取统计数据
        $stats = [
            'students' => $this->getCount(TABLE_PREFIX . 'students'),
            'achievements' => $this->getCount(TABLE_PREFIX . 'achievements'),
            'achievement_types' => $this->getCount(TABLE_PREFIX . 'achievements', 'COUNT(DISTINCT achievement_type)'),
            'newest_achievements' => $this->getNewestAchievements(5)
        ];
        
        include_once VIEW_PATH . '/admin/dashboard.php';
    }
    
    /**
     * 显示添加学生页面
     */
    public function addStudentForm() {
        $page_title = '添加学生';
        $active_page = 'admin_students';
        
        // 获取已有班级列表，用于自动完成
        $sql = "SELECT DISTINCT class_name FROM " . TABLE_PREFIX . "students WHERE class_name != '' ORDER BY class_name";
        $classes = $this->db->queryAll($sql);
        $classes = array_column($classes, 'class_name');
        
        // 表单数据（用于重新填充表单）
        $form_data = [];
        if (isset($_SESSION['form_data'])) {
            $form_data = $_SESSION['form_data'];
            unset($_SESSION['form_data']);
        }
        
        // 错误和成功消息
        $error_message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : null;
        $success_message = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : null;
        
        // 清除会话消息
        unset($_SESSION['error_message']);
        unset($_SESSION['success_message']);
        
        include_once VIEW_PATH . '/admin/add_student.php';
    }
    
    /**
     * 处理添加学生表单提交
     */
    public function addStudent() {
        // 验证CSRF令牌
        if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
            $_SESSION['error_message'] = '安全验证失败，请重试。';
            redirect(site_url('admin/add_student'));
            return;
        }
        
        // 验证必填字段
        $required_fields = ['student_id', 'name'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                $_SESSION['error_message'] = '请填写所有必填字段。';
                $_SESSION['form_data'] = $_POST;
                redirect(site_url('admin/add_student'));
                return;
            }
        }
        
        // 保存表单数据以便在出错时恢复
        $_SESSION['form_data'] = $_POST;
        
        $student_id = sanitize_input($_POST['student_id']);
        $name = sanitize_input($_POST['name']);
        $class_name = sanitize_input($_POST['class_name'] ?? '');
        $contact = sanitize_input($_POST['contact'] ?? '');
        $email = sanitize_input($_POST['email'] ?? '');
        $notes = sanitize_input($_POST['notes'] ?? '');
        
        // 验证学号是否已存在
        $sql = "SELECT COUNT(*) as count FROM " . TABLE_PREFIX . "students WHERE student_id = :student_id";
        $result = $this->db->query($sql, ['student_id' => $student_id]);
        
        if ($result && $result['count'] > 0) {
            $_SESSION['error_message'] = '学号已存在，请使用其他学号。';
            redirect(site_url('admin/add_student'));
            return;
        }
        
        // 验证邮箱是否已存在（如果提供了邮箱）
        if (!empty($email)) {
            $sql = "SELECT COUNT(*) as count FROM " . TABLE_PREFIX . "users WHERE email = :email";
            $result = $this->db->query($sql, ['email' => $email]);
            
            if ($result && $result['count'] > 0) {
                $_SESSION['error_message'] = '邮箱已被使用，请使用其他邮箱。';
                redirect(site_url('admin/add_student'));
                return;
            }
        }
        
        // 准备学生数据
        $studentData = [
            'student_id' => $student_id,
            'name' => $name,
            'class_name' => $class_name,
            'contact' => $contact,
            'notes' => $notes
        ];
        
        // 准备用户数据
        // 确定密码
        if (isset($_POST['generate_password']) && $_POST['generate_password'] == 1) {
            // 自动生成8位随机密码
            $password = $this->generateRandomPassword(8);
        } else if (!empty($_POST['password'])) {
            // 使用提供的密码
            $password = $_POST['password'];
        } else {
            // 使用学号作为默认密码
            $password = $student_id;
        }
        
        $userData = [
            'username' => $student_id,
            'password' => $password,
            'email' => $email,
            'role' => 'student',
            'status' => 1, // 改为整数1，而不是字符串'active'
            'require_password_change' => isset($_POST['require_password_change']) ? 1 : 0
        ];
        
        // 记录调试信息
        error_log("添加学生 - 学生数据: " . json_encode($studentData));
        error_log("添加学生 - 用户数据: " . json_encode($userData));
        
        // 引入Student模型
        require_once MODEL_PATH . '/Student.php';
        $studentModel = new Student();
        
        // 创建学生账号
        $result = $studentModel->createStudentWithUser($studentData, $userData);
        
        if ($result) {
            // 清除表单数据
            unset($_SESSION['form_data']);
            
            $_SESSION['success_message'] = '学生添加成功。' . 
                                          (isset($_POST['generate_password']) && $_POST['generate_password'] == 1 ? 
                                          '生成的密码为：' . $password : '');
            
            redirect(site_url('admin/students'));
        } else {
            // 获取错误信息
            error_log("添加学生失败 - 模型返回: " . $result);
            
            $_SESSION['error_message'] = '学生添加失败，请重试。';
            redirect(site_url('admin/add_student'));
        }
    }
    
    /**
     * 导入学生数据（从CSV文件）
     */
    public function importStudents() {
        // 调试信息
        error_log("importStudents方法被调用");
        error_log("POST数据: " . json_encode($_POST));
        error_log("FILES数据: " . json_encode($_FILES));
        
        // 验证CSRF令牌
        if (!isset($_POST['csrf_token'])) {
            error_log("CSRF令牌不存在");
            $_SESSION['error_message'] = '安全令牌验证失败，请重试。';
            redirect(site_url('admin/add_student'));
            return;
        }
        
        error_log("提交的CSRF令牌: " . $_POST['csrf_token']);
        error_log("会话中的CSRF令牌: " . ($_SESSION['csrf_token'] ?? 'undefined'));
        
        if (!verify_csrf_token($_POST['csrf_token'])) {
            error_log("CSRF令牌验证失败");
            $_SESSION['error_message'] = '安全令牌验证失败，请重试。';
            redirect(site_url('admin/add_student'));
            return;
        }
        
        // 检查是否上传了文件
        if (!isset($_FILES['import_file']) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['error_message'] = '请选择CSV文件进行导入。';
            redirect(site_url('admin/add_student'));
            return;
        }
        
        // 检查文件类型
        $file_info = pathinfo($_FILES['import_file']['name']);
        $extension = strtolower($file_info['extension']);
        
        if ($extension !== 'csv') {
            $_SESSION['error_message'] = '只支持CSV文件格式（.csv）。';
            redirect(site_url('admin/add_student'));
            return;
        }
        
        // 是否自动生成密码
        $generate_password = isset($_POST['generate_password']) && $_POST['generate_password'] == 1;
        
        try {
            // 读取CSV文件内容
            $csv_content = file_get_contents($_FILES['import_file']['tmp_name']);
            
            // 检测编码并转换为UTF-8
            $encoding = mb_detect_encoding($csv_content, ['ASCII', 'UTF-8', 'GB2312', 'GBK', 'BIG5'], true);
            if ($encoding !== 'UTF-8') {
                error_log("CSV文件编码非UTF-8，检测到的编码为: " . $encoding);
                $csv_content = mb_convert_encoding($csv_content, 'UTF-8', $encoding);
                // 将转换后的内容写回临时文件
                file_put_contents($_FILES['import_file']['tmp_name'], $csv_content);
            }
            
            // 打开CSV文件
            $file = fopen($_FILES['import_file']['tmp_name'], 'r');
            if (!$file) {
                throw new Exception('无法打开CSV文件');
            }
            
            // 读取表头
            $headers = fgetcsv($file);
            if (!$headers) {
                throw new Exception('CSV文件格式错误或为空');
            }
            
            // 转换表头为小写
            $headers = array_map('trim', array_map('strtolower', $headers));
            
            // 检查必要的列
            if (!in_array('student_id', $headers) || !in_array('name', $headers)) {
                $_SESSION['error_message'] = 'CSV文件必须包含学号(student_id)和姓名(name)列。';
                redirect(site_url('admin/add_student'));
                return;
            }
            
            // 映射列索引
            $column_map = [];
            foreach ($headers as $index => $header) {
                $column_map[$header] = $index;
            }
            
            // 引入Student模型
            require_once MODEL_PATH . '/Student.php';
            $studentModel = new Student();
            
            // 记录导入结果
            $success_count = 0;
            $error_count = 0;
            $error_rows = [];
            $row_number = 1; // 从第二行开始，因为第一行是表头
            
            // 开始导入数据
            while (($row = fgetcsv($file)) !== false) {
                $row_number++;
                
                // 跳过空行
                if (empty($row[$column_map['student_id']]) || empty($row[$column_map['name']])) {
                    continue;
                }
                
                // 提取学生数据
                $student_id = trim($row[$column_map['student_id']]);
                $name = trim($row[$column_map['name']]);
                
                // 确保学生姓名是UTF-8编码
                if (!mb_check_encoding($name, 'UTF-8')) {
                    $name = mb_convert_encoding($name, 'UTF-8', 'auto');
                }
                
                error_log("导入学生: 学号={$student_id}, 姓名={$name}, 编码=" . mb_detect_encoding($name));
                
                // 准备学生数据
                $studentData = [
                    'student_id' => $student_id,
                    'name' => $name,
                    'class_name' => isset($column_map['class_name']) && isset($row[$column_map['class_name']]) ? trim($row[$column_map['class_name']]) : '',
                    'contact' => isset($column_map['contact']) && isset($row[$column_map['contact']]) ? trim($row[$column_map['contact']]) : '',
                    'notes' => isset($column_map['notes']) && isset($row[$column_map['notes']]) ? trim($row[$column_map['notes']]) : ''
                ];
                
                // 准备用户数据
                $email = isset($column_map['email']) && isset($row[$column_map['email']]) ? trim($row[$column_map['email']]) : '';
                
                // 设置密码
                if (isset($column_map['password']) && isset($row[$column_map['password']]) && !empty($row[$column_map['password']])) {
                    $password = trim($row[$column_map['password']]);
                } else if ($generate_password) {
                    // 自动生成8位随机密码
                    $password = $this->generateRandomPassword(8);
                } else {
                    // 使用学号作为默认密码
                    $password = $student_id;
                }
                
                $userData = [
                    'username' => $student_id,
                    'password' => $password,
                    'email' => $email,
                    'role' => 'student',
                    'status' => 1, // 改为整数1，而不是字符串'active'
                    'require_password_change' => 1
                ];
                
                // 尝试创建学生账号
                try {
                    // 检查学号是否已存在
                    $sql = "SELECT COUNT(*) as count FROM " . TABLE_PREFIX . "students WHERE student_id = :student_id";
                    $result = $this->db->query($sql, ['student_id' => $student_id]);
                    
                    if ($result && $result['count'] > 0) {
                        $error_rows[] = [
                            'row' => $row_number,
                            'student_id' => $student_id,
                            'name' => $name,
                            'error' => '学号已存在'
                        ];
                        $error_count++;
                        continue;
                    }
                    
                    // 检查邮箱是否已存在（如果提供了邮箱）
                    if (!empty($email)) {
                        $sql = "SELECT COUNT(*) as count FROM " . TABLE_PREFIX . "users WHERE email = :email";
                        $result = $this->db->query($sql, ['email' => $email]);
                        
                        if ($result && $result['count'] > 0) {
                            $error_rows[] = [
                                'row' => $row_number,
                                'student_id' => $student_id,
                                'name' => $name,
                                'error' => '邮箱已被使用'
                            ];
                            $error_count++;
                            continue;
                        }
                    }
                    
                    // 创建学生账号
                    $result = $studentModel->createStudentWithUser($studentData, $userData);
                    
                    if ($result) {
                        $success_count++;
                    } else {
                        $error_rows[] = [
                            'row' => $row_number,
                            'student_id' => $student_id,
                            'name' => $name,
                            'error' => '创建失败'
                        ];
                        $error_count++;
                    }
                } catch (Exception $e) {
                    $error_rows[] = [
                        'row' => $row_number,
                        'student_id' => $student_id,
                        'name' => $name,
                        'error' => $e->getMessage()
                    ];
                    $error_count++;
                }
            }
            
            // 关闭CSV文件
            fclose($file);
            
            // 设置导入结果消息
            if ($success_count > 0) {
                $_SESSION['success_message'] = "成功导入 {$success_count} 名学生。";
            }
            
            if ($error_count > 0) {
                $_SESSION['error_message'] = "导入过程中有 {$error_count} 条记录失败。";
                $_SESSION['import_errors'] = $error_rows;
            }
            
        } catch (Exception $e) {
            $_SESSION['error_message'] = '导入过程中发生错误：' . $e->getMessage();
        }
        
        redirect(site_url('admin/students'));
    }
    
    /**
     * 生成随机密码
     * 
     * @param int $length 密码长度
     * @return string 随机密码
     */
    private function generateRandomPassword($length = 8) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $password = '';
        
        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[rand(0, strlen($chars) - 1)];
        }
        
        return $password;
    }
    
    /**
     * 下载学生导入模板
     */
    public function downloadTemplate() {
        // 设置CSV文件信息
        $filename = '学生导入模板.csv';
        $headers = ['学号(student_id)', '姓名(name)', '班级(class_name)', '联系方式(contact)', '邮箱(email)', '备注(notes)', '密码(password)'];
        
        // 示例数据
        $example_data = [
            '20210001',
            '张三',
            '计算机科学1班',
            '13800138000',
            'zhangsan@example.com',
            '班长',
            'password123'
        ];
        
        // 设置响应头
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // 创建输出流
        $output = fopen('php://output', 'w');
        
        // 添加UTF-8 BOM，解决Excel打开CSV中文乱码问题
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // 写入表头
        fputcsv($output, $headers);
        
        // 写入示例数据
        fputcsv($output, $example_data);
        
        // 关闭输出流
        fclose($output);
        exit;
    }
    
    /**
     * 显示学生列表
     */
    public function listStudents() {
        $page_title = '学生管理';
        $active_page = 'admin_students';
        
        // 加载模态框样式
        $extra_css = '<link rel="stylesheet" href="' . asset_url('css/modals.css') . '">';
        
        // 获取当前页码
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        
        // 获取搜索条件 - 用于安全显示
        $search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
        $filter_class = isset($_GET['class']) ? sanitize_input($_GET['class']) : '';
        $filter_grade = isset($_GET['grade']) ? sanitize_input($_GET['grade']) : '';
        $filter_status = isset($_GET['status']) !== false ? sanitize_input($_GET['status']) : '';
        
        // 初始化提示消息
        $search_message = '';
        
        // 对搜索输入进行处理 - 用于数据库查询
        $search_raw = isset($_GET['search']) ? trim(urldecode($_GET['search'])) : '';
        $filter_class_raw = isset($_GET['class']) ? trim(urldecode($_GET['class'])) : '';
        $filter_grade_raw = isset($_GET['grade']) ? trim(urldecode($_GET['grade'])) : '';
        
        // 记录搜索条件 - 用于调试
        error_log("原始搜索词: " . (isset($_GET['search']) ? $_GET['search'] : '空'));
        error_log("解码后搜索词: " . $search_raw);
        
        // 构建查询
        $sql = "SELECT s.*, u.username, u.email, u.last_login, u.status, COUNT(DISTINCT a.id) as achievement_count 
                FROM " . TABLE_PREFIX . "students s
                LEFT JOIN " . TABLE_PREFIX . "users u ON s.user_id = u.id
                LEFT JOIN " . TABLE_PREFIX . "achievements a ON s.id = a.student_id";
        
        $params = [];
        $whereConditions = [];
        
        // 添加搜索条件
        if (!empty($search_raw)) {
            $whereConditions[] = "(s.name LIKE ? OR s.student_id LIKE ? OR s.class_name LIKE ?)";
            $params[] = "%{$search_raw}%";
            $params[] = "%{$search_raw}%";
            $params[] = "%{$search_raw}%";
            $search_message = "搜索：\"" . htmlspecialchars($search) . "\"";
        }
        
        // 添加班级筛选
        if (!empty($filter_class_raw)) {
            $whereConditions[] = "s.class_name = ?";
            $params[] = $filter_class_raw;
            $search_message .= (!empty($search_message) ? "，" : "") . "班级：" . htmlspecialchars($filter_class);
        }
        
        // 添加年级筛选
        if (!empty($filter_grade_raw)) {
            $whereConditions[] = "s.grade = ?";
            $params[] = $filter_grade_raw;
            $search_message .= (!empty($search_message) ? "，" : "") . "年级：" . htmlspecialchars($filter_grade);
        }
        
        // 添加账号状态筛选
        if ($filter_status !== '') {
            $whereConditions[] = "u.status = ?";
            $params[] = (int)$filter_status;
            $search_message .= (!empty($search_message) ? "，" : "") . "状态：" . ((int)$filter_status == 1 ? "启用" : "禁用");
        }
        
        // 构建WHERE子句部分
        $whereClause = '';
        if (!empty($whereConditions)) {
            $whereClause = " WHERE " . implode(' AND ', $whereConditions);
        }
        
        // 获取总记录数
        $countSql = "SELECT COUNT(*) as total FROM (
                    SELECT DISTINCT s.id 
                    FROM " . TABLE_PREFIX . "students s
                    LEFT JOIN " . TABLE_PREFIX . "users u ON s.user_id = u.id"
                    . $whereClause . ") as subquery";
        
        // 记录计数SQL查询
        error_log("学生计数SQL查询: " . $countSql);
        error_log("参数数量: " . count($params));
        
        try {
            $totalResult = $this->db->query($countSql, $params);
            $total = $totalResult ? $totalResult['total'] : 0;
        } catch (Exception $e) {
            error_log("计数查询错误: " . $e->getMessage());
            $total = 0;
        }
        
        // 添加主查询的WHERE子句
        $sql .= $whereClause;
        
        // 添加分组和排序
        $sql .= " GROUP BY s.id ORDER BY s.name";
        
        // 计算总页数
        $totalPages = ceil($total / $this->itemsPerPage);
        $page = min($page, max(1, $totalPages));
        
        // 添加分页
        $offset = ($page - 1) * $this->itemsPerPage;
        $sql .= " LIMIT {$offset}, {$this->itemsPerPage}";
        
        // 记录主SQL查询
        error_log("学生列表SQL查询: " . $sql);
        
        // 执行查询
        try {
            $students = $this->db->queryAll($sql, $params);
            error_log("获取到的学生数量: " . count($students));
        } catch (Exception $e) {
            error_log("列表查询错误: " . $e->getMessage());
            $students = [];
        }
        
        // 设置搜索条件的提示消息
        if (!empty($search_message)) {
            $_SESSION['search_message'] = $search_message;
        } else if (isset($_SESSION['search_message'])) {
            unset($_SESSION['search_message']);
        }
        
        // 获取班级列表（用于筛选表单）
        $sql = "SELECT DISTINCT class_name FROM " . TABLE_PREFIX . "students WHERE class_name IS NOT NULL AND class_name != '' ORDER BY class_name";
        $classes = array_column($this->db->queryAll($sql), 'class_name');
        
        // 获取年级列表（用于筛选表单）
        $sql = "SELECT DISTINCT grade FROM " . TABLE_PREFIX . "students WHERE grade IS NOT NULL AND grade != '' ORDER BY grade";
        $grades = array_column($this->db->queryAll($sql), 'grade');
        
        // 生成分页HTML
        $paginationParams = [];
        if (!empty($search)) $paginationParams[] = 'search=' . urlencode($search);
        if (!empty($filter_class)) $paginationParams[] = 'class=' . urlencode($filter_class);
        if (!empty($filter_grade)) $paginationParams[] = 'grade=' . urlencode($filter_grade);
        if ($filter_status !== '') $paginationParams[] = 'status=' . $filter_status;
        
        $paginationUrl = '?page=%d';
        if (!empty($paginationParams)) {
            $paginationUrl .= '&' . implode('&', $paginationParams);
        }
        
        $pagination = get_pagination($total, $this->itemsPerPage, $page, $paginationUrl);
        
        include_once VIEW_PATH . '/admin/students.php';
    }
    
    /**
     * 显示成就列表
     */
    public function listAchievements() {
        $page_title = '成就管理';
        $active_page = 'admin_achievements';
        
        // 加载模态框样式
        $extra_css = '<link rel="stylesheet" href="' . asset_url('css/modals.css') . '">';
        
        // 获取当前页码
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        
        // 获取筛选条件 - 用于安全显示
        $type = isset($_GET['type']) ? sanitize_input($_GET['type']) : '';
        $search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
        $student_id = isset($_GET['student_id']) ? (int)$_GET['student_id'] : 0;
        
        // 对搜索输入进行处理 - 用于数据库查询
        $search_raw = isset($_GET['search']) ? trim(urldecode($_GET['search'])) : '';
        $type_raw = isset($_GET['type']) ? trim(urldecode($_GET['type'])) : '';
        
        // 获取成就类型列表
        $types = $this->getAchievementTypes();
        
        // 获取学生列表（用于筛选）
        $students = $this->getStudents();
        
        // 构建查询
        $sql = "SELECT a.*, s.name as student_name 
                FROM " . TABLE_PREFIX . "achievements a 
                JOIN " . TABLE_PREFIX . "students s ON a.student_id = s.id";
        
        $params = [];
        $whereConditions = [];
        
        // 添加筛选条件
        if (!empty($type_raw)) {
            $whereConditions[] = "a.achievement_type = ?";
            $params[] = $type_raw;
        }
        
        if (!empty($search_raw)) {
            $whereConditions[] = "(a.title LIKE ? OR a.description LIKE ? OR s.name LIKE ?)";
            $params[] = "%{$search_raw}%";
            $params[] = "%{$search_raw}%";
            $params[] = "%{$search_raw}%";
        }
        
        if ($student_id > 0) {
            $whereConditions[] = "a.student_id = ?";
            $params[] = $student_id;
        }
        
        // 组合WHERE子句
        $whereClause = '';
        if (!empty($whereConditions)) {
            $whereClause = " WHERE " . implode(' AND ', $whereConditions);
        }
        
        // 添加排序
        $orderClause = " ORDER BY a.achieved_date DESC";
        
        // 获取总记录数
        $countSql = "SELECT COUNT(*) as total FROM " . TABLE_PREFIX . "achievements a 
                    JOIN " . TABLE_PREFIX . "students s ON a.student_id = s.id" 
                    . $whereClause;
        
        try {
            $totalResult = $this->db->query($countSql, $params);
            $total = $totalResult ? $totalResult['total'] : 0;
        } catch (Exception $e) {
            error_log("成就计数查询错误: " . $e->getMessage());
            $total = 0;
        }
        
        // 计算总页数
        $totalPages = ceil($total / $this->itemsPerPage);
        $page = min($page, max(1, $totalPages));
        
        // 添加完整SQL
        $sql .= $whereClause . $orderClause;
        
        // 添加分页
        $offset = ($page - 1) * $this->itemsPerPage;
        $sql .= " LIMIT {$offset}, {$this->itemsPerPage}";
        
        // 执行查询
        try {
            $achievements = $this->db->queryAll($sql, $params);
        } catch (Exception $e) {
            error_log("成就列表查询错误: " . $e->getMessage());
            $achievements = [];
        }
        
        // 生成分页HTML
        $paginationURL = '?page=%d' . 
                        (!empty($type) ? '&type=' . urlencode($type) : '') . 
                        (!empty($search) ? '&search=' . urlencode($search) : '') . 
                        ($student_id > 0 ? '&student_id=' . $student_id : '');
        
        $pagination = get_pagination($total, $this->itemsPerPage, $page, $paginationURL);
        
        include_once VIEW_PATH . '/admin/achievements.php';
    }
    
    /**
     * 显示添加成就页面
     */
    public function showAddAchievement() {
        $page_title = '添加成就';
        $active_page = 'admin_achievements';
        
        // 获取学生列表
        $students = $this->getStudents();
        
        // 获取成就类型列表
        $types = $this->getAchievementTypes();
        
        include_once VIEW_PATH . '/admin/achievement_form.php';
    }
    
    /**
     * 添加成就
     */
    public function addAchievement() {
        // 验证CSRF令牌
        if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
            $_SESSION['flash_message'] = '安全令牌验证失败，请重试。';
            $_SESSION['flash_type'] = 'danger';
            redirect(site_url('admin/achievements/add'));
            return;
        }
        
        // 获取并验证表单数据
        $student_id = isset($_POST['student_id']) ? (int)$_POST['student_id'] : 0;
        $title = isset($_POST['title']) ? sanitize_input($_POST['title']) : '';
        $description = isset($_POST['description']) ? sanitize_input($_POST['description']) : '';
        $achievement_type = isset($_POST['achievement_type']) ? sanitize_input($_POST['achievement_type']) : '';
        $score = isset($_POST['score']) ? sanitize_input($_POST['score']) : '';
        $certificate_no = isset($_POST['certificate_no']) ? sanitize_input($_POST['certificate_no']) : '';
        $issue_authority = isset($_POST['issue_authority']) ? sanitize_input($_POST['issue_authority']) : '';
        $achieved_date = isset($_POST['achieved_date']) ? sanitize_input($_POST['achieved_date']) : '';
        
        // 验证必填字段
        if ($student_id <= 0 || empty($title) || empty($achievement_type) || empty($achieved_date)) {
            $_SESSION['flash_message'] = '请填写所有必填字段。';
            $_SESSION['flash_type'] = 'danger';
            redirect(site_url('admin/achievements/add'));
            return;
        }
        
        // 处理文件上传
        $attachment = '';
        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] == 0) {
            $attachment = $this->uploadFile($_FILES['attachment']);
            
            if ($attachment === false) {
                $_SESSION['flash_message'] = '文件上传失败，请重试。';
                $_SESSION['flash_type'] = 'danger';
                redirect(site_url('admin/achievements/add'));
                return;
            }
        }
        
        // 插入数据库
        $sql = "INSERT INTO " . TABLE_PREFIX . "achievements 
                (student_id, title, description, achievement_type, score, certificate_no, issue_authority, achieved_date, attachment, created_at) 
                VALUES (:student_id, :title, :description, :achievement_type, :score, :certificate_no, :issue_authority, :achieved_date, :attachment, NOW())";
        
        $params = [
            'student_id' => $student_id,
            'title' => $title,
            'description' => $description,
            'achievement_type' => $achievement_type,
            'score' => $score,
            'certificate_no' => $certificate_no,
            'issue_authority' => $issue_authority,
            'achieved_date' => $achieved_date,
            'attachment' => $attachment
        ];
        
        $result = $this->db->exec($sql, $params);
        
        if ($result) {
            $_SESSION['flash_message'] = '成就添加成功！';
            $_SESSION['flash_type'] = 'success';
            
            // 检查是否是从学生成就页面跳转来的
            if (isset($_GET['student_id']) && (int)$_GET['student_id'] > 0) {
                redirect(site_url('admin/students/achievements/' . $student_id));
            } else {
                redirect(site_url('admin/achievements'));
            }
        } else {
            $_SESSION['flash_message'] = '成就添加失败，请重试。';
            $_SESSION['flash_type'] = 'danger';
            redirect(site_url('admin/achievements/add'));
        }
    }
    
    /**
     * 显示编辑成就页面
     *
     * @param int $id 成就ID
     */
    public function showEditAchievement($id) {
        $id = (int)$id;
        
        // 获取成就信息
        $achievement = $this->getAchievement($id);
        
        if (!$achievement) {
            $_SESSION['flash_message'] = '成就不存在。';
            $_SESSION['flash_type'] = 'danger';
            redirect(site_url('admin/achievements'));
            return;
        }
        
        $page_title = '编辑成就';
        $active_page = 'admin_achievements';
        
        // 获取学生列表
        $students = $this->getStudents();
        
        // 获取成就类型列表
        $types = $this->getAchievementTypes();
        
        include_once VIEW_PATH . '/admin/achievement_form.php';
    }
    
    /**
     * 更新成就
     *
     * @param int $id 成就ID
     */
    public function updateAchievement($id) {
        $id = (int)$id;
        
        // 验证CSRF令牌
        if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
            $_SESSION['flash_message'] = '安全令牌验证失败，请重试。';
            $_SESSION['flash_type'] = 'danger';
            redirect(site_url('admin/achievements/edit/' . $id));
            return;
        }
        
        // 获取并验证表单数据
        $student_id = isset($_POST['student_id']) ? (int)$_POST['student_id'] : 0;
        $title = isset($_POST['title']) ? sanitize_input($_POST['title']) : '';
        $description = isset($_POST['description']) ? sanitize_input($_POST['description']) : '';
        $achievement_type = isset($_POST['achievement_type']) ? sanitize_input($_POST['achievement_type']) : '';
        $score = isset($_POST['score']) ? sanitize_input($_POST['score']) : '';
        $certificate_no = isset($_POST['certificate_no']) ? sanitize_input($_POST['certificate_no']) : '';
        $issue_authority = isset($_POST['issue_authority']) ? sanitize_input($_POST['issue_authority']) : '';
        $achieved_date = isset($_POST['achieved_date']) ? sanitize_input($_POST['achieved_date']) : '';
        
        // 验证必填字段
        if ($student_id <= 0 || empty($title) || empty($achievement_type) || empty($achieved_date)) {
            $_SESSION['flash_message'] = '请填写所有必填字段。';
            $_SESSION['flash_type'] = 'danger';
            redirect(site_url('admin/achievements/edit/' . $id));
            return;
        }
        
        // 获取当前成就信息
        $achievement = $this->getAchievement($id);
        
        if (!$achievement) {
            $_SESSION['flash_message'] = '成就不存在。';
            $_SESSION['flash_type'] = 'danger';
            redirect(site_url('admin/achievements'));
            return;
        }
        
        // 处理文件上传
        $attachment = $achievement['attachment'];
        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] == 0) {
            $newAttachment = $this->uploadFile($_FILES['attachment']);
            
            if ($newAttachment === false) {
                $_SESSION['flash_message'] = '文件上传失败，请重试。';
                $_SESSION['flash_type'] = 'danger';
                redirect(site_url('admin/achievements/edit/' . $id));
                return;
            }
            
            // 删除旧附件
            if (!empty($attachment) && file_exists(UPLOAD_PATH . '/' . $attachment)) {
                @unlink(UPLOAD_PATH . '/' . $attachment);
            }
            
            $attachment = $newAttachment;
        }
        
        // 更新数据库
        $sql = "UPDATE " . TABLE_PREFIX . "achievements SET 
                student_id = :student_id, 
                title = :title, 
                description = :description, 
                achievement_type = :achievement_type, 
                score = :score, 
                certificate_no = :certificate_no, 
                issue_authority = :issue_authority, 
                achieved_date = :achieved_date, 
                attachment = :attachment 
                WHERE id = :id";
        
        $params = [
            'student_id' => $student_id,
            'title' => $title,
            'description' => $description,
            'achievement_type' => $achievement_type,
            'score' => $score,
            'certificate_no' => $certificate_no,
            'issue_authority' => $issue_authority,
            'achieved_date' => $achieved_date,
            'attachment' => $attachment,
            'id' => $id
        ];
        
        $result = $this->db->exec($sql, $params);
        
        if ($result) {
            $_SESSION['flash_message'] = '成就更新成功！';
            $_SESSION['flash_type'] = 'success';
            redirect(site_url('admin/achievements'));
        } else {
            $_SESSION['flash_message'] = '成就更新失败，请重试。';
            $_SESSION['flash_type'] = 'danger';
            redirect(site_url('admin/achievements/edit/' . $id));
        }
    }
    
    /**
     * 删除成就
     *
     * @param int $id 成就ID
     */
    public function deleteAchievement($id) {
        $id = (int)$id;
        
        // 验证CSRF令牌
        if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
            $_SESSION['flash_message'] = '安全令牌验证失败，请重试。';
            $_SESSION['flash_type'] = 'danger';
            redirect(site_url('admin/achievements'));
            return;
        }
        
        // 获取成就信息
        $achievement = $this->getAchievement($id);
        
        if (!$achievement) {
            $_SESSION['flash_message'] = '成就不存在。';
            $_SESSION['flash_type'] = 'danger';
            redirect(site_url('admin/achievements'));
            return;
        }
        
        // 删除附件
        if (!empty($achievement['attachment']) && file_exists(UPLOAD_PATH . '/' . $achievement['attachment'])) {
            @unlink(UPLOAD_PATH . '/' . $achievement['attachment']);
        }
        
        // 从数据库中删除
        $sql = "DELETE FROM " . TABLE_PREFIX . "achievements WHERE id = :id";
        $result = $this->db->exec($sql, ['id' => $id]);
        
        if ($result) {
            $_SESSION['flash_message'] = '成就删除成功！';
            $_SESSION['flash_type'] = 'success';
        } else {
            $_SESSION['flash_message'] = '成就删除失败，请重试。';
            $_SESSION['flash_type'] = 'danger';
        }
        
        // 检查是否需要重定向回学生成就页面
        if (isset($_POST['redirect_to_student']) && (int)$_POST['redirect_to_student'] > 0) {
            redirect(site_url('admin/students/achievements/' . (int)$_POST['redirect_to_student']));
        } else {
            redirect(site_url('admin/achievements'));
        }
    }
    
    /**
     * 获取学生列表
     *
     * @return array 学生列表
     */
    private function getStudents() {
        $sql = "SELECT id, name FROM " . TABLE_PREFIX . "students ORDER BY name";
        return $this->db->queryAll($sql);
    }
    
    /**
     * 获取成就类型列表
     *
     * @return array 成就类型列表
     */
    private function getAchievementTypes() {
        $sql = "SELECT DISTINCT achievement_type FROM " . TABLE_PREFIX . "achievements ORDER BY achievement_type";
        $results = $this->db->queryAll($sql);
        
        return array_column($results, 'achievement_type');
    }
    
    /**
     * 获取成就信息
     *
     * @param int $id 成就ID
     * @return array|bool 成就信息或false
     */
    private function getAchievement($id) {
        $sql = "SELECT * FROM " . TABLE_PREFIX . "achievements WHERE id = :id";
        return $this->db->query($sql, ['id' => $id]);
    }
    
    /**
     * 获取表记录数
     *
     * @param string $table 表名
     * @param string $expr 统计表达式，默认为COUNT(*)
     * @param string $where WHERE条件语句
     * @return int 记录数
     */
    private function getCount($table, $expr = 'COUNT(*)', $where = '') {
        $sql = "SELECT {$expr} as total FROM {$table}";
        
        if (!empty($where)) {
            $sql .= " WHERE " . $where;
        }
        
        $result = $this->db->query($sql);
        
        return $result ? (int)$result['total'] : 0;
    }
    
    /**
     * 获取最新成就列表
     *
     * @param int $limit 数量限制
     * @return array 成就列表
     */
    private function getNewestAchievements($limit = 5) {
        $sql = "SELECT a.*, s.name as student_name 
                FROM " . TABLE_PREFIX . "achievements a 
                JOIN " . TABLE_PREFIX . "students s ON a.student_id = s.id 
                ORDER BY a.created_at DESC LIMIT " . (int)$limit;
        
        return $this->db->queryAll($sql);
    }
    
    /**
     * 上传文件
     *
     * @param array $file $_FILES数组中的文件项
     * @return string|bool 上传后的文件名或false
     */
    private function uploadFile($file) {
        // 使用辅助函数上传文件
        $filename = upload_file($file, 'certificates');
        if ($filename !== false) {
            return $filename;
        }
        
        // 如果辅助函数上传失败，尝试原始上传方法
        // 检查上传目录
        $uploadDir = UPLOAD_PATH . '/certificates';
        if (!is_dir($uploadDir) || !is_writable(UPLOAD_PATH)) {
            if (!mkdir($uploadDir, 0755, true)) {
                return false;
            }
        }
        
        // 检查文件类型
        $allowedTypes = get_allowed_mime_types();
        if (!in_array($file['type'], $allowedTypes)) {
            return false;
        }
        
        // 生成唯一文件名
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'certificate_' . date('YmdHis') . '_' . uniqid() . '.' . $extension;
        $destination = $uploadDir . '/' . $filename;
        
        // 保存文件
        if (move_uploaded_file($file['tmp_name'], $destination)) {
            return 'certificates/' . $filename;
        }
        
        return false;
    }
    
    /**
     * 显示系统设置页面
     */
    public function showSettings() {
        $page_title = '系统设置';
        $active_page = 'admin_settings';
        
        // 获取所有设置
        require_once MODEL_PATH . '/Setting.php';
        $settingModel = new Setting();
        $settings = $settingModel->getAllSettings();
        
        include_once VIEW_PATH . '/admin/settings.php';
    }
    
    /**
     * 保存系统设置
     */
    public function saveSettings() {
        // 验证CSRF令牌
        if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
            $_SESSION['flash_message'] = '安全令牌验证失败，请重试。';
            $_SESSION['flash_type'] = 'danger';
            redirect(site_url('admin/settings'));
            return;
        }
        
        // 获取设置组
        $group = isset($_POST['setting_group']) ? sanitize_input($_POST['setting_group']) : 'general';
        
        // 从POST数据中提取设置
        $settings = [];
        foreach ($_POST as $key => $value) {
            if ($key != 'csrf_token' && $key != 'setting_group' && $key != 'is_security_setting') {
                // 处理数组值（如密码策略）
                if (is_array($value)) {
                    $settings[$key] = $value;
                    // 对数组中的每个元素进行sanitize_input处理
                    foreach ($settings[$key] as $subKey => $subValue) {
                        $settings[$key][$subKey] = sanitize_input($subValue);
                    }
                } 
                // 复选框特殊处理
                else if (strpos($key, 'enable_') === 0 || strpos($key, 'allow_') === 0) {
                    $settings[$key] = isset($_POST[$key]) ? 1 : 0;
                } 
                // 普通字符串值
                else {
                    $settings[$key] = sanitize_input($value);
                }
            }
        }
        
        // 保存设置
        require_once MODEL_PATH . '/Setting.php';
        $settingModel = new Setting();
        $result = $settingModel->setMultiple($settings, $group);
        
        if ($result) {
            $_SESSION['flash_message'] = '设置已成功保存！';
            $_SESSION['flash_type'] = 'success';
            $_SESSION['settings_saved'] = true;
            
            // 记录当前设置的标签页到session，用于前端自动刷新
            $_SESSION['tab'] = $group;
            
            // 安全设置特殊处理
            if ($group === 'security' || isset($_POST['is_security_setting'])) {
                // 如果是安全设置，更新config.php中的安全相关常量
                if (isset($settings['login_attempts'])) {
                    // 可以在这里同步更新config.php中的安全相关常量
                    // $this->updateConfigConstant('LOGIN_ATTEMPTS', $settings['login_attempts']);
                }
                
                // 记录安全设置已更新，前端将使用此标记来触发页面刷新
                $_SESSION['security_settings_updated'] = true;
            }
            
            // 如果修改了网站名称，同步更新config.php中的常量
            if ($group === 'general' && isset($settings['site_name'])) {
                $this->updateConfigConstant('SITE_NAME', $settings['site_name']);
            }
            
            // 如果更新了其他关键设置，也可以在这里同步到config.php
            if ($group === 'general' && isset($settings['site_description'])) {
                // 将来可以添加其他全局配置同步
            }
        } else {
            $_SESSION['flash_message'] = '保存设置失败，请重试。';
            $_SESSION['flash_type'] = 'danger';
        }
        
        // 重定向回设置页面，并保持当前标签页
        redirect(site_url('admin/settings?tab=' . $group));
    }
    
    /**
     * 更新config.php文件中的常量值
     *
     * @param string $constant 常量名
     * @param string $value 新值
     * @return bool 是否成功
     */
    private function updateConfigConstant($constant, $value) {
        try {
            $configFile = BASE_PATH . '/config.php';
            
            // 确保文件存在且可写
            if (!file_exists($configFile) || !is_writable($configFile)) {
                error_log("配置文件不存在或不可写: {$configFile}");
                return false;
            }
            
            // 读取当前配置文件内容
            $content = file_get_contents($configFile);
            
            // 转义值中的特殊字符（如引号）
            $escapedValue = str_replace("'", "\'", $value);
            
            // 使用正则表达式替换常量值
            $pattern = "/define\s*\(\s*['\"]" . preg_quote($constant, '/') . "['\"][\s,]*['\"].*['\"]\s*\)/";
            $replacement = "define('{$constant}', '{$escapedValue}')";
            
            // 替换并写回文件
            $newContent = preg_replace($pattern, $replacement, $content);
            
            if ($newContent !== $content) {
                file_put_contents($configFile, $newContent);
                return true;
            }
            
            error_log("没有找到要替换的常量: {$constant}");
            return false;
        } catch (Exception $e) {
            error_log("更新配置常量时出错: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 重置系统设置
     */
    public function resetSettings() {
        // 验证CSRF令牌
        if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
            $_SESSION['flash_message'] = '安全令牌验证失败，请重试。';
            $_SESSION['flash_type'] = 'danger';
            redirect(site_url('admin/settings'));
            return;
        }
        
        // 重置设置
        require_once MODEL_PATH . '/Setting.php';
        $settingModel = new Setting();
        $result = $settingModel->resetToDefaults();
        
        if ($result) {
            $_SESSION['flash_message'] = '所有设置已重置为默认值。';
            $_SESSION['flash_type'] = 'success';
        } else {
            $_SESSION['flash_message'] = '重置设置失败，请重试。';
            $_SESSION['flash_type'] = 'danger';
        }
        
        redirect(site_url('admin/settings'));
    }
    
    /**
     * 显示数据统计页面
     */
    public function showStatistics() {
        $page_title = '数据统计';
        $active_page = 'admin_statistics';
        
        // 基础统计数据
        $stats = [
            'students' => $this->getCount(TABLE_PREFIX . 'students'),
            'achievements' => $this->getCount(TABLE_PREFIX . 'achievements'),
            'achievement_types' => $this->getCount(TABLE_PREFIX . 'achievements', 'COUNT(DISTINCT achievement_type)')
        ];
        
        // 计算平均每个学生的成就数
        $stats['avg_achievements_per_student'] = $stats['students'] > 0 
            ? number_format($stats['achievements'] / $stats['students'], 1) 
            : 0;
        
        // 获取成就类型分布
        $sql = "SELECT achievement_type, COUNT(*) as count 
                FROM " . TABLE_PREFIX . "achievements 
                GROUP BY achievement_type 
                ORDER BY count DESC";
        $typeResults = $this->db->queryAll($sql);
        
        $stats['achievement_by_type'] = [];
        foreach ($typeResults as $row) {
            $stats['achievement_by_type'][$row['achievement_type']] = (int)$row['count'];
        }
        
        // 获取月度成就趋势（最近12个月）
        $sql = "SELECT DATE_FORMAT(achieved_date, '%Y-%m') as month, COUNT(*) as count 
                FROM " . TABLE_PREFIX . "achievements 
                WHERE achieved_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH) 
                GROUP BY month 
                ORDER BY month";
        $monthlyResults = $this->db->queryAll($sql);
        
        $stats['achievements_by_month'] = [];
        foreach ($monthlyResults as $row) {
            $stats['achievements_by_month'][$row['month']] = (int)$row['count'];
        }
        
        // 获取成就最多的前10名学生
        $sql = "SELECT s.id, s.name, s.class_name, COUNT(a.id) as achievement_count 
                FROM " . TABLE_PREFIX . "students s 
                LEFT JOIN " . TABLE_PREFIX . "achievements a ON s.id = a.student_id 
                GROUP BY s.id 
                ORDER BY achievement_count DESC 
                LIMIT 10";
        $stats['top_students'] = $this->db->queryAll($sql);
        
        // 计算最大成就数，用于进度条显示
        $stats['max_achievements'] = !empty($stats['top_students']) ? $stats['top_students'][0]['achievement_count'] : 0;
        
        // 获取最新的10条成就记录
        $sql = "SELECT a.*, s.name as student_name 
                FROM " . TABLE_PREFIX . "achievements a 
                JOIN " . TABLE_PREFIX . "students s ON a.student_id = s.id 
                ORDER BY a.created_at DESC 
                LIMIT 10";
        $stats['recent_achievements'] = $this->db->queryAll($sql);
        
        include_once VIEW_PATH . '/admin/statistics.php';
    }
    
    /**
     * 显示编辑学生表单
     *
     * @param int $id 学生ID
     */
    public function editStudentForm($id) {
        $id = (int)$id;
        
        // 获取学生信息
        require_once MODEL_PATH . '/Student.php';
        $studentModel = new Student();
        $student = $studentModel->getStudentWithInfo($id);
        
        if (!$student) {
            $_SESSION['error_message'] = '找不到指定的学生。';
            redirect(site_url('admin/students'));
            return;
        }
        
        // 设置页面标题
        $page_title = '编辑学生';
        $active_page = 'admin_students';
        
        // 获取班级列表（用于自动完成）
        $sql = "SELECT DISTINCT class_name FROM " . TABLE_PREFIX . "students WHERE class_name IS NOT NULL AND class_name != '' ORDER BY class_name";
        $classes = array_column($this->db->queryAll($sql), 'class_name');
        
        // 获取学生统计信息
        $stats = [];
        $stats['achievements_count'] = $this->getCount(TABLE_PREFIX . 'achievements', 'COUNT(*)', "student_id = " . $id);
        $sql = "SELECT SUM(score) as total_points FROM " . TABLE_PREFIX . "achievements WHERE student_id = :student_id AND score IS NOT NULL";
        $result = $this->db->query($sql, ['student_id' => $id]);
        $stats['total_points'] = $result ? (float)$result['total_points'] : 0;
        
        // 获取学生成就列表（用于显示在侧边栏）
        $student_achievements = [];
        if ($stats['achievements_count'] > 0) {
            $sql = "SELECT a.*, 'fas fa-trophy' as icon, DATE_FORMAT(a.achieved_date, '%Y-%m-%d') as achieved_at 
                    FROM " . TABLE_PREFIX . "achievements a 
                    WHERE a.student_id = :student_id 
                    ORDER BY a.achieved_date DESC 
                    LIMIT 5";
            $student_achievements = $this->db->queryAll($sql, ['student_id' => $id]);
        }
        
        // 获取学生最近活动记录
        $recent_activities = [];
        
        include_once VIEW_PATH . '/admin/edit_student.php';
    }
    
    /**
     * 处理编辑学生表单提交
     *
     * @param int $id 学生ID
     */
    public function editStudent($id) {
        $id = (int)$id;
        
        // 验证CSRF令牌
        if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
            $_SESSION['error_message'] = '安全令牌验证失败，请重试。';
            redirect(site_url('admin/students/edit/' . $id));
            return;
        }
        
        // 验证必填字段
        if (empty($_POST['name'])) {
            $_SESSION['error_message'] = '请填写姓名。';
            redirect(site_url('admin/students/edit/' . $id));
            return;
        }
        
        // 引入Student模型
        require_once MODEL_PATH . '/Student.php';
        $studentModel = new Student();
        
        // 获取学生当前信息
        $student = $studentModel->getStudentWithInfo($id);
        
        if (!$student) {
            $_SESSION['error_message'] = '找不到指定的学生。';
            redirect(site_url('admin/students'));
            return;
        }
        
        // 检查邮箱是否已被使用
        $email = isset($_POST['email']) ? sanitize_input($_POST['email']) : '';
        $original_email = isset($_POST['original_email']) ? sanitize_input($_POST['original_email']) : '';
        
        if (!empty($email) && $email !== $original_email) {
            $sql = "SELECT COUNT(*) as count FROM " . TABLE_PREFIX . "users WHERE email = :email AND id != :user_id";
            $result = $this->db->query($sql, ['email' => $email, 'user_id' => $student['user_id']]);
            
            if ($result && $result['count'] > 0) {
                $_SESSION['error_message'] = '邮箱已被其他用户使用，请更换。';
                redirect(site_url('admin/students/edit/' . $id));
                return;
            }
        }
        
        // 准备学生数据
        $studentData = [
            'name' => sanitize_input($_POST['name']),
            'class_name' => isset($_POST['class_name']) ? sanitize_input($_POST['class_name']) : '',
            'contact' => isset($_POST['contact']) ? sanitize_input($_POST['contact']) : '',
            'notes' => isset($_POST['notes']) ? sanitize_input($_POST['notes']) : ''
        ];
        
        // 准备用户数据
        $userData = [
            'email' => $email,
            'status' => isset($_POST['active']) && $_POST['active'] == 1 ? 1 : 0
        ];
        
        // 处理密码更改
        $password = isset($_POST['new_password']) ? sanitize_input($_POST['new_password']) : '';
        if (!empty($password)) {
            $userData['password'] = password_hash($password, PASSWORD_DEFAULT);
            
            // 如果勾选了"要求下次登录修改密码"，则设置标志
            if (isset($_POST['require_password_change']) && $_POST['require_password_change'] == 1) {
                $userData['require_password_change'] = 1;
            }
        }
        
        // 即使未更改密码，也处理require_password_change状态
        if (isset($_POST['require_password_change']) && $_POST['require_password_change'] == 1) {
            $userData['require_password_change'] = 1;
        } else {
            $userData['require_password_change'] = 0;
        }
        
        // 更新学生信息
        $result = $studentModel->updateStudentWithUser($id, $studentData, $userData);
        
        if ($result) {
            $_SESSION['success_message'] = '学生信息更新成功！';
            redirect(site_url('admin/students'));
        } else {
            $_SESSION['error_message'] = '学生信息更新失败，请重试。';
            redirect(site_url('admin/students/edit/' . $id));
        }
    }

    /**
     * 显示学生成就列表
     * 
     * @param int $id 学生ID
     */
    public function studentAchievements($id) {
        $id = (int)$id;
        
        // 获取学生信息
        require_once MODEL_PATH . '/Student.php';
        $studentModel = new Student();
        $student = $studentModel->getStudentWithInfo($id);
        
        if (!$student) {
            $_SESSION['error_message'] = '找不到指定的学生。';
            redirect(site_url('admin/students'));
            return;
        }
        
        // 设置页面标题
        $page_title = $student['name'] . ' 的成就管理';
        $active_page = 'admin_students';
        
        // 加载模态框样式
        $extra_css = '<link rel="stylesheet" href="' . asset_url('css/modals.css') . '">';
        
        // 获取当前页码
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        
        // 获取筛选条件
        $type = isset($_GET['type']) ? sanitize_input($_GET['type']) : '';
        $search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
        
        // 获取成就类型列表
        $types = $this->getAchievementTypes();
        
        // 构建查询
        $sql = "SELECT a.* 
                FROM " . TABLE_PREFIX . "achievements a 
                WHERE a.student_id = :student_id";
        
        $params = ['student_id' => $id];
        
        // 添加筛选条件
        if (!empty($type)) {
            $sql .= " AND a.achievement_type = :type";
            $params['type'] = $type;
        }
        
        if (!empty($search)) {
            $sql .= " AND (a.title LIKE :search OR a.description LIKE :search)";
            $params['search'] = "%{$search}%";
        }
        
        // 添加排序
        $sql .= " ORDER BY a.achieved_date DESC";
        
        // 获取总记录数
        $countSql = str_replace("a.*", "COUNT(*) as total", $sql);
        $totalResult = $this->db->query($countSql, $params);
        $total = $totalResult ? (int)$totalResult['total'] : 0;
        
        // 计算总页数
        $totalPages = ceil($total / $this->itemsPerPage);
        $page = min($page, max(1, $totalPages));
        
        // 添加分页
        $offset = ($page - 1) * $this->itemsPerPage;
        $sql .= " LIMIT {$offset}, {$this->itemsPerPage}";
        
        // 执行查询
        $achievements = $this->db->queryAll($sql, $params);
        
        // 生成分页HTML
        $paginationURL = '?page=%d' . 
                        (!empty($type) ? '&type=' . urlencode($type) : '') . 
                        (!empty($search) ? '&search=' . urlencode($search) : '');
        
        $pagination = get_pagination($total, $this->itemsPerPage, $page, $paginationURL);
        
        // 加载视图
        include_once VIEW_PATH . '/admin/student_achievements.php';
    }

    /**
     * 显示学生详情页面
     *
     * @param int $id 学生ID
     */
    public function viewStudent($id) {
        $id = (int)$id;
        
        // 获取学生信息
        require_once MODEL_PATH . '/Student.php';
        $studentModel = new Student();
        $student = $studentModel->getStudentWithInfo($id);
        
        if (!$student) {
            $_SESSION['error_message'] = '找不到指定的学生。';
            redirect(site_url('admin/students'));
            return;
        }
        
        // 设置页面标题
        $page_title = '查看学生详情';
        $active_page = 'admin_students';
        
        // 加载模态框样式
        $extra_css = '<link rel="stylesheet" href="' . asset_url('css/modals.css') . '">';
        
        // 获取学生统计信息
        $stats = [];
        $stats['achievements_count'] = $this->getCount(TABLE_PREFIX . 'achievements', 'COUNT(*)', "student_id = " . $id);
        $sql = "SELECT SUM(score) as total_points FROM " . TABLE_PREFIX . "achievements WHERE student_id = :student_id AND score IS NOT NULL";
        $result = $this->db->query($sql, ['student_id' => $id]);
        $stats['total_points'] = $result ? (float)$result['total_points'] : 0;
        
        // 获取学生成就列表（用于显示在侧边栏）
        $student_achievements = [];
        if ($stats['achievements_count'] > 0) {
            $sql = "SELECT a.*, 'fas fa-trophy' as icon, DATE_FORMAT(a.achieved_date, '%Y-%m-%d') as achieved_at 
                    FROM " . TABLE_PREFIX . "achievements a 
                    WHERE a.student_id = :student_id 
                    ORDER BY a.achieved_date DESC 
                    LIMIT 5";
            $student_achievements = $this->db->queryAll($sql, ['student_id' => $id]);
        }
        
        // 获取学生最近活动记录
        $activity_logs = $this->getStudentActivityLogs($id, 5);
        
        include_once VIEW_PATH . '/admin/view_student.php';
    }
    
    /**
     * 获取学生活动记录
     *
     * @param int $student_id 学生ID
     * @param int $limit 限制记录数
     * @return array 活动记录
     */
    private function getStudentActivityLogs($student_id, $limit = 5) {
        // 此处可以查询学生的活动记录表
        // 由于系统可能还没有活动记录表，所以这里返回空数组
        return [];
    }

    /**
     * 删除学生
     * 
     * @param int $id 学生ID
     * @return void
     */
    public function deleteStudent($id) {
        // 调试信息
        error_log("deleteStudent方法被调用，ID: " . $id);
        error_log("POST数据: " . json_encode($_POST));
        
        // 验证CSRF令牌
        if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
            $_SESSION['error_message'] = '安全验证失败，请重试。';
            redirect(site_url('admin/students'));
            return;
        }

        // 检查学生是否存在
        $sql = "SELECT * FROM " . TABLE_PREFIX . "students WHERE id = :id";
        $student = $this->db->query($sql, ['id' => $id]);
        
        if (!$student) {
            $_SESSION['error_message'] = '学生不存在，无法删除。';
            redirect(site_url('admin/students'));
            return;
        }
        
        // 查询与此学生关联的用户ID
        $sql = "SELECT user_id FROM " . TABLE_PREFIX . "students WHERE id = :id";
        $result = $this->db->query($sql, ['id' => $id]);
        $user_id = $result ? $result['user_id'] : null;
        
        // 开始事务
        $this->db->beginTransaction();
        
        try {
            // 先删除学生的所有成就记录
            $sql = "DELETE FROM " . TABLE_PREFIX . "achievements WHERE student_id = :student_id";
            $this->db->exec($sql, ['student_id' => $id]);
            
            // 删除学生记录
            $sql = "DELETE FROM " . TABLE_PREFIX . "students WHERE id = :id";
            $this->db->exec($sql, ['id' => $id]);
            
            // 如果有关联的用户账号，也删除用户账号
            if ($user_id) {
                $sql = "DELETE FROM " . TABLE_PREFIX . "users WHERE id = :id";
                $this->db->exec($sql, ['id' => $user_id]);
            }
            
            // 提交事务
            $this->db->commit();
            
            $_SESSION['success_message'] = '学生删除成功。';
        } catch (Exception $e) {
            // 回滚事务
            $this->db->rollBack();
            
            // 记录错误信息
            error_log("删除学生错误: " . $e->getMessage());
            
            $_SESSION['error_message'] = '删除学生失败，请重试。';
        }
        
        // 重定向回学生列表
        redirect(site_url('admin/students'));
    }

    /**
     * 处理批量操作请求
     */
    public function bulkAction() {
        // 验证CSRF令牌
        if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
            $_SESSION['error_message'] = '安全令牌验证失败，请重试。';
            redirect(site_url('admin/students'));
            return;
        }
        
        // 检查操作类型
        $action = isset($_POST['action']) ? sanitize_input($_POST['action']) : '';
        
        // 检查选中的学生
        $selectedStudents = isset($_POST['selected_students']) ? $_POST['selected_students'] : [];
        
        if (empty($selectedStudents) || !is_array($selectedStudents)) {
            $_SESSION['error_message'] = '请至少选择一名学生。';
            redirect(site_url('admin/students'));
            return;
        }
        
        // 将ID转换为整数
        $selectedStudents = array_map('intval', $selectedStudents);
        
        // 根据操作类型执行不同的操作
        switch ($action) {
            case 'delete':
                $this->bulkDeleteStudents($selectedStudents);
                break;
                
            // 可以在这里添加其他批量操作类型
            
            default:
                $_SESSION['error_message'] = '未知的操作类型。';
                redirect(site_url('admin/students'));
                break;
        }
    }
    
    /**
     * 批量删除学生
     * 
     * @param array $studentIds 学生ID数组
     */
    private function bulkDeleteStudents($studentIds) {
        if (empty($studentIds)) {
            return;
        }
        
        // 开始事务
        $this->db->beginTransaction();
        
        try {
            // 获取关联的用户ID
            $placeholders = implode(',', array_fill(0, count($studentIds), '?'));
            $sql = "SELECT id, user_id FROM " . TABLE_PREFIX . "students WHERE id IN ({$placeholders})";
            $students = $this->db->queryAll($sql, $studentIds);
            
            // 提取用户ID
            $userIds = [];
            foreach ($students as $student) {
                if (!empty($student['user_id'])) {
                    $userIds[] = $student['user_id'];
                }
            }
            
            // 删除成就记录
            if (!empty($studentIds)) {
                $placeholders = implode(',', array_fill(0, count($studentIds), '?'));
                $sql = "DELETE FROM " . TABLE_PREFIX . "achievements WHERE student_id IN ({$placeholders})";
                $this->db->exec($sql, $studentIds);
            }
            
            // 删除学生记录
            if (!empty($studentIds)) {
                $placeholders = implode(',', array_fill(0, count($studentIds), '?'));
                $sql = "DELETE FROM " . TABLE_PREFIX . "students WHERE id IN ({$placeholders})";
                $this->db->exec($sql, $studentIds);
            }
            
            // 删除用户记录
            if (!empty($userIds)) {
                $placeholders = implode(',', array_fill(0, count($userIds), '?'));
                $sql = "DELETE FROM " . TABLE_PREFIX . "users WHERE id IN ({$placeholders})";
                $this->db->exec($sql, $userIds);
            }
            
            // 提交事务
            $this->db->commit();
            
            $_SESSION['success_message'] = '成功删除 ' . count($studentIds) . ' 名学生。';
        } catch (Exception $e) {
            // 回滚事务
            $this->db->rollBack();
            
            // 记录错误信息
            error_log("批量删除学生错误: " . $e->getMessage());
            
            $_SESSION['error_message'] = '删除学生失败，请重试。';
        }
        
        redirect(site_url('admin/students'));
    }
    
    /**
     * 导出学生数据到Excel
     * 
     * @return void
     */
    public function exportStudents() {
        // 检查是否指定了特定学生
        $selectedIds = [];
        if (isset($_GET['ids']) && !empty($_GET['ids'])) {
            $selectedIds = array_map('intval', explode(',', $_GET['ids']));
        }
        
        // 构建查询
        $sql = "SELECT s.*, u.email, u.status, u.last_login, COUNT(a.id) as achievement_count 
                FROM " . TABLE_PREFIX . "students s
                LEFT JOIN " . TABLE_PREFIX . "users u ON s.user_id = u.id
                LEFT JOIN " . TABLE_PREFIX . "achievements a ON s.id = a.student_id";
        
        $params = [];
        $whereConditions = [];
        
        // 如果选择了特定学生，则只导出这些学生的数据
        if (!empty($selectedIds)) {
            $placeholders = implode(',', array_fill(0, count($selectedIds), '?'));
            $whereConditions[] = "s.id IN ({$placeholders})";
            $params = $selectedIds;
        }
        
        // 组合WHERE子句
        if (!empty($whereConditions)) {
            $sql .= " WHERE " . implode(' AND ', $whereConditions);
        }
        
        // 添加分组和排序
        $sql .= " GROUP BY s.id ORDER BY s.name";
        
        // 执行查询
        $students = $this->db->queryAll($sql, $params);
        
        // 如果没有学生数据，返回提示
        if (empty($students)) {
            $_SESSION['error_message'] = '没有找到学生数据可供导出';
            redirect(site_url('admin/students'));
            return;
        }
        
        // 设置文件名
        $filename = 'students_export_' . date('Ymd_His') . '.csv';
        
        // 设置CSV表头
        $headers = [
            '学号', '姓名', '班级', '联系方式', '邮箱', '账号状态', '最后登录', '成就数量', '注册时间', '备注'
        ];
        
        // 设置响应头
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // 创建输出流
        $output = fopen('php://output', 'w');
        
        // 添加UTF-8 BOM，解决Excel打开CSV中文乱码问题
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // 写入表头
        fputcsv($output, $headers);
        
        // 写入数据行
        foreach ($students as $student) {
            $row = [
                $student['student_id'],
                $student['name'],
                $student['class_name'] ?? '',
                $student['contact'] ?? '',
                $student['email'] ?? '',
                $student['status'] == 1 ? '启用' : '禁用',
                !empty($student['last_login']) ? date('Y-m-d H:i:s', strtotime($student['last_login'])) : '尚未登录',
                $student['achievement_count'] ?? 0,
                date('Y-m-d H:i:s', strtotime($student['created_at'])),
                $student['notes'] ?? ''
            ];
            
            fputcsv($output, $row);
        }
        
        // 关闭输出流
        fclose($output);
        exit;
    }
} 