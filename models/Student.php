<?php
/**
 * 学生模型类
 */
class Student extends Model {
    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct('students');
    }
    
    /**
     * 获取学生列表，带用户信息和成就数量
     *
     * @param int $page 当前页码
     * @param int $perPage 每页记录数
     * @param string $search 搜索关键词
     * @return array 学生列表数据
     */
    public function getStudentsWithInfo($page = 1, $perPage = 20, $search = '') {
        $sql = "SELECT s.*, u.username, u.status, u.last_login, 
                     (SELECT COUNT(*) FROM " . TABLE_PREFIX . "achievements WHERE student_id = s.id) as achievement_count
                FROM {$this->table} s
                LEFT JOIN " . TABLE_PREFIX . "users u ON s.user_id = u.id";
        
        $params = [];
        
        if (!empty($search)) {
            $sql .= " WHERE s.name LIKE :search OR s.student_id LIKE :search OR s.grade LIKE :search";
            $params['search'] = "%{$search}%";
        }
        
        $sql .= " ORDER BY s.id DESC";
        
        // 获取总记录数
        $countSql = str_replace("s.*, u.username, u.status, u.last_login, 
                     (SELECT COUNT(*) FROM " . TABLE_PREFIX . "achievements WHERE student_id = s.id) as achievement_count", 
                                "COUNT(*) as total", $sql);
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
        $students = $this->db->queryAll($sql, $params);
        
        return [
            'data' => $students,
            'total' => $total,
            'total_pages' => $totalPages,
            'current_page' => $page,
            'per_page' => $perPage
        ];
    }
    
    /**
     * 创建学生及用户账号
     *
     * @param array $studentData 学生数据
     * @param array $userData 用户数据
     * @return int|bool 学生ID或false
     */
    public function createStudentWithUser($studentData, $userData) {
        $this->db->beginTransaction();
        
        try {
            // 创建用户账号
            require_once MODEL_PATH . '/User.php';
            $userModel = new User();
            $userId = $userModel->createUser($userData);
            
            if (!$userId) {
                $this->db->rollBack();
                error_log("创建用户失败 - 用户数据: " . json_encode($userData));
                return false;
            }
            
            // 添加用户ID到学生数据
            $studentData['user_id'] = $userId;
            $studentData['created_at'] = date('Y-m-d H:i:s');
            
            // 创建学生记录
            $studentId = $this->insert($studentData);
            
            if (!$studentId) {
                error_log("创建学生记录失败 - 学生数据: " . json_encode($studentData));
                error_log("数据库错误: " . json_encode($this->db->getErrorInfo()));
                $this->db->rollBack();
                return false;
            }
            
            $this->db->commit();
            return $studentId;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("创建学生异常: " . $e->getMessage());
            error_log("异常堆栈: " . $e->getTraceAsString());
            return false;
        }
    }
    
    /**
     * 更新学生信息
     *
     * @param int $id 学生ID
     * @param array $studentData 学生数据
     * @param array $userData 用户数据
     * @return bool 是否成功
     */
    public function updateStudentWithUser($id, $studentData, $userData = null) {
        $this->db->beginTransaction();
        
        try {
            // 更新学生记录
            $result = $this->update($id, $studentData);
            
            if (!$result) {
                $this->db->rollBack();
                return false;
            }
            
            // 如果有用户数据，更新用户记录
            if ($userData) {
                // 获取学生对应的用户ID
                $student = $this->find($id);
                
                if (!$student) {
                    $this->db->rollBack();
                    return false;
                }
                
                require_once MODEL_PATH . '/User.php';
                $userModel = new User();
                $result = $userModel->update($student['user_id'], $userData);
                
                if (!$result) {
                    $this->db->rollBack();
                    return false;
                }
            }
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
    
    /**
     * 删除学生及用户账号
     *
     * @param int $id 学生ID
     * @return bool 是否成功
     */
    public function deleteStudentWithUser($id) {
        $this->db->beginTransaction();
        
        try {
            // 获取学生信息
            $student = $this->find($id);
            
            if (!$student) {
                $this->db->rollBack();
                return false;
            }
            
            // 删除学生的所有成就
            $sql = "DELETE FROM " . TABLE_PREFIX . "achievements WHERE student_id = :student_id";
            $this->db->exec($sql, ['student_id' => $id]);
            
            // 删除学生记录
            $result = $this->delete($id);
            
            if (!$result) {
                $this->db->rollBack();
                return false;
            }
            
            // 删除用户账号
            require_once MODEL_PATH . '/User.php';
            $userModel = new User();
            $result = $userModel->delete($student['user_id']);
            
            if (!$result) {
                $this->db->rollBack();
                return false;
            }
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
    
    /**
     * 重置学生密码
     *
     * @param int $id 学生ID
     * @param string $newPassword 新密码
     * @return bool 是否成功
     */
    public function resetPassword($id, $newPassword) {
        // 获取学生信息
        $student = $this->find($id);
        
        if (!$student) {
            return false;
        }
        
        // 重置密码
        require_once MODEL_PATH . '/User.php';
        $userModel = new User();
        return $userModel->changePassword($student['user_id'], $newPassword);
    }
    
    /**
     * 修改学生账号状态
     *
     * @param int $id 学生ID
     * @param int $status 状态：1=启用，0=禁用
     * @return bool 是否成功
     */
    public function changeAccountStatus($id, $status) {
        // 获取学生信息
        $student = $this->find($id);
        
        if (!$student) {
            return false;
        }
        
        // 修改状态
        require_once MODEL_PATH . '/User.php';
        $userModel = new User();
        return $userModel->changeStatus($student['user_id'], $status);
    }
    
    /**
     * 获取学生详细信息
     *
     * @param int $id 学生ID
     * @return array|bool 学生信息或false
     */
    public function getStudentWithInfo($id) {
        // 先获取users表结构，确认是否有last_login字段
        $sql = "SHOW COLUMNS FROM " . TABLE_PREFIX . "users LIKE 'last_login'";
        $hasLastLogin = $this->db->query($sql) !== false;
        
        // 根据字段情况构建SQL
        if ($hasLastLogin) {
            $sql = "SELECT s.*, u.email, u.status, u.last_login
                    FROM {$this->table} s
                    LEFT JOIN " . TABLE_PREFIX . "users u ON s.user_id = u.id
                    WHERE s.id = :id";
        } else {
            $sql = "SELECT s.*, u.email, u.status
                    FROM {$this->table} s
                    LEFT JOIN " . TABLE_PREFIX . "users u ON s.user_id = u.id
                    WHERE s.id = :id";
        }
        
        $student = $this->db->query($sql, ['id' => $id]);
        
        if ($student) {
            $student['active'] = $student['status'] == 1;
        }
        
        return $student;
    }
} 