<?php
/**
 * 管理员编辑学生视图
 */
include_once VIEW_PATH . '/header.php';
?>

<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo site_url('admin/dashboard'); ?>">首页</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo site_url('admin/students'); ?>">学生管理</a></li>
                    <li class="breadcrumb-item active">编辑学生</li>
                </ol>
            </nav>
            <h1 class="h3 mb-3">
                <i class="fas fa-user-edit me-2"></i>编辑学生信息
                <small class="text-muted"><?php echo htmlspecialchars($student['name']); ?> (<?php echo htmlspecialchars($student['student_id']); ?>)</small>
            </h1>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">学生信息</h6>
                    <span class="badge bg-<?php echo $student['active'] ? 'success' : 'danger'; ?>">
                        <?php echo $student['active'] ? '正常' : '已禁用'; ?>
                    </span>
                </div>
                <div class="card-body">
                    <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo $error_message; ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (isset($success_message)): ?>
                    <div class="alert alert-success" role="alert">
                        <?php echo $success_message; ?>
                    </div>
                    <?php endif; ?>
                    
                    <form id="editStudentForm" action="<?php echo site_url('admin/students/edit/' . $student['id']); ?>" method="post" class="needs-validation" novalidate>
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                        <input type="hidden" name="student_id" value="<?php echo htmlspecialchars($student['student_id']); ?>">
                        <input type="hidden" name="original_email" value="<?php echo htmlspecialchars($student['email']); ?>">
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="display_student_id" class="form-label">学号</label>
                                    <input type="text" class="form-control" id="display_student_id" value="<?php echo htmlspecialchars($student['student_id']); ?>" readonly>
                                    <div class="form-text">学号无法修改，如需更改请删除后重新添加学生</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name" class="form-label">姓名 <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name" placeholder="输入姓名" required
                                           value="<?php echo htmlspecialchars($student['name']); ?>">
                                    <div class="invalid-feedback">
                                        请输入学生姓名
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="class_name" class="form-label">班级</label>
                                    <input type="text" class="form-control" id="class_name" name="class_name" placeholder="输入班级名称"
                                           value="<?php echo htmlspecialchars($student['class_name']); ?>"
                                           list="class_list">
                                    <datalist id="class_list">
                                        <?php if (!empty($classes)): ?>
                                            <?php foreach ($classes as $class): ?>
                                            <option value="<?php echo htmlspecialchars($class); ?>">
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </datalist>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="contact" class="form-label">联系方式</label>
                                    <input type="text" class="form-control" id="contact" name="contact" placeholder="输入手机号码"
                                           value="<?php echo htmlspecialchars($student['contact']); ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label for="email" class="form-label">邮箱</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="输入邮箱地址"
                                   value="<?php echo htmlspecialchars($student['email']); ?>">
                            <div class="invalid-feedback">
                                请输入有效的邮箱地址
                            </div>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label for="notes" class="form-label">备注</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="输入备注信息（可选）"><?php echo htmlspecialchars($student['notes']); ?></textarea>
                        </div>
                        
                        <div class="card bg-light mb-3">
                            <div class="card-header">
                                <h6 class="mb-0">账号设置</h6>
                            </div>
                            <div class="card-body">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" value="1" id="active" name="active" <?php echo $student['active'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="active">
                                        账号有效
                                    </label>
                                    <div class="form-text">禁用后学生将无法登录系统</div>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label for="password" class="form-label">重置密码</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="password" name="password" placeholder="留空表示不修改密码">
                                        <button class="btn btn-outline-secondary" type="button" id="generatePassword">
                                            <i class="fas fa-key me-1"></i> 生成密码
                                        </button>
                                    </div>
                                    <div class="form-text">如需重置密码，请在此输入新密码，否则请留空</div>
                                </div>
                                
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" value="1" id="require_change_password" name="require_change_password">
                                    <label class="form-check-label" for="require_change_password">
                                        要求学生下次登录时修改密码
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group text-end">
                            <a href="<?php echo site_url('admin/students'); ?>" class="btn btn-outline-secondary me-2">
                                <i class="fas fa-arrow-left me-1"></i> 返回列表
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> 保存更改
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">学生状态</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6>账号信息</h6>
                        <div class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">注册时间</span>
                            <span><?php echo date('Y-m-d H:i', strtotime($student['created_at'])); ?></span>
                        </div>
                        <div class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">最后登录</span>
                            <span><?php echo ($student['last_login'] ?? null) ? date('Y-m-d H:i', strtotime($student['last_login'])) : '从未登录'; ?></span>
                        </div>
                        <div class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">登录次数</span>
                            <span><?php echo $student['login_count'] ?? 0; ?></span>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="mb-3">
                        <h6>学习统计</h6>
                        <div class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">已完成任务</span>
                            <span><?php echo $stats['completed_tasks'] ?? 0; ?></span>
                        </div>
                        <div class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">获得成就</span>
                            <span><?php echo $stats['achievements_count'] ?? 0; ?></span>
                        </div>
                        <div class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">累计得分</span>
                            <span><?php echo $stats['total_points'] ?? 0; ?></span>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="d-grid gap-2">
                        <a href="<?php echo site_url('admin/students/achievements/' . $student['id']); ?>" class="btn btn-outline-info">
                            <i class="fas fa-award me-1"></i> 管理成就
                        </a>
                        
                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteStudentModal">
                            <i class="fas fa-trash-alt me-1"></i> 删除学生
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">最新活动</h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($recent_activities)): ?>
                    <div class="timeline small">
                        <?php foreach ($recent_activities as $activity): ?>
                        <div class="timeline-item">
                            <div class="timeline-date"><?php echo date('m-d H:i', strtotime($activity['created_at'])); ?></div>
                            <div class="timeline-content">
                                <div class="timeline-title"><?php echo htmlspecialchars($activity['title']); ?></div>
                                <div class="timeline-text text-muted"><?php echo htmlspecialchars($activity['description']); ?></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <p class="text-muted">暂无活动记录</p>
                    <?php endif; ?>
                    
                    <?php if (!empty($recent_activities)): ?>
                    <div class="text-center mt-3">
                        <a href="<?php echo site_url('admin/students/activities/' . $student['id']); ?>" class="btn btn-sm btn-link">查看全部活动</a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 删除学生模态框 -->
<div class="modal fade" id="deleteStudentModal" tabindex="-1" aria-labelledby="deleteStudentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?php echo site_url('admin/students/delete/' . $student['id']); ?>" method="post">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteStudentModalLabel">确认删除学生</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i> 警告：此操作将永久删除学生 <strong><?php echo htmlspecialchars($student['name']); ?> (<?php echo htmlspecialchars($student['student_id']); ?>)</strong> 的所有数据，且无法恢复！
                    </div>
                    <p>删除将包括：</p>
                    <ul>
                        <li>学生账号信息</li>
                        <li>学习记录和成就</li>
                        <li>所有活动记录</li>
                    </ul>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" value="1" id="confirm_delete" name="confirm_delete" required>
                        <label class="form-check-label" for="confirm_delete">
                            我已了解此操作的风险并确认删除
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-danger">确认删除</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 表单验证
    const form = document.getElementById('editStudentForm');
    
    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        
        form.classList.add('was-validated');
    });
    
    // 生成随机密码
    const generatePasswordBtn = document.getElementById('generatePassword');
    const passwordInput = document.getElementById('password');
    
    generatePasswordBtn.addEventListener('click', function() {
        // 生成8位随机密码，包含数字和字母
        const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
        let password = '';
        
        for (let i = 0; i < 8; i++) {
            password += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        
        passwordInput.value = password;
        
        // 自动勾选要求修改密码
        document.getElementById('require_change_password').checked = true;
    });
    
    // 密码输入时自动勾选"要求修改密码"
    passwordInput.addEventListener('input', function() {
        if (passwordInput.value) {
            document.getElementById('require_change_password').checked = true;
        }
    });
});
</script>

<style>
.timeline {
    position: relative;
    padding-left: 1.5rem;
}
.timeline:before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e9ecef;
}
.timeline-item {
    position: relative;
    padding-bottom: 1.5rem;
}
.timeline-item:last-child {
    padding-bottom: 0;
}
.timeline-date {
    font-size: 0.75rem;
    color: #6c757d;
    margin-bottom: 0.25rem;
}
.timeline-content {
    position: relative;
    padding-left: 1rem;
}
.timeline-content:before {
    content: '';
    position: absolute;
    left: -1.5rem;
    top: 0.25rem;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: #4e73df;
}
.timeline-title {
    font-weight: 600;
    margin-bottom: 0.25rem;
}
</style>

<?php include_once VIEW_PATH . '/footer.php'; ?> 