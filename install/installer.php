<?php
/**
 * 少儿编程成就展示系统 - 安装程序类
 */
class Installer {
    // 安装锁文件路径
    private $lockFile;
    // 配置文件路径
    private $configFile;
    // 数据库SQL文件路径
    private $sqlFile;
    
    /**
     * 构造函数
     */
    public function __construct() {
        $this->lockFile = dirname(__DIR__) . '/.install_lock';
        $this->configFile = dirname(__DIR__) . '/config.php';
        $this->sqlFile = __DIR__ . '/database.sql';
    }
    
    /**
     * 检查系统是否已安装
     *
     * @return bool 是否已安装
     */
    public function isInstalled() {
        return file_exists($this->lockFile) || (
            defined('SYSTEM_INSTALLED') && SYSTEM_INSTALLED === true
        );
    }
    
    /**
     * 检查系统要求
     *
     * @return array 系统要求检查结果
     */
    public function checkRequirements() {
        $requirements = [];
        
        // PHP版本检查
        $requirements[] = [
            'name' => 'PHP版本 >= 8.0',
            'status' => version_compare(PHP_VERSION, '8.0.0', '>='),
            'value' => PHP_VERSION
        ];
        
        // 必要的PHP扩展
        $requiredExtensions = ['pdo', 'pdo_mysql', 'mbstring', 'json', 'gd'];
        foreach ($requiredExtensions as $ext) {
            $requirements[] = [
                'name' => "PHP扩展: {$ext}",
                'status' => extension_loaded($ext),
                'value' => extension_loaded($ext) ? '已安装' : '未安装'
            ];
        }
        
        // 目录权限检查
        $dirPermissions = [
            '/' => dirname(__DIR__),
            '/config.php' => $this->configFile,
            '/uploads' => dirname(__DIR__) . '/uploads',
            '/assets' => dirname(__DIR__) . '/assets',
        ];
        
        foreach ($dirPermissions as $name => $path) {
            $isWritable = is_writable($path);
            $requirements[] = [
                'name' => "目录权限: {$name}",
                'status' => $isWritable,
                'value' => $isWritable ? '可写' : '不可写'
            ];
        }
        
        return $requirements;
    }
    
    /**
     * 设置数据库
     *
     * @param array $data 表单数据
     * @return bool|string 成功返回true，失败返回错误信息
     */
    public function setupDatabase($data) {
        $host = isset($data['db_host']) ? trim($data['db_host']) : '';
        $name = isset($data['db_name']) ? trim($data['db_name']) : '';
        $user = isset($data['db_user']) ? trim($data['db_user']) : '';
        $pass = isset($data['db_pass']) ? trim($data['db_pass']) : '';
        $prefix = isset($data['table_prefix']) ? trim($data['table_prefix']) : 'ach_';
        
        // 验证数据
        if (empty($host) || empty($name) || empty($user)) {
            return '请填写所有必填字段';
        }
        
        // 尝试连接数据库
        try {
            $dsn = "mysql:host={$host};charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ];
            
            $pdo = new PDO($dsn, $user, $pass, $options);
            
            // 创建数据库（如果不存在）
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$name}` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            
            // 选择数据库
            $pdo->exec("USE `{$name}`");
            
            // 导入SQL文件
            if (file_exists($this->sqlFile)) {
                $sql = file_get_contents($this->sqlFile);
                $sql = str_replace('{TABLE_PREFIX}', $prefix, $sql);
                
                $statements = array_filter(
                    array_map('trim', explode(';', $sql)),
                    function($statement) {
                        return !empty($statement);
                    }
                );
                
                foreach ($statements as $statement) {
                    $pdo->exec($statement);
                }
            } else {
                return 'SQL文件不存在: ' . $this->sqlFile;
            }
            
            // 更新配置文件
            $config = file_get_contents($this->configFile);
            $config = preg_replace("/define\('DB_HOST',\s*'.*?'\);/", "define('DB_HOST', '{$host}');", $config);
            $config = preg_replace("/define\('DB_NAME',\s*'.*?'\);/", "define('DB_NAME', '{$name}');", $config);
            $config = preg_replace("/define\('DB_USER',\s*'.*?'\);/", "define('DB_USER', '{$user}');", $config);
            $config = preg_replace("/define\('DB_PASS',\s*'.*?'\);/", "define('DB_PASS', '{$pass}');", $config);
            $config = preg_replace("/define\('TABLE_PREFIX',\s*'.*?'\);/", "define('TABLE_PREFIX', '{$prefix}');", $config);
            
            file_put_contents($this->configFile, $config);
            
            return true;
        } catch (Exception $e) {
            return '数据库连接错误: ' . $e->getMessage();
        }
    }
    
    /**
     * 设置管理员
     *
     * @param array $data 表单数据
     * @return bool|string 成功返回true，失败返回错误信息
     */
    public function setupAdmin($data) {
        $site_name = isset($data['site_name']) ? trim($data['site_name']) : '';
        $site_url = isset($data['site_url']) ? trim($data['site_url']) : '';
        $admin_email = isset($data['admin_email']) ? trim($data['admin_email']) : '';
        $admin_user = isset($data['admin_user']) ? trim($data['admin_user']) : '';
        $admin_pass = isset($data['admin_pass']) ? trim($data['admin_pass']) : '';
        $admin_pass_confirm = isset($data['admin_pass_confirm']) ? trim($data['admin_pass_confirm']) : '';
        $default_theme = isset($data['default_theme']) ? trim($data['default_theme']) : 'light';
        
        // 验证数据
        if (empty($site_name) || empty($admin_user) || empty($admin_pass)) {
            return '请填写所有必填字段';
        }
        
        if ($admin_pass !== $admin_pass_confirm) {
            return '两次输入的密码不一致';
        }
        
        if (strlen($admin_pass) < 6) {
            return '密码长度至少为6个字符';
        }
        
        // 更新配置文件
        $config = file_get_contents($this->configFile);
        $config = preg_replace("/define\('SITE_NAME',\s*'.*?'\);/", "define('SITE_NAME', '{$site_name}');", $config);
        $config = preg_replace("/define\('SITE_URL',\s*'.*?'\);/", "define('SITE_URL', '{$site_url}');", $config);
        $config = preg_replace("/define\('ADMIN_EMAIL',\s*'.*?'\);/", "define('ADMIN_EMAIL', '{$admin_email}');", $config);
        $config = preg_replace("/define\('DEFAULT_THEME',\s*'.*?'\);/", "define('DEFAULT_THEME', '{$default_theme}');", $config);
        
        // 生成安全密钥
        $auth_key = bin2hex(random_bytes(16));
        $secure_auth_key = bin2hex(random_bytes(16));
        
        $config = preg_replace("/define\('AUTH_KEY',\s*'.*?'\);/", "define('AUTH_KEY', '{$auth_key}');", $config);
        $config = preg_replace("/define\('SECURE_AUTH_KEY',\s*'.*?'\);/", "define('SECURE_AUTH_KEY', '{$secure_auth_key}');", $config);
        
        file_put_contents($this->configFile, $config);
        
        // 创建管理员账号
        try {
            require_once dirname(__DIR__) . '/core/helper.php';
            
            // 连接数据库
            $db_host = $this->getConfigValue('DB_HOST');
            $db_name = $this->getConfigValue('DB_NAME');
            $db_user = $this->getConfigValue('DB_USER');
            $db_pass = $this->getConfigValue('DB_PASS');
            $table_prefix = $this->getConfigValue('TABLE_PREFIX');
            
            $dsn = "mysql:host={$db_host};dbname={$db_name};charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ];
            
            $pdo = new PDO($dsn, $db_user, $db_pass, $options);
            
            // 检查管理员是否已存在
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM {$table_prefix}users WHERE username = :username");
            $stmt->execute(['username' => $admin_user]);
            $count = $stmt->fetchColumn();
            
            if ($count > 0) {
                return '管理员账号已存在';
            }
            
            // 创建管理员账号
            $hashed_password = password_hash($admin_pass, PASSWORD_BCRYPT, ['cost' => 12]);
            
            $stmt = $pdo->prepare("INSERT INTO {$table_prefix}users (username, password, role, status, theme_preference, created_at) VALUES (:username, :password, 'admin', 1, :theme, NOW())");
            $stmt->execute([
                'username' => $admin_user,
                'password' => $hashed_password,
                'theme' => $default_theme
            ]);
            
            // 更新安装状态
            $config = file_get_contents($this->configFile);
            $config = preg_replace("/define\('SYSTEM_INSTALLED',\s*false\);/", "define('SYSTEM_INSTALLED', true);", $config);
            file_put_contents($this->configFile, $config);
            
            // 创建安装锁文件
            file_put_contents($this->lockFile, date('Y-m-d H:i:s'));
            
            return true;
        } catch (Exception $e) {
            return '创建管理员账号失败: ' . $e->getMessage();
        }
    }
    
    /**
     * 获取配置值
     *
     * @param string $name 配置名
     * @return string 配置值
     */
    private function getConfigValue($name) {
        $config = file_get_contents($this->configFile);
        preg_match("/define\('{$name}',\s*'(.*?)'\);/", $config, $matches);
        return isset($matches[1]) ? $matches[1] : '';
    }
} 