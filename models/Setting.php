<?php
/**
 * 系统设置模型类
 */
class Setting extends Model {
    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct('settings');
    }
    
    /**
     * 获取设置值
     *
     * @param string $key 设置键名
     * @param string $default 默认值
     * @return string 设置值
     */
    public function get($key, $default = '') {
        $sql = "SELECT setting_value FROM {$this->table} WHERE setting_key = :key LIMIT 1";
        $result = $this->db->query($sql, ['key' => $key]);
        
        return $result ? $result['setting_value'] : $default;
    }
    
    /**
     * 设置值
     *
     * @param string $key 设置键名
     * @param string $value 设置值
     * @param string $group 设置分组
     * @return bool 是否成功
     */
    public function set($key, $value, $group = 'general') {
        // 检查设置是否已存在
        $sql = "SELECT id FROM {$this->table} WHERE setting_key = :key LIMIT 1";
        $result = $this->db->query($sql, ['key' => $key]);
        
        if ($result) {
            // 更新设置
            $sql = "UPDATE {$this->table} SET setting_value = :value, updated_at = NOW() WHERE setting_key = :key";
            return $this->db->exec($sql, ['key' => $key, 'value' => $value]);
        } else {
            // 插入新设置
            $sql = "INSERT INTO {$this->table} (setting_key, setting_value, setting_group, created_at, updated_at) 
                    VALUES (:key, :value, :group, NOW(), NOW())";
            return $this->db->exec($sql, ['key' => $key, 'value' => $value, 'group' => $group]);
        }
    }
    
    /**
     * 批量设置值
     *
     * @param array $settings 设置数组，格式为 ['键名' => '值']
     * @param string $group 设置分组
     * @return bool 是否成功
     */
    public function setMultiple($settings, $group = 'general') {
        $this->db->beginTransaction();
        
        try {
            foreach ($settings as $key => $value) {
                $this->set($key, $value, $group);
            }
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
    
    /**
     * 获取指定分组的所有设置
     *
     * @param string $group 设置分组
     * @return array 设置数组
     */
    public function getGroupSettings($group) {
        $sql = "SELECT setting_key, setting_value FROM {$this->table} WHERE setting_group = :group";
        $results = $this->db->queryAll($sql, ['group' => $group]);
        
        $settings = [];
        foreach ($results as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        
        return $settings;
    }
    
    /**
     * 获取所有设置，按分组组织
     *
     * @return array 设置数组
     */
    public function getAllSettings() {
        $sql = "SELECT setting_key, setting_value, setting_group FROM {$this->table} ORDER BY setting_group, setting_key";
        $results = $this->db->queryAll($sql);
        
        $settings = [];
        foreach ($results as $row) {
            if (!isset($settings[$row['setting_group']])) {
                $settings[$row['setting_group']] = [];
            }
            
            $settings[$row['setting_group']][$row['setting_key']] = $row['setting_value'];
        }
        
        return $settings;
    }
    
    /**
     * 删除设置
     *
     * @param string $key 设置键名
     * @return bool 是否成功
     */
    public function delete($key) {
        $sql = "DELETE FROM {$this->table} WHERE setting_key = :key";
        return $this->db->exec($sql, ['key' => $key]);
    }
    
    /**
     * 重置设置为默认值
     *
     * @return bool 是否成功
     */
    public function resetToDefaults() {
        $this->db->beginTransaction();
        
        try {
            // 清空设置表
            $sql = "TRUNCATE TABLE {$this->table}";
            $this->db->exec($sql);
            
            // 插入默认设置
            $defaultSettings = [
                ['site_name', '少儿编程成就展示系统', 'general'],
                ['site_description', '记录和展示少儿编程学习成就', 'general'],
                ['items_per_page', '10', 'general'],
                ['allow_registration', '0', 'security'],
                ['enable_achievements_public', '1', 'content'],
                ['default_theme', 'light', 'appearance']
            ];
            
            foreach ($defaultSettings as $setting) {
                $sql = "INSERT INTO {$this->table} (setting_key, setting_value, setting_group, created_at, updated_at) 
                        VALUES (:key, :value, :group, NOW(), NOW())";
                $this->db->exec($sql, [
                    'key' => $setting[0],
                    'value' => $setting[1],
                    'group' => $setting[2]
                ]);
            }
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
} 