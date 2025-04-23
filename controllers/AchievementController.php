<?php
/**
 * 成就控制器
 */
class AchievementController {
    private $db;
    private $itemsPerPage = 12;
    
    /**
     * 构造函数
     */
    public function __construct() {
        global $db;
        $this->db = $db;
        
        // 从设置获取每页显示数量
        if (SYSTEM_INSTALLED) {
            $setting = $this->getSetting('items_per_page');
            if ($setting) {
                $this->itemsPerPage = (int)$setting;
            }
        }
    }
    
    /**
     * 显示首页
     */
    public function index() {
        // 设置页面标题和当前页面
        $page_title = '首页';
        $active_page = 'home';
        
        // 获取最新成就
        $latestAchievements = $this->getLatestAchievements(6);
        
        // 加载视图
        include_once VIEW_PATH . '/home.php';
    }
    
    /**
     * 获取最新成就
     *
     * @param int $limit 限制数量
     * @return array 成就列表
     */
    private function getLatestAchievements($limit = 6) {
        if (!SYSTEM_INSTALLED) {
            return [];
        }
        
        $sql = "SELECT a.*, s.name as student_name 
                FROM " . TABLE_PREFIX . "achievements a 
                JOIN " . TABLE_PREFIX . "students s ON a.student_id = s.id 
                ORDER BY a.achieved_date DESC 
                LIMIT :limit";
        
        return $this->db->queryAll($sql, ['limit' => $limit]);
    }
    
    /**
     * 显示公共成就列表
     */
    public function listPublicAchievements() {
        // 设置页面标题和当前页面
        $page_title = '成就展示';
        $active_page = 'achievements';
        
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
        $achievements = SYSTEM_INSTALLED ? $this->db->queryAll($sql, $params) : [];
        
        // 生成分页HTML
        $pagination = get_pagination($total, $this->itemsPerPage, $page, '?page=%d' . 
                                    (!empty($type) ? '&type=' . urlencode($type) : '') . 
                                    (!empty($search) ? '&search=' . urlencode($search) : ''));
        
        // 加载视图
        include_once VIEW_PATH . '/achievements.php';
    }
    
    /**
     * 显示学生个人成就
     *
     * @param int $studentId 学生ID
     */
    public function listStudentAchievements($studentId) {
        // 验证学生ID
        $studentId = (int)$studentId;
        
        // 获取学生信息
        $student = $this->getStudent($studentId);
        
        if (!$student) {
            // 学生不存在，显示404页面
            header('HTTP/1.0 404 Not Found');
            include_once VIEW_PATH . '/404.php';
            return;
        }
        
        // 设置页面标题和当前页面
        $page_title = $student['name'] . ' 的成就';
        $active_page = 'achievements';
        
        // 加载模态框样式
        $extra_css = '<link rel="stylesheet" href="' . asset_url('css/modals.css') . '">';
        
        // 获取当前页码
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        
        // 获取筛选条件
        $type = isset($_GET['type']) ? sanitize_input($_GET['type']) : '';
        
        // 获取成就类型列表
        $types = $this->getAchievementTypes();
        
        // 构建查询
        $sql = "SELECT a.* 
                FROM " . TABLE_PREFIX . "achievements a 
                WHERE a.student_id = :student_id";
        
        $params = ['student_id' => $studentId];
        
        // 添加类型筛选
        if (!empty($type)) {
            $sql .= " AND a.achievement_type = :type";
            $params['type'] = $type;
        }
        
        // 添加排序
        $sql .= " ORDER BY a.achieved_date DESC";
        
        // 获取总记录数
        $countSql = str_replace("a.*", "COUNT(*) as total", $sql);
        $totalResult = $this->db->query($countSql, $params);
        $total = $totalResult ? $totalResult['total'] : 0;
        
        // 计算总页数
        $totalPages = ceil($total / $this->itemsPerPage);
        $page = min($page, max(1, $totalPages));
        
        // 添加分页
        $offset = ($page - 1) * $this->itemsPerPage;
        $sql .= " LIMIT {$offset}, {$this->itemsPerPage}";
        
        // 执行查询
        $achievements = SYSTEM_INSTALLED ? $this->db->queryAll($sql, $params) : [];
        
        // 生成分页HTML
        $pagination = get_pagination($total, $this->itemsPerPage, $page, '?page=%d' . 
                                    (!empty($type) ? '&type=' . urlencode($type) : ''));
        
        // 加载视图
        include_once VIEW_PATH . '/student_achievements.php';
    }
    
    /**
     * 获取成就类型列表
     *
     * @return array 成就类型列表
     */
    private function getAchievementTypes() {
        if (!SYSTEM_INSTALLED) {
            return [];
        }
        
        $sql = "SELECT DISTINCT achievement_type FROM " . TABLE_PREFIX . "achievements ORDER BY achievement_type";
        $results = $this->db->queryAll($sql);
        
        return array_column($results, 'achievement_type');
    }
    
    /**
     * 获取学生信息
     *
     * @param int $studentId 学生ID
     * @return array|bool 学生信息或false
     */
    private function getStudent($studentId) {
        if (!SYSTEM_INSTALLED) {
            return false;
        }
        
        $sql = "SELECT * FROM " . TABLE_PREFIX . "students WHERE id = :id";
        return $this->db->query($sql, ['id' => $studentId]);
    }
    
    /**
     * 获取系统设置
     *
     * @param string $key 设置键名
     * @return string|null 设置值或null
     */
    private function getSetting($key) {
        if (!SYSTEM_INSTALLED) {
            return null;
        }
        
        // 使用全局辅助函数获取设置
        return get_setting($key);
    }
    
    /**
     * 显示公共成就页面
     */
    public function showPublicAchievements() {
        $this->listPublicAchievements();
    }
    
    /**
     * 显示学生个人资料和成就页面
     * 
     * @param array $params 路由参数
     */
    public function showStudentProfile($id) {
        $this->listStudentAchievements($id);
    }
} 