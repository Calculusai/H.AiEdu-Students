<?php
/**
 * 数据库连接类
 */
class Database {
    private $conn;
    private $host;
    private $dbname;
    private $user;
    private $password;
    private $charset;

    public function __construct() {
        $config = require_once dirname(__DIR__) . '/db_config.php';
        $this->host = $config['host'];
        $this->dbname = $config['dbname'];
        $this->user = $config['user'];
        $this->password = $config['password'];
        $this->charset = $config['charset'];
    }

    /**
     * 连接数据库
     */
    public function connect() {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset={$this->charset}";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $this->conn = new PDO($dsn, $this->user, $this->password, $options);
            return $this->conn;
        } catch (PDOException $e) {
            echo "数据库连接失败: " . $e->getMessage();
            exit;
        }
    }
    
    /**
     * 获取数据库连接实例
     */
    public static function getInstance() {
        static $instance = null;
        if ($instance === null) {
            $instance = new self();
        }
        return $instance->connect();
    }
} 