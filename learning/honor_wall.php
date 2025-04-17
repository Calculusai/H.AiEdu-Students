<?php
require_once '../includes/config.php';
require_once '../includes/db.php';

$pageTitle = '学生荣誉墙';

// 页面特定样式
$extraStyles = <<<HTML
<style>
    .page-header {
        background: linear-gradient(135deg, var(--primary), var(--orange));
        color: white;
        padding: var(--space-md) 0;
        margin-bottom: var(--space-lg);
        border-radius: var(--radius-xl);
        text-align: center;
    }
    
    .filter-section {
        margin-bottom: var(--space-md);
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: var(--space-sm);
        padding: var(--space-sm);
        background-color: white;
        border-radius: var(--radius-lg);
        box-shadow: 0 5px 15px var(--shadow-color);
    }
    
    .filter-label {
        font-weight: 600;
        color: var(--text-primary);
    }
    
    .filter-select {
        padding: 8px 12px;
        border-radius: var(--radius-md);
        border: 2px solid var(--border-color);
        margin-right: var(--space-sm);
    }
    
    .honor-wall {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: var(--space-md);
    }
    
    .honor-card {
        background: linear-gradient(135deg, var(--card-bg), #FFFBF5);
        border-radius: var(--radius-xl);
        padding: var(--space-md);
        box-shadow: 0 10px 20px var(--shadow-color);
        transition: all 0.3s ease;
        text-align: center;
        position: relative;
        overflow: hidden;
    }
    
    .honor-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 5px;
        background: linear-gradient(to right, var(--orange), var(--primary));
    }
    
    .honor-title {
        font-size: var(--font-h3);
        color: var(--primary);
        margin-bottom: var(--space-xs);
    }
    
    .honor-meta {
        color: var(--text-secondary);
        font-size: var(--font-small);
        margin-bottom: var(--space-sm);
    }
    
    .pagination {
        display: flex;
        justify-content: center;
        margin-top: var(--space-lg);
    }
    
    .pagination a {
        display: inline-block;
        padding: 8px 16px;
        margin: 0 5px;
        border-radius: var(--radius-md);
        background-color: white;
        color: var(--primary);
        text-decoration: none;
        transition: all 0.3s ease;
        border: 2px solid var(--border-color);
    }
    
    .pagination a:hover, .pagination a.active {
        background-color: var(--primary);
        color: white;
        border-color: var(--primary);
    }
    
    .no-results {
        text-align: center;
        padding: var(--space-lg);
        background-color: white;
        border-radius: var(--radius-lg);
        box-shadow: 0 5px 15px var(--shadow-color);
    }
    
    .student-name-filter {
        flex: 1;
        margin: 0 10px;
    }
    
    .student-name-filter input {
        width: 100%;
        padding: 8px 12px;
        border-radius: var(--radius-md);
        border: 2px solid var(--border-color);
    }
</style>
HTML;

include TEMPLATES_PATH . '/header.php';

// 获取数据库连接
$db = Database::getInstance();

// 设置分页参数
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

// 获取筛选条件
$typeFilter = isset($_GET['type']) ? $_GET['type'] : '';
$yearFilter = isset($_GET['year']) ? (int)$_GET['year'] : 0;
$studentFilter = isset($_GET['student']) ? (int)$_GET['student'] : 0;
$studentNameFilter = isset($_GET['student_name']) ? trim($_GET['student_name']) : '';

// 构建查询条件
$params = [];
$where = [];

if (!empty($typeFilter)) {
    $where[] = "h.honor_type = ?";
    $params[] = $typeFilter;
}

if (!empty($yearFilter)) {
    $where[] = "YEAR(h.honor_date) = ?";
    $params[] = $yearFilter;
}

if (!empty($studentFilter)) {
    $where[] = "h.student_id = ?";
    $params[] = $studentFilter;
}

if (!empty($studentNameFilter)) {
    $where[] = "s.name LIKE ?";
    $params[] = '%' . $studentNameFilter . '%';
}

$whereClause = '';
if (!empty($where)) {
    $whereClause = "WHERE " . implode(" AND ", $where);
}

// 准备命名参数
$namedParams = [];
if (!empty($params)) {
    foreach ($params as $index => $value) {
        $paramName = ':param' . ($index + 1);
        $namedParams[$paramName] = $value;
        
        // 替换SQL中的问号为命名参数
        $whereClause = preg_replace('/\?/', $paramName, $whereClause, 1);
    }
}

// 获取荣誉记录总数
$countQuery = "SELECT COUNT(*) FROM honors h JOIN students s ON h.student_id = s.id $whereClause";
$countStmt = $db->prepare($countQuery);

// 绑定命名参数
if (!empty($namedParams)) {
    foreach ($namedParams as $param => $value) {
        $countStmt->bindValue($param, $value);
    }
}

$countStmt->execute();
$totalRecords = $countStmt->fetchColumn();

// 计算总页数
$totalPages = ceil($totalRecords / $perPage);

// 获取荣誉记录数据
$query = "SELECT h.*, s.name as student_name 
          FROM honors h 
          JOIN students s ON h.student_id = s.id 
          $whereClause 
          ORDER BY h.honor_date DESC 
          LIMIT :offset, :perPage";

$stmt = $db->prepare($query);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->bindParam(':perPage', $perPage, PDO::PARAM_INT);

// 绑定WHERE条件参数
if (!empty($namedParams)) {
    foreach ($namedParams as $param => $value) {
        $stmt->bindValue($param, $value);
    }
}

$stmt->execute();
$honors = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 获取所有荣誉类型
$typeQuery = "SELECT DISTINCT honor_type FROM honors ORDER BY honor_type";
$stmt = $db->query($typeQuery);
$honorTypes = $stmt->fetchAll(PDO::FETCH_COLUMN);

// 获取所有年份
$yearQuery = "SELECT DISTINCT YEAR(honor_date) as year FROM honors ORDER BY year DESC";
$stmt = $db->query($yearQuery);
$years = $stmt->fetchAll(PDO::FETCH_COLUMN);

// 获取所有获奖学生
$studentQuery = "SELECT DISTINCT s.id, s.name 
                FROM students s 
                INNER JOIN honors h ON s.id = h.student_id 
                ORDER BY s.name";
$stmt = $db->query($studentQuery);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- 页面标题 -->
<div class="page-header">
    <div class="container">
        <h1>学生荣誉墙</h1>
        <p>展示我们优秀学生的成就和荣誉</p>
    </div>
</div>

<!-- 筛选区域 -->
<div class="filter-section">
    <span class="filter-label">筛选：</span>
    
    <select class="filter-select form-select" id="typeFilter">
        <option value="">所有类型</option>
        <?php foreach ($honorTypes as $type): ?>
            <option value="<?php echo $type; ?>" <?php echo ($typeFilter === $type) ? 'selected' : ''; ?>>
                <?php echo $type; ?>
            </option>
        <?php endforeach; ?>
    </select>
    
    <select class="filter-select form-select" id="yearFilter">
        <option value="">所有年份</option>
        <?php foreach ($years as $year): ?>
            <option value="<?php echo $year; ?>" <?php echo ($yearFilter === (int)$year) ? 'selected' : ''; ?>>
                <?php echo $year; ?>
            </option>
        <?php endforeach; ?>
    </select>

    <select class="filter-select form-select" id="studentFilter">
        <option value="">所有学生</option>
        <?php foreach ($students as $student): ?>
            <option value="<?php echo $student['id']; ?>" <?php echo ($studentFilter === (int)$student['id']) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($student['name']); ?>
            </option>
        <?php endforeach; ?>
    </select>
    
    <div class="student-name-filter">
        <input type="text" id="studentNameFilter" class="form-control" placeholder="输入学生姓名" value="<?php echo htmlspecialchars($studentNameFilter); ?>">
    </div>
    
    <button class="btn btn-primary" id="filterBtn" style="margin-left: auto;">应用筛选</button>
    <button class="btn btn-secondary" id="resetBtn" style="margin-left: 5px;">重置筛选</button>
</div>

<!-- 荣誉墙 -->
<div class="honor-wall" id="honorWall">
    <?php if (empty($honors)): ?>
        <div class="no-results">
            <h3>暂无荣誉记录</h3>
            <p>目前还没有符合条件的荣誉记录。</p>
        </div>
    <?php else: ?>
        <?php foreach ($honors as $honor): ?>
            <div class="honor-card" data-type="<?php echo $honor['honor_type']; ?>" data-year="<?php echo date('Y', strtotime($honor['honor_date'])); ?>">
                <h3 class="honor-title"><?php echo htmlspecialchars($honor['honor_title']); ?></h3>
                <div class="honor-meta">
                    <span class="badge badge-primary">获得者: <?php echo htmlspecialchars($honor['student_name']); ?></span>
                    <span class="badge badge-blue"><?php echo date('Y-m-d', strtotime($honor['honor_date'])); ?></span>
                    <span class="badge badge-orange"><?php echo htmlspecialchars($honor['honor_type']); ?></span>
                </div>
                <p><?php echo htmlspecialchars($honor['description'] ?: ''); ?></p>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- 分页 -->
<?php if ($totalPages > 1): ?>
<div class="pagination">
    <?php if ($page > 1): ?>
        <a href="?page=<?php echo ($page - 1); ?><?php echo !empty($typeFilter) ? '&type=' . urlencode($typeFilter) : ''; ?><?php echo !empty($yearFilter) ? '&year=' . $yearFilter : ''; ?><?php echo !empty($studentFilter) ? '&student=' . $studentFilter : ''; ?><?php echo !empty($studentNameFilter) ? '&student_name=' . urlencode($studentNameFilter) : ''; ?>">&laquo; 上一页</a>
    <?php endif; ?>
    
    <?php
    $startPage = max(1, $page - 2);
    $endPage = min($totalPages, $page + 2);
    
    for ($i = $startPage; $i <= $endPage; $i++):
    ?>
        <a href="?page=<?php echo $i; ?><?php echo !empty($typeFilter) ? '&type=' . urlencode($typeFilter) : ''; ?><?php echo !empty($yearFilter) ? '&year=' . $yearFilter : ''; ?><?php echo !empty($studentFilter) ? '&student=' . $studentFilter : ''; ?><?php echo !empty($studentNameFilter) ? '&student_name=' . urlencode($studentNameFilter) : ''; ?>" <?php echo ($page === $i) ? 'class="active"' : ''; ?>><?php echo $i; ?></a>
    <?php endfor; ?>
    
    <?php if ($page < $totalPages): ?>
        <a href="?page=<?php echo ($page + 1); ?><?php echo !empty($typeFilter) ? '&type=' . urlencode($typeFilter) : ''; ?><?php echo !empty($yearFilter) ? '&year=' . $yearFilter : ''; ?><?php echo !empty($studentFilter) ? '&student=' . $studentFilter : ''; ?><?php echo !empty($studentNameFilter) ? '&student_name=' . urlencode($studentNameFilter) : ''; ?>">下一页 &raquo;</a>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php
// 页面特定脚本
$extraScripts = <<<HTML
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const filterBtn = document.getElementById('filterBtn');
        const resetBtn = document.getElementById('resetBtn');
        const typeFilter = document.getElementById('typeFilter');
        const yearFilter = document.getElementById('yearFilter');
        const studentFilter = document.getElementById('studentFilter');
        const studentNameFilter = document.getElementById('studentNameFilter');
        
        filterBtn.addEventListener('click', function() {
            const selectedType = typeFilter.value;
            const selectedYear = yearFilter.value;
            const selectedStudent = studentFilter.value;
            const studentName = studentNameFilter.value.trim();
            
            let url = window.location.pathname;
            let queryParams = [];
            
            if (selectedType) {
                queryParams.push('type=' + encodeURIComponent(selectedType));
            }
            
            if (selectedYear) {
                queryParams.push('year=' + encodeURIComponent(selectedYear));
            }
            
            if (selectedStudent) {
                queryParams.push('student=' + encodeURIComponent(selectedStudent));
            }
            
            if (studentName) {
                queryParams.push('student_name=' + encodeURIComponent(studentName));
            }
            
            if (queryParams.length > 0) {
                url += '?' + queryParams.join('&');
            }
            
            window.location.href = url;
        });
        
        resetBtn.addEventListener('click', function() {
            window.location.href = window.location.pathname;
        });
        
        // 当选择了学生下拉菜单时，清空学生姓名输入框
        studentFilter.addEventListener('change', function() {
            if (this.value) {
                studentNameFilter.value = '';
            }
        });
        
        // 当输入学生姓名时，清空学生下拉菜单选择
        studentNameFilter.addEventListener('input', function() {
            if (this.value.trim()) {
                studentFilter.value = '';
            }
        });
        
        // 按下回车键时触发筛选
        studentNameFilter.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                filterBtn.click();
            }
        });
    });
</script>
HTML;

include TEMPLATES_PATH . '/footer.php';
?> 