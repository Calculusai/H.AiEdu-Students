<?php
/**
 * 管理员学生列表视图
 */
include_once VIEW_PATH . '/templates/header.php';
?>

<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="h3 mb-3"><i class="fas fa-users me-2"></i>学生管理</h1>
        </div>
        <div class="col-md-6 text-end">
            <a href="<?php echo site_url('admin/students/add'); ?>" class="btn btn-primary">
                <i class="fas fa-user-plus me-1"></i> 添加学生
            </a>
        </div>
    </div>
    
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">学生列表</h6>
        </div>
        <div class="card-body">
            <!-- 搜索表单 -->
            <form action="<?php echo site_url('admin/students'); ?>" method="get" class="mb-4">
                <div class="row g-3 align-items-center">
                    <div class="col-auto">
                        <input type="text" class="form-control" name="search" value="<?php echo htmlspecialchars($search ?? ''); ?>" placeholder="搜索学生姓名或学号...">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-1"></i> 搜索
                        </button>
                    </div>
                    <?php if (!empty($search)): ?>
                    <div class="col-auto">
                        <a href="<?php echo site_url('admin/students'); ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i> 清除筛选
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </form>
            
            <?php if (empty($students)): ?>
                <div class="alert alert-info">
                    <?php if (!empty($search)): ?>
                        未找到符合"<?php echo htmlspecialchars($search); ?>"的学生记录。
                    <?php else: ?>
                        暂无学生记录。
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>学号</th>
                                <th>姓名</th>
                                <th>班级</th>
                                <th>联系方式</th>
                                <th>注册日期</th>
                                <th>成就数量</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $student): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($student['id']); ?></td>
                                <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                                <td>
                                    <a href="<?php echo site_url('student/profile/' . $student['id']); ?>" target="_blank">
                                        <?php echo htmlspecialchars($student['name']); ?>
                                    </a>
                                </td>
                                <td><?php echo htmlspecialchars($student['class_name'] ?? '未设置'); ?></td>
                                <td><?php echo htmlspecialchars($student['contact'] ?? '未设置'); ?></td>
                                <td><?php echo date('Y-m-d', strtotime($student['created_at'])); ?></td>
                                <td>
                                    <a href="<?php echo site_url('admin/achievements?student_id=' . $student['id']); ?>">
                                        <?php echo htmlspecialchars($student['achievement_count']); ?>
                                    </a>
                                </td>
                                <td class="text-center">
                                    <a href="<?php echo site_url('admin/students/edit/' . $student['id']); ?>" class="btn btn-sm btn-primary me-1">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="<?php echo site_url('admin/achievements/add?student_id=' . $student['id']); ?>" class="btn btn-sm btn-success me-1" title="添加成就">
                                        <i class="fas fa-award"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- 分页 -->
                <?php if ($total_pages > 1): ?>
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center">
                        <?php if ($current_page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?php echo site_url('admin/students?page=' . ($current_page - 1) . (!empty($search) ? '&search=' . urlencode($search) : '')); ?>">
                                上一页
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $current_page - 2); $i <= min($total_pages, $current_page + 2); $i++): ?>
                        <li class="page-item <?php echo ($i == $current_page) ? 'active' : ''; ?>">
                            <a class="page-link" href="<?php echo site_url('admin/students?page=' . $i . (!empty($search) ? '&search=' . urlencode($search) : '')); ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                        <?php endfor; ?>
                        
                        <?php if ($current_page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?php echo site_url('admin/students?page=' . ($current_page + 1) . (!empty($search) ? '&search=' . urlencode($search) : '')); ?>">
                                下一页
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include_once VIEW_PATH . '/templates/footer.php'; ?> 