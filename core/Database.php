<?php
/**
 * 数据库连接和操作类
 */
class Database {
    private $connection;
    private $stmt;
    public $error;
    
    /**
     * 构造函数，建立数据库连接
     */
    public function __construct() {
        // 如果系统未安装，不建立连接
        if (!SYSTEM_INSTALLED) {
            return;
        }
        
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_PERSISTENT => true,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ];
            
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            error_log("数据库连接错误: " . $this->error);
            
            if (DEBUG_MODE) {
                die("数据库连接失败: " . $this->error);
            } else {
                die("数据库连接失败，请检查配置或联系管理员。");
            }
        }
    }
    
    /**
     * 准备SQL语句
     *
     * @param string $sql SQL语句
     * @return bool 是否成功
     */
    public function prepare($sql) {
        // 检查连接是否存在
        if (!$this->connection) {
            $this->error = "数据库连接不存在";
            error_log("SQL准备错误: " . $this->error);
            return false;
        }
        
        try {
            $this->stmt = $this->connection->prepare($sql);
            return true;
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            error_log("SQL准备错误: " . $this->error);
            return false;
        }
    }
    
    /**
     * 绑定参数
     *
     * @param string $param 参数名
     * @param mixed $value 参数值
     * @param int $type 参数类型
     * @return bool 是否成功
     */
    public function bind($param, $value, $type = null) {
        // 检查stmt是否存在
        if (!$this->stmt) {
            $this->error = "SQL语句未准备";
            error_log("参数绑定错误: " . $this->error);
            return false;
        }
        
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }
        
        return $this->stmt->bindValue($param, $value, $type);
    }
    
    /**
     * 执行SQL语句
     *
     * @return bool 是否成功
     */
    public function execute() {
        // 检查stmt是否存在
        if (!$this->stmt) {
            $this->error = "SQL语句未准备";
            error_log("SQL执行错误: " . $this->error);
            return false;
        }
        
        try {
            return $this->stmt->execute();
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            error_log("SQL执行错误: " . $this->error);
            return false;
        }
    }
    
    /**
     * 获取单条记录
     *
     * @return mixed 记录或false
     */
    public function fetch() {
        // 检查stmt是否存在
        if (!$this->stmt) {
            $this->error = "SQL语句未准备";
            error_log("获取记录错误: " . $this->error);
            return false;
        }
        
        $this->execute();
        return $this->stmt->fetch();
    }
    
    /**
     * 获取所有记录
     *
     * @return array 记录集
     */
    public function fetchAll() {
        // 检查stmt是否存在
        if (!$this->stmt) {
            $this->error = "SQL语句未准备";
            error_log("获取记录错误: " . $this->error);
            return [];
        }
        
        $this->execute();
        return $this->stmt->fetchAll();
    }
    
    /**
     * 获取记录数
     *
     * @return int 记录数
     */
    public function rowCount() {
        // 检查stmt是否存在
        if (!$this->stmt) {
            $this->error = "SQL语句未准备";
            error_log("获取记录数错误: " . $this->error);
            return 0;
        }
        
        return $this->stmt->rowCount();
    }
    
    /**
     * 获取最后插入ID
     *
     * @return int 最后插入ID
     */
    public function lastInsertId() {
        // 检查连接是否存在
        if (!$this->connection) {
            $this->error = "数据库连接不存在";
            error_log("获取ID错误: " . $this->error);
            return 0;
        }
        
        return $this->connection->lastInsertId();
    }
    
    /**
     * 开始事务
     *
     * @return bool 是否成功
     */
    public function beginTransaction() {
        // 检查连接是否存在
        if (!$this->connection) {
            $this->error = "数据库连接不存在";
            error_log("事务错误: " . $this->error);
            return false;
        }
        
        return $this->connection->beginTransaction();
    }
    
    /**
     * 提交事务
     *
     * @return bool 是否成功
     */
    public function commit() {
        // 检查连接是否存在
        if (!$this->connection) {
            $this->error = "数据库连接不存在";
            error_log("事务错误: " . $this->error);
            return false;
        }
        
        return $this->connection->commit();
    }
    
    /**
     * 回滚事务
     *
     * @return bool 是否成功
     */
    public function rollBack() {
        // 检查连接是否存在
        if (!$this->connection) {
            $this->error = "数据库连接不存在";
            error_log("事务错误: " . $this->error);
            return false;
        }
        
        return $this->connection->rollBack();
    }
    
    /**
     * 直接查询单条记录
     *
     * @param string $sql SQL语句
     * @param array $params 参数
     * @return mixed 记录或false
     */
    public function query($sql, $params = []) {
        if (!$this->prepare($sql)) {
            return false;
        }
        
        if (!empty($params)) {
            foreach ($params as $key => $value) {
                if (is_int($key)) {
                    $this->bind($key + 1, $value);
                } else {
                    $this->bind($key, $value);
                }
            }
        }
        
        return $this->fetch();
    }
    
    /**
     * 直接查询多条记录
     *
     * @param string $sql SQL语句
     * @param array $params 参数
     * @return array 记录集
     */
    public function queryAll($sql, $params = []) {
        if (!$this->prepare($sql)) {
            return [];
        }
        
        if (!empty($params)) {
            foreach ($params as $key => $value) {
                if (is_int($key)) {
                    $this->bind($key + 1, $value);
                } else {
                    $this->bind($key, $value);
                }
            }
        }
        
        return $this->fetchAll();
    }
    
    /**
     * 直接执行SQL
     *
     * @param string $sql SQL语句
     * @param array $params 参数
     * @return bool 是否成功
     */
    public function exec($sql, $params = []) {
        if (!$this->prepare($sql)) {
            return false;
        }
        
        if (!empty($params)) {
            foreach ($params as $key => $value) {
                if (is_int($key)) {
                    $this->bind($key + 1, $value);
                } else {
                    $this->bind($key, $value);
                }
            }
        }
        
        return $this->execute();
    }
    
    /**
     * 获取错误信息
     *
     * @return array 错误信息
     */
    public function getErrorInfo() {
        if ($this->stmt) {
            return $this->stmt->errorInfo();
        } else if ($this->connection) {
            return $this->connection->errorInfo();
        }
        
        return ["无法获取错误信息", "", $this->error];
    }
    
    /**
     * 关闭连接
     */
    public function close() {
        $this->connection = null;
    }
} 