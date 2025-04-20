<?php
/**
 * 用户模型类
 */
class User extends Model {
    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct('users');
    }
    
    /**
     * 根据用户名获取用户
     *
     * @param string $username 用户名
     * @return array|bool 用户数据或false
     */
    public function findByUsername($username) {
        $sql = "SELECT * FROM {$this->table} WHERE username = :username LIMIT 1";
        return $this->db->query($sql, ['username' => $username]);
    }
    
    /**
     * 验证用户登录
     *
     * @param string $username 用户名
     * @param string $password 密码
     * @return array|bool 用户数据或false
     */
    public function authenticate($username, $password) {
        $user = $this->findByUsername($username);
        
        if (!$user) {
            return false;
        }
        
        if (!password_verify($password, $user['password'])) {
            return false;
        }
        
        if ($user['status'] != 1) {
            return false;
        }
        
        return $user;
    }
    
    /**
     * 创建用户
     *
     * @param array $data 用户数据
     * @return int|bool 用户ID或false
     */
    public function createUser($data) {
        // 检查用户名是否已存在
        if ($this->findByUsername($data['username'])) {
            error_log("创建用户失败 - 用户名已存在: " . $data['username']);
            return false;
        }
        
        try {
            // 加密密码
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            
            // 设置默认值
            if (!isset($data['status'])) {
                $data['status'] = 1;
            } else {
                // 确保状态是整数
                $data['status'] = (int)$data['status'];
            }
            
            if (!isset($data['theme_preference'])) {
                $data['theme_preference'] = 'light';
            }
            
            // 添加创建时间
            $data['created_at'] = date('Y-m-d H:i:s');
            
            // 确保email字段存在
            if (!isset($data['email'])) {
                $data['email'] = null;
            }
            
            error_log("尝试创建用户 - 数据: " . json_encode($data));
            $userId = $this->insert($data);
            
            if (!$userId) {
                error_log("插入用户记录失败 - 数据库错误: " . json_encode($this->db->getErrorInfo()));
            }
            
            return $userId;
        } catch (Exception $e) {
            error_log("创建用户异常: " . $e->getMessage());
            error_log("异常堆栈: " . $e->getTraceAsString());
            return false;
        }
    }
    
    /**
     * 修改密码
     *
     * @param int $userId 用户ID
     * @param string $newPassword 新密码
     * @return bool 是否成功
     */
    public function changePassword($userId, $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        return $this->update($userId, ['password' => $hashedPassword]);
    }
    
    /**
     * 修改用户状态
     *
     * @param int $userId 用户ID
     * @param int $status 状态：1=启用，0=禁用
     * @return bool 是否成功
     */
    public function changeStatus($userId, $status) {
        return $this->update($userId, ['status' => (int)$status]);
    }
    
    /**
     * 修改主题偏好
     *
     * @param int $userId 用户ID
     * @param string $theme 主题：light, dark, auto
     * @return bool 是否成功
     */
    public function changeTheme($userId, $theme) {
        return $this->update($userId, ['theme_preference' => $theme]);
    }
    
    /**
     * 获取用户角色
     *
     * @param int $userId 用户ID
     * @return string|bool 角色或false
     */
    public function getUserRole($userId) {
        $user = $this->find($userId);
        return $user ? $user['role'] : false;
    }
    
    /**
     * 检查用户是否是管理员
     *
     * @param int $userId 用户ID
     * @return bool 是否是管理员
     */
    public function isAdmin($userId) {
        $role = $this->getUserRole($userId);
        return $role === 'admin';
    }
    
    /**
     * 记录登录信息
     *
     * @param int $userId 用户ID
     */
    public function recordLogin($userId) {
        $this->update($userId, ['last_login' => date('Y-m-d H:i:s')]);
        
        // 如果有登录日志表，可以记录更详细的信息
        if ($this->tableExists(TABLE_PREFIX . 'login_logs')) {
            $ip = $_SERVER['REMOTE_ADDR'];
            $userAgent = $_SERVER['HTTP_USER_AGENT'];
            
            $sql = "INSERT INTO " . TABLE_PREFIX . "login_logs (user_id, login_status, ip_address, user_agent, login_time) 
                    VALUES (:user_id, 'success', :ip_address, :user_agent, NOW())";
            
            $this->db->exec($sql, [
                'user_id' => $userId,
                'ip_address' => $ip,
                'user_agent' => $userAgent
            ]);
        }
    }
    
    /**
     * 检查表是否存在
     *
     * @param string $tableName 表名
     * @return bool 是否存在
     */
    private function tableExists($tableName) {
        $sql = "SHOW TABLES LIKE :table";
        $result = $this->db->query($sql, ['table' => $tableName]);
        return !empty($result);
    }
} 