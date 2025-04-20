<?php
/**
 * 基础模型类
 */
class Model {
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    
    /**
     * 构造函数
     *
     * @param string $table 表名（不含前缀）
     */
    public function __construct($table) {
        global $db;
        $this->db = $db;
        $this->table = TABLE_PREFIX . $table;
    }
    
    /**
     * 根据主键获取单条记录
     *
     * @param int $id 主键值
     * @return array|bool 记录数组或false
     */
    public function find($id) {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id";
        return $this->db->query($sql, ['id' => $id]);
    }
    
    /**
     * 获取所有记录
     *
     * @param string $orderBy 排序字段
     * @param string $order 排序方向
     * @return array 记录数组
     */
    public function all($orderBy = null, $order = 'ASC') {
        $sql = "SELECT * FROM {$this->table}";
        
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy} {$order}";
        }
        
        return $this->db->queryAll($sql);
    }
    
    /**
     * 根据条件获取记录
     *
     * @param array $conditions 条件数组，格式为 ['字段名' => '值']
     * @param string $orderBy 排序字段
     * @param string $order 排序方向
     * @return array 记录数组
     */
    public function where($conditions, $orderBy = null, $order = 'ASC') {
        $sql = "SELECT * FROM {$this->table}";
        $params = [];
        
        if (!empty($conditions)) {
            $whereClauses = [];
            foreach ($conditions as $field => $value) {
                $whereClauses[] = "{$field} = :{$field}";
                $params[$field] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $whereClauses);
        }
        
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy} {$order}";
        }
        
        return $this->db->queryAll($sql, $params);
    }
    
    /**
     * 插入记录
     *
     * @param array $data 数据数组，格式为 ['字段名' => '值']
     * @return int|bool 新增记录的ID或false
     */
    public function insert($data) {
        $fields = array_keys($data);
        $placeholders = array_map(function($field) {
            return ":{$field}";
        }, $fields);
        
        $sql = "INSERT INTO {$this->table} (" . implode(', ', $fields) . ") 
                VALUES (" . implode(', ', $placeholders) . ")";
        
        $result = $this->db->exec($sql, $data);
        
        if ($result) {
            return $this->db->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * 更新记录
     *
     * @param int $id 主键值
     * @param array $data 数据数组，格式为 ['字段名' => '值']
     * @return bool 是否成功
     */
    public function update($id, $data) {
        $setClause = [];
        foreach ($data as $field => $value) {
            $setClause[] = "{$field} = :{$field}";
        }
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $setClause) . " WHERE {$this->primaryKey} = :id";
        
        $data['id'] = $id;
        
        return $this->db->exec($sql, $data);
    }
    
    /**
     * 删除记录
     *
     * @param int $id 主键值
     * @return bool 是否成功
     */
    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id";
        return $this->db->exec($sql, ['id' => $id]);
    }
    
    /**
     * 获取记录总数
     *
     * @param array $conditions 条件数组，格式为 ['字段名' => '值']
     * @return int 记录数量
     */
    public function count($conditions = []) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        $params = [];
        
        if (!empty($conditions)) {
            $whereClauses = [];
            foreach ($conditions as $field => $value) {
                $whereClauses[] = "{$field} = :{$field}";
                $params[$field] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $whereClauses);
        }
        
        $result = $this->db->query($sql, $params);
        return $result ? (int)$result['total'] : 0;
    }
    
    /**
     * 获取分页数据
     *
     * @param int $page 当前页码
     * @param int $perPage 每页记录数
     * @param array $conditions 条件数组
     * @param string $orderBy 排序字段
     * @param string $order 排序方向
     * @return array 包含数据和分页信息的数组
     */
    public function paginate($page = 1, $perPage = 20, $conditions = [], $orderBy = null, $order = 'ASC') {
        $sql = "SELECT * FROM {$this->table}";
        $params = [];
        
        if (!empty($conditions)) {
            $whereClauses = [];
            foreach ($conditions as $field => $value) {
                $whereClauses[] = "{$field} = :{$field}";
                $params[$field] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $whereClauses);
        }
        
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy} {$order}";
        }
        
        // 获取总记录数
        $total = $this->count($conditions);
        
        // 计算总页数
        $totalPages = ceil($total / $perPage);
        $page = min($page, max(1, $totalPages));
        
        // 计算偏移量
        $offset = ($page - 1) * $perPage;
        
        // 添加分页限制
        $sql .= " LIMIT {$offset}, {$perPage}";
        
        // 获取数据
        $data = $this->db->queryAll($sql, $params);
        
        return [
            'data' => $data,
            'total' => $total,
            'total_pages' => $totalPages,
            'current_page' => $page,
            'per_page' => $perPage
        ];
    }
} 