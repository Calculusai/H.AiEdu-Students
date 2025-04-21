<?php
/**
 * 管理员编辑学生视图
 */
include_once VIEW_PATH . '/header.php';

// 设置面包屑导航
$breadcrumbs = [
    ['title' => '首页', 'url' => site_url()],
    ['title' => '管理控制台', 'url' => site_url('admin/dashboard')],
    ['title' => '学生管理', 'url' => site_url('admin/students')],
    ['title' => '编辑学生', 'active' => true]
];
include_once VIEW_PATH . '/templates/breadcrumb.php';
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card glass animate-float">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h1 class="gradient-text mb-2 animate-pulse"><i class="fas fa-user-edit me-2"></i>编辑学生</h1>
                            <p class="lead text-muted">修改学生 <strong><?php echo htmlspecialchars($student['name']); ?></strong> 的信息</p>
                        </div>
                        <a href="<?php echo site_url('admin/students'); ?>" class="btn btn-outline-primary btn-shine">
                            <i class="fas fa-arrow-left me-1"></i> 返回列表
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card glass shadow-sm mb-4">
                <div class="card-header bg-transparent py-3 border-bottom-0">
                    <h5 class="gradient-text m-0">学生信息</h5>
                </div>
                <div class="card-body p-4">
                    <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger rounded-4 border-0" role="alert">
                        <div class="d-flex">
                            <i class="fas fa-exclamation-circle fa-lg me-3 mt-1"></i>
                            <div><?php echo $error_message; ?></div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (isset($success_message)): ?>
                    <div class="alert alert-success rounded-4 border-0" role="alert">
                        <div class="d-flex">
                            <i class="fas fa-check-circle fa-lg me-3 mt-1"></i>
                            <div><?php echo $success_message; ?></div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <form id="editStudentForm" action="<?php echo site_url('admin/students/edit/' . $student['id']); ?>" method="post" class="needs-validation" novalidate>
                        <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                        <input type="hidden" name="student_id" value="<?php echo htmlspecialchars($student['student_id']); ?>">
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="student_id_display" class="form-label">学号</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-transparent border-end-0">
                                            <i class="fas fa-id-card"></i>
                                        </span>
                                        <input type="text" class="form-control custom-input rounded-pill ps-0 border-start-0" id="student_id_display" value="<?php echo htmlspecialchars($student['student_id']); ?>" readonly>
                                    </div>
                                    <div class="form-text">学号不可修改</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name" class="form-label">姓名 <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-transparent border-end-0">
                                            <i class="fas fa-user"></i>
                                        </span>
                                        <input type="text" class="form-control custom-input rounded-pill ps-0 border-start-0" id="name" name="name" placeholder="输入姓名" required
                                               value="<?php echo htmlspecialchars($student['name']); ?>">
                                    </div>
                                    <div class="invalid-feedback">请输入学生姓名</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="class_name" class="form-label">班级</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-transparent border-end-0">
                                            <i class="fas fa-users"></i>
                                        </span>
                                        <input type="text" class="form-control custom-input rounded-pill ps-0 border-start-0" id="class_name" name="class_name" placeholder="输入班级名称"
                                               value="<?php echo htmlspecialchars($student['class_name']); ?>"
                                               list="class_list">
                                    </div>
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
                                    <div class="input-group">
                                        <span class="input-group-text bg-transparent border-end-0">
                                            <i class="fas fa-phone"></i>
                                        </span>
                                        <input type="text" class="form-control custom-input rounded-pill ps-0 border-start-0" id="contact" name="contact" placeholder="输入手机号码"
                                               value="<?php echo htmlspecialchars($student['contact']); ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group mb-4">
                            <label for="email" class="form-label">邮箱</label>
                            <div class="input-group">
                                <span class="input-group-text bg-transparent border-end-0">
                                    <i class="fas fa-envelope"></i>
                                </span>
                                <input type="email" class="form-control custom-input rounded-pill ps-0 border-start-0" id="email" name="email" placeholder="输入邮箱地址"
                                       value="<?php echo htmlspecialchars($student['email']); ?>">
                            </div>
                            <div class="invalid-feedback">请输入有效的邮箱地址</div>
                        </div>
                        
                        <div class="form-group mb-4">
                            <label for="notes" class="form-label">备注</label>
                            <textarea class="form-control custom-input rounded-4" id="notes" name="notes" rows="3" placeholder="输入备注信息（可选）"><?php echo htmlspecialchars($student['notes']); ?></textarea>
                        </div>
                        
                        <div class="card glass bg-light-opacity mb-4 border-0 rounded-4">
                            <div class="card-header bg-transparent border-bottom-0">
                                <h6 class="mb-0 gradient-text-light"><i class="fas fa-key me-2"></i>账号设置</h6>
                            </div>
                            <div class="card-body">
                                <div class="form-group mb-3">
                                    <label for="new_password" class="form-label">重置密码</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control custom-input rounded-pill rounded-end-0" id="new_password" name="new_password" placeholder="留空表示不修改密码">
                                        <button class="btn btn-outline-primary rounded-pill rounded-start-0" type="button" id="generatePasswordBtn">
                                            <i class="fas fa-random me-1"></i> 生成密码
                                        </button>
                                    </div>
                                    <div class="form-text">如果不需要修改密码，请留空</div>
                                </div>
                                
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="require_password_change" name="require_password_change" value="1"
                                           <?php echo (isset($student['require_password_change']) && $student['require_password_change'] ? 'checked' : ''); ?>>
                                    <label class="form-check-label" for="require_password_change">
                                        要求学生下次登录时修改密码
                                    </label>
                                </div>
                                
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="active" name="active" value="1"
                                           <?php echo (!isset($student['status']) || $student['status'] == 1 ? 'checked' : ''); ?>>
                                    <label class="form-check-label" for="active">
                                        账号状态：启用
                                    </label>
                                    <div class="form-text text-danger">取消勾选将禁用该学生账号，禁用后学生将无法登录系统</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group text-end">
                            <a href="<?php echo site_url('admin/students'); ?>" class="btn btn-outline-secondary me-2">
                                <i class="fas fa-times me-1"></i> 取消
                            </a>
                            <button type="submit" class="btn btn-primary btn-shine">
                                <i class="fas fa-save me-1"></i> 保存更改
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card glass shadow-sm mb-4 animate-float-delay">
                <div class="card-header bg-transparent py-3 border-bottom-0 d-flex justify-content-between align-items-center">
                    <h5 class="gradient-text m-0"><i class="fas fa-award me-2"></i>学生成就</h5>
                    <a href="<?php echo site_url('admin/students/achievements/' . $student['id']); ?>" class="btn btn-sm btn-outline-primary rounded-pill">
                        <i class="fas fa-cog me-1"></i> 管理成就
                    </a>
                </div>
                <div class="card-body p-4">
                    <?php if (empty($student_achievements)): ?>
                    <div class="text-center py-4">
                        <div class="avatar-circle bg-warning-soft mx-auto mb-3">
                            <i class="fas fa-award fa-2x text-warning"></i>
                        </div>
                        <h6 class="mb-2">尚未获得成就</h6>
                        <p class="text-muted small">该学生目前还没有获得任何成就。</p>
                        <a href="<?php echo site_url('admin/students/achievements/' . $student['id']); ?>" class="btn btn-sm btn-gradient btn-shine mt-2">
                            <i class="fas fa-plus me-1"></i> 添加成就
                        </a>
                    </div>
                    <?php else: ?>
                    <div class="achievements-grid">
                        <?php foreach ($student_achievements as $achievement): ?>
                        <div class="achievement-item animate-hover">
                            <div class="achievement-icon">
                                <i class="<?php echo htmlspecialchars($achievement['icon']); ?>"></i>
                            </div>
                            <div class="achievement-info">
                                <h6 class="mb-0"><?php echo htmlspecialchars($achievement['title']); ?></h6>
                                <small class="text-muted">
                                    <?php echo date('Y-m-d', strtotime($achievement['achieved_at'])); ?>
                                </small>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if (count($student_achievements) > 5): ?>
                    <div class="text-center mt-3">
                        <a href="<?php echo site_url('admin/students/achievements/' . $student['id']); ?>" class="btn btn-sm btn-outline-primary rounded-pill">
                            查看全部 <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                    <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card glass shadow-sm mb-4 animate-float-delay-2">
                <div class="card-header bg-transparent py-3 border-bottom-0">
                    <h5 class="gradient-text m-0"><i class="fas fa-history me-2"></i>活动记录</h5>
                </div>
                <div class="card-body p-4">
                    <?php if (empty($activity_logs)): ?>
                    <div class="text-center py-4">
                        <div class="avatar-circle bg-info-soft mx-auto mb-3">
                            <i class="fas fa-history fa-2x text-info"></i>
                        </div>
                        <h6 class="mb-2">没有活动记录</h6>
                        <p class="text-muted small">该学生暂时没有任何活动记录。</p>
                    </div>
                    <?php else: ?>
                    <div class="timeline">
                        <?php foreach ($activity_logs as $log): ?>
                        <div class="timeline-item">
                            <div class="timeline-dot"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1 small"><?php echo htmlspecialchars($log['action']); ?></h6>
                                <div class="text-muted smaller">
                                    <i class="far fa-clock me-1"></i> <?php echo date('Y-m-d H:i', strtotime($log['created_at'])); ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if (count($activity_logs) > 5): ?>
                    <div class="text-center mt-3">
                        <a href="<?php echo site_url('admin/activity_logs?student_id=' . $student['id']); ?>" class="btn btn-sm btn-outline-primary rounded-pill">
                            查看全部 <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                    <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.custom-input, .custom-select {
    border: 1px solid rgba(0,0,0,0.1);
    padding: 0.6rem 1rem;
    transition: all 0.3s ease;
}
.custom-input:focus, .custom-select:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.25rem rgba(var(--primary-rgb), 0.25);
}
.bg-light-opacity {
    background-color: rgba(240, 242, 245, 0.5);
}
.icon-circle {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}
.avatar-circle {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}
.bg-primary-soft {
    background-color: rgba(var(--primary-rgb), 0.1);
}
.bg-warning-soft {
    background-color: rgba(255, 193, 7, 0.1);
}
.bg-info-soft {
    background-color: rgba(13, 202, 240, 0.1);
}
.gradient-text-light {
    background: linear-gradient(45deg, var(--primary-color), #6f42c1);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    color: transparent;
}
.btn-gradient {
    background: linear-gradient(45deg, var(--primary-color), #6f42c1);
    border: none;
    color: white;
}
.btn-gradient:hover {
    background: linear-gradient(45deg, #2e59d9, #5a34a5);
    color: white;
    transform: translateY(-2px);
}
.animate-float-delay {
    animation-delay: 0.2s;
}
.animate-float-delay-2 {
    animation-delay: 0.4s;
}
.form-check-input:checked {
    background-color: var(--primary-color);
    border-color: var(--primary-color);
}
.form-switch .form-check-input {
    transition: background-position 0.25s ease-in-out, background-color 0.25s ease-in-out;
}
.achievements-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 10px;
}
.achievement-item {
    display: flex;
    align-items: center;
    padding: 12px;
    border-radius: 12px;
    background-color: rgba(255, 255, 255, 0.5);
    transition: all 0.3s ease;
}
.achievement-item:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
}
.achievement-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 10px;
    background: linear-gradient(45deg, var(--primary-color), #6f42c1);
    color: white;
}
.achievement-info {
    flex: 1;
}
.timeline {
    position: relative;
    padding-left: 25px;
}
.timeline::before {
    content: '';
    position: absolute;
    left: 5px;
    top: 5px;
    bottom: 5px;
    width: 2px;
    background: linear-gradient(to bottom, var(--primary-color), rgba(var(--primary-rgb), 0.1));
}
.timeline-item {
    position: relative;
    margin-bottom: 16px;
}
.timeline-dot {
    position: absolute;
    left: -25px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: linear-gradient(45deg, var(--primary-color), #6f42c1);
    top: 6px;
}
.timeline-content {
    padding-bottom: 5px;
}
</style>

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
    const generatePasswordBtn = document.getElementById('generatePasswordBtn');
    const passwordInput = document.getElementById('new_password');
    
    if (generatePasswordBtn) {
        generatePasswordBtn.addEventListener('click', function () {
            // 生成8位随机密码，包含数字和字母
            const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
            let password = '';
            
            for (let i = 0; i < 8; i++) {
                password += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            
            passwordInput.value = password;
        });
    }
    
    // 确保Bootstrap已加载
    if (typeof bootstrap !== 'undefined') {
        // 初始化Bootstrap组件
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    } else {
        console.warn('Bootstrap未加载，某些功能可能无法正常工作');
    }
});
</script>

<?php include_once VIEW_PATH . '/footer.php'; ?> 