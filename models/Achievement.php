<?php
/**
 * 成就模型类
 */
class Achievement extends Model {
    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct('achievements');
    }
    
    /**
     * 获取成就详情，包含学生信息
     *
     * @param int $id 成就ID
     * @return array|bool 成就详情或false
     */
    public function getAchievementWithStudent($id) {
        $sql = "SELECT a.*, s.name as student_name 
                FROM {$this->table} a 
                JOIN " . TABLE_PREFIX . "students s ON a.student_id = s.id 
                WHERE a.id = :id";
        
        return $this->db->query($sql, ['id' => $id]);
    }
    
    /**
     * 获取成就列表，带学生信息和筛选功能
     *
     * @param int $page 当前页码
     * @param int $perPage 每页记录数
     * @param array $filters 筛选条件
     * @return array 成就列表数据
     */
    public function getAchievementsWithFilters($page = 1, $perPage = 20, $filters = []) {
        $sql = "SELECT a.*, s.name as student_name 
                FROM {$this->table} a 
                JOIN " . TABLE_PREFIX . "students s ON a.student_id = s.id";
        
        $params = [];
        $whereConditions = [];
        
        // 处理筛选条件
        if (!empty($filters['type'])) {
            $whereConditions[] = "a.achievement_type = :type";
            $params['type'] = $filters['type'];
        }
        
        if (!empty($filters['student_id'])) {
            $whereConditions[] = "a.student_id = :student_id";
            $params['student_id'] = $filters['student_id'];
        }
        
        if (!empty($filters['search'])) {
            $whereConditions[] = "(a.title LIKE :search OR a.description LIKE :search OR s.name LIKE :search)";
            $params['search'] = "%{$filters['search']}%";
        }
        
        // 组合WHERE子句
        if (!empty($whereConditions)) {
            $sql .= " WHERE " . implode(' AND ', $whereConditions);
        }
        
        // 添加排序
        $orderBy = !empty($filters['order_by']) ? $filters['order_by'] : 'a.achieved_date';
        $order = !empty($filters['order']) ? $filters['order'] : 'DESC';
        $sql .= " ORDER BY {$orderBy} {$order}";
        
        // 获取总记录数
        $countSql = str_replace("a.*, s.name as student_name", "COUNT(*) as total", $sql);
        $countSql = preg_replace('/ORDER BY.*$/', '', $countSql);
        
        $totalResult = $this->db->query($countSql, $params);
        $total = $totalResult ? (int)$totalResult['total'] : 0;
        
        // 计算总页数
        $totalPages = ceil($total / $perPage);
        $page = min($page, max(1, $totalPages));
        
        // 计算偏移量
        $offset = ($page - 1) * $perPage;
        
        // 添加分页限制
        $sql .= " LIMIT {$offset}, {$perPage}";
        
        // 获取数据
        $achievements = $this->db->queryAll($sql, $params);
        
        return [
            'data' => $achievements,
            'total' => $total,
            'total_pages' => $totalPages,
            'current_page' => $page,
            'per_page' => $perPage
        ];
    }
    
    /**
     * 获取学生的成就列表
     *
     * @param int $studentId 学生ID
     * @param int $page 当前页码
     * @param int $perPage 每页记录数
     * @param array $filters 筛选条件
     * @return array 成就列表数据
     */
    public function getStudentAchievements($studentId, $page = 1, $perPage = 20, $filters = []) {
        $filters['student_id'] = $studentId;
        return $this->getAchievementsWithFilters($page, $perPage, $filters);
    }
    
    /**
     * 获取成就类型列表
     *
     * @return array 成就类型列表
     */
    public function getAchievementTypes() {
        $sql = "SELECT DISTINCT achievement_type FROM {$this->table} ORDER BY achievement_type";
        $results = $this->db->queryAll($sql);
        
        return array_column($results, 'achievement_type');
    }
    
    /**
     * 获取最新成就列表
     *
     * @param int $limit 记录数量限制
     * @return array 成就列表
     */
    public function getLatestAchievements($limit = 10) {
        $sql = "SELECT a.*, s.name as student_name 
                FROM {$this->table} a 
                JOIN " . TABLE_PREFIX . "students s ON a.student_id = s.id 
                ORDER BY a.created_at DESC 
                LIMIT " . (int)$limit;
        
        return $this->db->queryAll($sql);
    }
    
    /**
     * 上传成就附件
     *
     * @param array $file $_FILES数组中的文件项
     * @return string|bool 上传后的文件名或false
     */
    public function uploadAttachment($file) {
        // 检查文件是否有效
        if (!isset($file) || $file['error'] != 0) {
            return false;
        }
        
        // 检查上传目录是否存在，不存在则创建
        $uploadDir = UPLOAD_PATH;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // 生成唯一文件名
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $newFilename = 'certificate_' . date('YmdHis') . '_' . uniqid() . '.' . $extension;
        $targetPath = $uploadDir . '/' . $newFilename;
        
        // 移动上传的文件
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return $newFilename;
        }
        
        return false;
    }
    
    /**
     * 删除成就及附件
     *
     * @param int $id 成就ID
     * @return bool 是否成功
     */
    public function deleteWithAttachment($id) {
        // 获取成就信息
        $achievement = $this->find($id);
        
        if (!$achievement) {
            return false;
        }
        
        // 如果有附件，删除附件文件
        if (!empty($achievement['attachment'])) {
            $attachmentPath = UPLOAD_PATH . '/' . $achievement['attachment'];
            if (file_exists($attachmentPath)) {
                @unlink($attachmentPath);
            }
        }
        
        // 删除成就记录
        return $this->delete($id);
    }
    
    /**
     * 统计每种成就类型的数量
     *
     * @return array 类型统计数据
     */
    public function countByTypes() {
        $sql = "SELECT achievement_type, COUNT(*) as count 
                FROM {$this->table} 
                GROUP BY achievement_type 
                ORDER BY count DESC";
        
        return $this->db->queryAll($sql);
    }
    
    /**
     * 获取学生的成就统计信息
     *
     * @param int $studentId 学生ID
     * @return array 统计信息
     */
    public function getStudentAchievementStats($studentId) {
        // 获取学生成就总数
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE student_id = :student_id";
        $result = $this->db->query($sql, ['student_id' => $studentId]);
        $total = $result ? (int)$result['total'] : 0;
        
        // 获取学生各类型成就数量
        $sql = "SELECT achievement_type, COUNT(*) as count 
                FROM {$this->table} 
                WHERE student_id = :student_id 
                GROUP BY achievement_type 
                ORDER BY count DESC";
        $typeResults = $this->db->queryAll($sql, ['student_id' => $studentId]);
        
        $types = [];
        foreach ($typeResults as $row) {
            $types[$row['achievement_type']] = (int)$row['count'];
        }
        
        return [
            'total' => $total,
            'types' => $types
        ];
    }
    
    /**
     * 统计符合筛选条件的成就数量
     *
     * @param string $where_sql 筛选WHERE子句
     * @param array $params 筛选参数
     * @return int 成就总数
     */
    public function countWithFilters($where_sql, $params = []) {
        $count_sql = "SELECT COUNT(*) as total FROM {$this->table} a" . $where_sql;
        $result = $this->db->query($count_sql, $params);
        return $result ? (int)$result['total'] : 0;
    }
    
    /**
     * 获取筛选后的成就列表，包含学生信息
     *
     * @param string $where_sql 筛选WHERE子句
     * @param array $params 筛选参数
     * @param int $page 当前页码
     * @param int $per_page 每页记录数
     * @return array 成就列表
     */
    public function getFilteredAchievements($where_sql, $params = [], $page = 1, $per_page = 9) {
        $offset = ($page - 1) * $per_page;
        
        $sql = "SELECT a.*, s.name as student_name 
                FROM {$this->table} a 
                JOIN " . TABLE_PREFIX . "students s ON a.student_id = s.id
                " . $where_sql . "
                ORDER BY a.achieved_date DESC 
                LIMIT {$offset}, {$per_page}";
        
        return $this->db->queryAll($sql, $params);
    }
} 