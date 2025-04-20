<?php
/**
 * 管理员成就列表视图
 */
include_once VIEW_PATH . '/header.php';

// 设置面包屑导航
$breadcrumbs = [
    ['title' => '首页', 'url' => site_url()],
    ['title' => '管理控制台', 'url' => site_url('admin/dashboard')],
    ['title' => '成就管理', 'active' => true]
];
include_once VIEW_PATH . '/templates/breadcrumb.php';
?>

<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="h3 mb-3"><i class="bi bi-award me-2"></i>成就管理</h1>
        </div>
        <div class="col-md-6 text-end">
            <a href="<?php echo site_url('admin/achievements/add'); ?>" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i> 添加成就
            </a>
        </div>
    </div>
    
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 fw-bold text-primary">成就列表</h6>
        </div>
        <div class="card-body">
            <!-- 搜索和筛选表单 -->
            <form action="<?php echo site_url('admin/achievements'); ?>" method="get" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <input type="text" class="form-control" name="search" value="<?php echo htmlspecialchars($search ?? ''); ?>" placeholder="搜索成就标题或描述...">
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="type">
                            <option value="">- 所有类型 -</option>
                            <?php foreach ($types as $type_option): ?>
                            <option value="<?php echo htmlspecialchars($type_option); ?>" <?php echo ($type == $type_option) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($type_option); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="student_id">
                            <option value="">- 所有学生 -</option>
                            <?php foreach ($students as $student_option): ?>
                            <option value="<?php echo $student_option['id']; ?>" <?php echo ($student_id == $student_option['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($student_option['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <div class="d-grid d-md-flex">
                            <button type="submit" class="btn btn-primary me-md-2">
                                <i class="bi bi-search me-1"></i> 搜索
                            </button>
                            <?php if (!empty($search) || !empty($type) || !empty($student_id)): ?>
                            <a href="<?php echo site_url('admin/achievements'); ?>" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-1"></i> 清除筛选
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </form>
            
            <?php if (empty($achievements)): ?>
                <div class="alert alert-info">
                    <?php if (!empty($search) || !empty($type) || !empty($student_id)): ?>
                        未找到符合条件的成就记录。
                    <?php else: ?>
                        暂无成就记录。
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>学生</th>
                                <th>成就标题</th>
                                <th>类型</th>
                                <th>分数/评级</th>
                                <th>证书编号</th>
                                <th>获得日期</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($achievements as $achievement): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($achievement['id']); ?></td>
                                <td><?php echo htmlspecialchars($achievement['student_name']); ?></td>
                                <td><?php echo htmlspecialchars($achievement['title']); ?></td>
                                <td><span class="badge bg-primary"><?php echo htmlspecialchars($achievement['achievement_type']); ?></span></td>
                                <td><?php echo htmlspecialchars($achievement['score'] ?: '无'); ?></td>
                                <td><?php echo htmlspecialchars($achievement['certificate_no'] ?: '无'); ?></td>
                                <td><?php echo date('Y-m-d', strtotime($achievement['achieved_date'])); ?></td>
                                <td class="text-center">
                                    <a href="<?php echo site_url('admin/achievements/edit/' . $achievement['id']); ?>" class="btn btn-sm btn-primary me-1" title="编辑">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger" title="删除" 
                                            onclick="confirmDelete(<?php echo $achievement['id']; ?>, '<?php echo htmlspecialchars(addslashes($achievement['title'])); ?>')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- 分页 -->
                <?php if (isset($pagination)): ?>
                <div class="mt-4">
                    <?php echo $pagination; ?>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- 删除确认模态框 -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">确认删除</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="关闭"></button>
            </div>
            <div class="modal-body">
                确定要删除成就 "<span id="achievementTitle"></span>" 吗？此操作不可恢复。
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                <form id="deleteForm" action="" method="post" style="display: inline;">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    <button type="submit" class="btn btn-danger">确认删除</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(id, title) {
    document.getElementById('achievementTitle').textContent = title;
    document.getElementById('deleteForm').action = '<?php echo site_url('admin/achievements/delete/'); ?>' + id;
    
    var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    deleteModal.show();
}
</script>

<?php include_once VIEW_PATH . '/footer.php'; ?> 