<?php
/**
 * 学生成就管理页面
 */
include_once VIEW_PATH . '/header.php';

// 设置面包屑导航
$breadcrumbs = [
    ['title' => '首页', 'url' => site_url()],
    ['title' => '管理控制台', 'url' => site_url('admin/dashboard')],
    ['title' => '学生管理', 'url' => site_url('admin/students')],
    ['title' => $student['name'] . ' 的成就', 'active' => true]
];
include_once VIEW_PATH . '/templates/breadcrumb.php';
?>

<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">
            <i class="fas fa-award me-2"></i><?php echo htmlspecialchars($student['name']); ?> 的成就
            <small class="text-muted">(<?php echo htmlspecialchars($student['student_id']); ?>)</small>
        </h1>
        <div>
            <a href="<?php echo site_url('admin/students/edit/' . $student['id']); ?>" class="btn btn-outline-secondary me-2">
                <i class="fas fa-user-edit me-1"></i> 编辑学生信息
            </a>
            <a href="<?php echo site_url('admin/achievements/add'); ?>" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> 添加成就
            </a>
        </div>
    </div>
    
    <!-- 筛选工具栏 -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="get" action="<?php echo site_url('admin/students/achievements/' . $student['id']); ?>" class="row g-3 align-items-end">
                <!-- 类型筛选 -->
                <div class="col-md-4">
                    <label for="type" class="form-label">成就类型</label>
                    <select class="form-select" id="type" name="type">
                        <option value="">全部类型</option>
                        <?php foreach ($types as $t): ?>
                        <option value="<?php echo htmlspecialchars($t); ?>" <?php echo $type === $t ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($t); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- 搜索框 -->
                <div class="col-md-6">
                    <label for="search" class="form-label">搜索成就</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           placeholder="输入成就标题或描述..." value="<?php echo htmlspecialchars($search ?? ''); ?>">
                </div>
                
                <!-- 搜索按钮 -->
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-1"></i> 筛选
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <?php if (isset($_SESSION['flash_message'])): ?>
    <div class="alert alert-<?php echo $_SESSION['flash_type'] ?? 'info'; ?> alert-dismissible fade show" role="alert">
        <?php echo $_SESSION['flash_message']; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php 
    unset($_SESSION['flash_message']);
    unset($_SESSION['flash_type']);
    endif; 
    ?>
    
    <!-- 成就列表 -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">成就列表</h6>
            <span class="badge bg-primary"><?php echo count($achievements); ?> 条记录</span>
        </div>
        <div class="card-body">
            <?php if (empty($achievements)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i> 该学生暂无成就记录
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>成就标题</th>
                            <th>类型</th>
                            <th>获得日期</th>
                            <th>分数</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($achievements as $item): ?>
                        <tr>
                            <td><?php echo $item['id']; ?></td>
                            <td>
                                <?php echo htmlspecialchars($item['title']); ?>
                                <?php if (!empty($item['description'])): ?>
                                <p class="small text-muted mb-0"><?php echo htmlspecialchars(mb_substr($item['description'], 0, 50)) . (mb_strlen($item['description']) > 50 ? '...' : ''); ?></p>
                                <?php endif; ?>
                            </td>
                            <td><span class="badge bg-secondary"><?php echo htmlspecialchars($item['achievement_type']); ?></span></td>
                            <td><?php echo date('Y-m-d', strtotime($item['achieved_date'])); ?></td>
                            <td><?php echo !empty($item['score']) ? $item['score'] : '-'; ?></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="<?php echo site_url('admin/achievements/edit/' . $item['id']); ?>" class="btn btn-outline-primary" data-bs-toggle="tooltip" title="编辑成就">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $item['id']; ?>" title="删除成就">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                                
                                <!-- 删除确认模态框 -->
                                <div class="modal fade" id="deleteModal<?php echo $item['id']; ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?php echo $item['id']; ?>" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="deleteModalLabel<?php echo $item['id']; ?>">确认删除</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>您确定要删除成就 "<?php echo htmlspecialchars($item['title']); ?>" 吗？</p>
                                                <p class="text-danger"><strong>注意：</strong> 此操作无法撤销！</p>
                                            </div>
                                            <div class="modal-footer">
                                                <form action="<?php echo site_url('admin/achievements/delete/' . $item['id']); ?>" method="post">
                                                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">取消</button>
                                                    <button type="submit" class="btn btn-danger">确认删除</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
            
            <!-- 分页 -->
            <?php if (!empty($pagination)): ?>
            <div class="mt-4">
                <?php echo $pagination; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 初始化工具提示
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>

<?php include_once VIEW_PATH . '/footer.php'; ?> 