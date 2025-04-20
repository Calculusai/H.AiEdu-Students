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
     * 导入学生数据（从Excel文件）
     */
    public function importStudents() {
        // 验证CSRF令牌
        if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
            $_SESSION['error_message'] = '安全令牌验证失败，请重试。';
            redirect(site_url('admin/add_student'));
            return;
        }
        
        // 检查是否上传了文件
        if (!isset($_FILES['import_file']) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['error_message'] = '请选择Excel文件进行导入。';
            redirect(site_url('admin/add_student'));
            return;
        }
        
        // 检查文件类型
        $file_info = pathinfo($_FILES['import_file']['name']);
        $extension = strtolower($file_info['extension']);
        
        if (!in_array($extension, ['xlsx', 'xls'])) {
            $_SESSION['error_message'] = '只支持Excel文件格式（.xlsx或.xls）。';
            redirect(site_url('admin/add_student'));
            return;
        }
        
        // 是否自动生成密码
        $generate_password = isset($_POST['generate_password']) && $_POST['generate_password'] == 1;
        
        try {
            // 引入PHPExcel库
            require_once LIB_PATH . '/vendor/autoload.php';
            
            // 创建reader对象
            if ($extension == 'xlsx') {
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            } else {
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
            }
            
            // 加载Excel文件
            $spreadsheet = $reader->load($_FILES['import_file']['tmp_name']);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
            
            // 至少需要两行（表头和数据）
            if (count($rows) < 2) {
                $_SESSION['error_message'] = 'Excel文件中没有数据。';
                redirect(site_url('admin/add_student'));
                return;
            }
            
            // 获取表头
            $headers = array_map('strtolower', $rows[0]);
            
            // 检查必要的列
            if (!in_array('student_id', $headers) || !in_array('name', $headers)) {
                $_SESSION['error_message'] = 'Excel文件必须包含学号(student_id)和姓名(name)列。';
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
            
            // 开始导入数据（跳过表头）
            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                
                // 跳过空行
                if (empty($row[$column_map['student_id']]) || empty($row[$column_map['name']])) {
                    continue;
                }
                
                // 提取学生数据
                $student_id = trim($row[$column_map['student_id']]);
                $name = trim($row[$column_map['name']]);
                
                // 准备学生数据
                $studentData = [
                    'student_id' => $student_id,
                    'name' => $name,
                    'class_name' => isset($column_map['class_name']) ? trim($row[$column_map['class_name']]) : '',
                    'contact' => isset($column_map['contact']) ? trim($row[$column_map['contact']]) : '',
                    'notes' => isset($column_map['notes']) ? trim($row[$column_map['notes']]) : ''
                ];
                
                // 准备用户数据
                $email = isset($column_map['email']) ? trim($row[$column_map['email']]) : '';
                
                // 设置密码
                if (isset($column_map['password']) && !empty($row[$column_map['password']])) {
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
                            'row' => $i + 1,
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
                                'row' => $i + 1,
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
                            'row' => $i + 1,
                            'student_id' => $student_id,
                            'name' => $name,
                            'error' => '创建失败'
                        ];
                        $error_count++;
                    }
                } catch (Exception $e) {
                    $error_rows[] = [
                        'row' => $i + 1,
                        'student_id' => $student_id,
                        'name' => $name,
                        'error' => $e->getMessage()
                    ];
                    $error_count++;
                }
            }
            
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
        // 设置Excel文件信息
        $filename = '学生导入模板.xlsx';
        $headers = ['学号(student_id)', '姓名(name)', '班级(class_name)', '联系方式(contact)', '邮箱(email)', '备注(notes)', '密码(password)'];
        
        // 创建Spreadsheet对象
        require_once LIB_PATH . '/vendor/autoload.php';
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // 设置表头
        for ($i = 0; $i < count($headers); $i++) {
            $sheet->setCellValue(chr(65 + $i) . '1', $headers[$i]);
            $sheet->getStyle(chr(65 + $i) . '1')->getFont()->setBold(true);
        }
        
        // 设置示例数据
        $sheet->setCellValue('A2', '20210001');
        $sheet->setCellValue('B2', '张三');
        $sheet->setCellValue('C2', '计算机科学1班');
        $sheet->setCellValue('D2', '13800138000');
        $sheet->setCellValue('E2', 'zhangsan@example.com');
        $sheet->setCellValue('F2', '班长');
        $sheet->setCellValue('G2', 'password123');
        
        // 设置列宽
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // 创建writer
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        
        // 输出文件
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }
    
    /**
     * 显示学生列表
     */
    public function listStudents() {
        $page_title = '学生管理';
        $active_page = 'admin_students';
        
        // 获取当前页码
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        
        // 获取搜索条件
        $search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
        
        // 构建查询
        $sql = "SELECT s.*, u.username, COUNT(a.id) as achievement_count 
                FROM " . TABLE_PREFIX . "students s
                LEFT JOIN " . TABLE_PREFIX . "users u ON s.user_id = u.id
                LEFT JOIN " . TABLE_PREFIX . "achievements a ON s.id = a.student_id";
        
        $params = [];
        
        // 添加搜索条件
        if (!empty($search)) {
            $sql .= " WHERE s.name LIKE :search OR s.school LIKE :search OR s.grade LIKE :search";
            $params['search'] = "%{$search}%";
        }
        
        // 添加分组
        $sql .= " GROUP BY s.id";
        
        // 添加排序
        $sql .= " ORDER BY s.name";
        
        // 获取总记录数
        $countSql = "SELECT COUNT(*) as total FROM " . TABLE_PREFIX . "students s";
        if (!empty($search)) {
            $countSql .= " WHERE s.name LIKE :search OR s.school LIKE :search OR s.grade LIKE :search";
        }
        $totalResult = $this->db->query($countSql, $params);
        $total = $totalResult ? $totalResult['total'] : 0;
        
        // 计算总页数
        $totalPages = ceil($total / $this->itemsPerPage);
        $page = min($page, max(1, $totalPages));
        
        // 添加分页
        $offset = ($page - 1) * $this->itemsPerPage;
        $sql .= " LIMIT {$offset}, {$this->itemsPerPage}";
        
        // 执行查询
        $students = $this->db->queryAll($sql, $params);
        
        // 生成分页HTML
        $pagination = get_pagination($total, $this->itemsPerPage, $page, '?page=%d' . 
                                    (!empty($search) ? '&search=' . urlencode($search) : ''));
        
        include_once VIEW_PATH . '/admin/students.php';
    }
    
    /**
     * 显示成就列表
     */
    public function listAchievements() {
        $page_title = '成就管理';
        $active_page = 'admin_achievements';
        
        // 获取当前页码
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        
        // 获取筛选条件
        $type = isset($_GET['type']) ? sanitize_input($_GET['type']) : '';
        $search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
        $student_id = isset($_GET['student_id']) ? (int)$_GET['student_id'] : 0;
        
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
        if (!empty($type)) {
            $whereConditions[] = "a.achievement_type = :type";
            $params['type'] = $type;
        }
        
        if (!empty($search)) {
            $whereConditions[] = "(a.title LIKE :search OR a.description LIKE :search OR s.name LIKE :search)";
            $params['search'] = "%{$search}%";
        }
        
        if ($student_id > 0) {
            $whereConditions[] = "a.student_id = :student_id";
            $params['student_id'] = $student_id;
        }
        
        // 组合WHERE子句
        if (!empty($whereConditions)) {
            $sql .= " WHERE " . implode(' AND ', $whereConditions);
        }
        
        // 添加排序
        $sql .= " ORDER BY a.achieved_date DESC";
        
        // 获取总记录数
        $countSql = str_replace("a.*, s.name as student_name", "COUNT(*) as total", $sql);
        $totalResult = $this->db->query($countSql, $params);
        $total = $totalResult ? $totalResult['total'] : 0;
        
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
            redirect(site_url('admin/achievements'));
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
        
        redirect(site_url('admin/achievements'));
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
            if ($key != 'csrf_token' && $key != 'setting_group') {
                // 复选框特殊处理
                if (strpos($key, 'enable_') === 0 || strpos($key, 'allow_') === 0) {
                    $settings[$key] = isset($_POST[$key]) ? 1 : 0;
                } else {
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
        } else {
            $_SESSION['flash_message'] = '保存设置失败，请重试。';
            $_SESSION['flash_type'] = 'danger';
        }
        
        // 重定向回设置页面，并保持当前标签页
        redirect(site_url('admin/settings?tab=' . $group));
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
        $password = isset($_POST['password']) ? sanitize_input($_POST['password']) : '';
        if (!empty($password)) {
            $userData['password'] = $password;
            
            // 如果勾选了"要求下次登录修改密码"，则设置标志
            if (isset($_POST['require_change_password']) && $_POST['require_change_password'] == 1) {
                $userData['require_password_change'] = 1;
            }
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
} 