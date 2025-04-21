<?php
/**
 * 成就添加/编辑表单视图
 */
include_once VIEW_PATH . '/header.php';

// 判断是添加还是编辑
$isEdit = isset($achievement);
$formTitle = $isEdit ? '编辑成就' : '添加成就';
$formAction = $isEdit ? site_url('admin/achievements/edit/' . $achievement['id']) : site_url('admin/achievements/add');

// 如果是添加且有student_id参数，则在action URL中添加该参数
if (!$isEdit && isset($_GET['student_id']) && (int)$_GET['student_id'] > 0) {
    $formAction .= '?student_id=' . (int)$_GET['student_id'];
}

// 获取表单预填值
$student_id = $isEdit ? $achievement['student_id'] : (isset($_GET['student_id']) ? (int)$_GET['student_id'] : 0);
$title = $isEdit ? $achievement['title'] : '';
$description = $isEdit ? $achievement['description'] : '';
$achievement_type = $isEdit ? $achievement['achievement_type'] : (isset($_GET['type']) ? $_GET['type'] : '');
$score = $isEdit ? $achievement['score'] : '';
$certificate_no = $isEdit ? $achievement['certificate_no'] : '';
$issue_authority = $isEdit ? $achievement['issue_authority'] : '';
$achieved_date = $isEdit ? date('Y-m-d', strtotime($achievement['achieved_date'])) : date('Y-m-d');
$attachment = $isEdit ? $achievement['attachment'] : '';
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card glass animate-float">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h1 class="gradient-text mb-2 animate-pulse">
                                <i class="bi bi-award me-2"></i><?php echo $formTitle; ?>
                            </h1>
                            <p class="text-muted">记录和管理学生的成就信息</p>
                        </div>
                        <a href="<?php echo site_url('admin/achievements'); ?>" class="btn btn-outline-primary btn-shine">
                            <i class="bi bi-arrow-left me-1"></i> 返回列表
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card glass shadow-sm">
                <div class="card-header bg-transparent py-3 border-bottom-0">
                    <h5 class="gradient-text m-0">成就详细信息</h5>
                </div>
                <div class="card-body p-4">
                    <form action="<?php echo $formAction; ?>" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="student_id" class="form-label">学生 <span class="text-danger">*</span></label>
                                <select class="form-select custom-select rounded-pill" id="student_id" name="student_id" required>
                                    <option value="">-- 选择学生 --</option>
                                    <?php foreach ($students as $student): ?>
                                    <option value="<?php echo $student['id']; ?>" <?php echo ($student_id == $student['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($student['name']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="achievement_type" class="form-label">成就类型 <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <select class="form-select custom-select rounded-pill rounded-end-0" id="achievement_type_select">
                                        <option value="">-- 选择或输入新类型 --</option>
                                        <?php foreach ($types as $type): ?>
                                        <option value="<?php echo htmlspecialchars($type); ?>" <?php echo ($achievement_type == $type) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($type); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <input type="text" class="form-control custom-input rounded-pill rounded-start-0" id="achievement_type" name="achievement_type" value="<?php echo htmlspecialchars($achievement_type); ?>" required>
                                </div>
                                <div class="form-text">选择现有类型或输入新类型</div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="title" class="form-label">成就标题 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control custom-input rounded-pill" id="title" name="title" value="<?php echo htmlspecialchars($title); ?>" required>
                        </div>
                        
                        <div class="mb-4">
                            <label for="description" class="form-label">成就描述</label>
                            <textarea class="form-control custom-input rounded-4" id="description" name="description" rows="3"><?php echo htmlspecialchars($description); ?></textarea>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label for="score" class="form-label">分数/评级</label>
                                <input type="text" class="form-control custom-input rounded-pill" id="score" name="score" value="<?php echo htmlspecialchars($score); ?>">
                            </div>
                            
                            <div class="col-md-4">
                                <label for="certificate_no" class="form-label">证书编号</label>
                                <input type="text" class="form-control custom-input rounded-pill" id="certificate_no" name="certificate_no" value="<?php echo htmlspecialchars($certificate_no); ?>">
                            </div>
                            
                            <div class="col-md-4">
                                <label for="issue_authority" class="form-label">颁发机构</label>
                                <input type="text" class="form-control custom-input rounded-pill" id="issue_authority" name="issue_authority" value="<?php echo htmlspecialchars($issue_authority); ?>">
                            </div>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="achieved_date" class="form-label">获得日期 <span class="text-danger">*</span></label>
                                <input type="date" class="form-control custom-input rounded-pill" id="achieved_date" name="achieved_date" value="<?php echo htmlspecialchars($achieved_date); ?>" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="attachment" class="form-label">附件（证书扫描/照片）</label>
                                <input type="file" class="form-control custom-input rounded-pill" id="attachment" name="attachment">
                                <?php if ($isEdit && !empty($attachment)): ?>
                                <div class="mt-2">
                                    <span class="badge bg-info rounded-pill">
                                        <i class="bi bi-paperclip me-1"></i>当前附件
                                    </span>
                                    <a href="<?php echo site_url('uploads/' . $attachment); ?>" target="_blank" class="ms-2">
                                        <?php echo htmlspecialchars($attachment); ?>
                                    </a>
                                    <small class="text-muted">(上传新文件将替换当前附件)</small>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="mt-4 text-end">
                            <a href="<?php echo site_url('admin/achievements'); ?>" class="btn btn-outline-secondary me-2">
                                取消
                            </a>
                            <button type="submit" class="btn btn-primary btn-shine">
                                <i class="bi bi-save me-1"></i>
                                <?php echo $isEdit ? '保存修改' : '添加成就'; ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card glass shadow-sm mb-4 animate-float-delay">
                <div class="card-header bg-transparent py-3 border-bottom-0">
                    <h5 class="gradient-text m-0">帮助指南</h5>
                </div>
                <div class="card-body p-4">
                    <div class="mb-4">
                        <div class="d-flex align-items-center mb-2">
                            <div class="icon-circle bg-info text-white">
                                <i class="bi bi-info"></i>
                            </div>
                            <h6 class="ms-2 mb-0">成就说明</h6>
                        </div>
                        <p class="small text-muted">
                            成就记录用于展示学生在编程学习过程中获得的各种证书、奖项、考试成绩等成就。
                        </p>
                    </div>
                    
                    <div class="mb-4">
                        <div class="d-flex align-items-center mb-2">
                            <div class="icon-circle bg-primary text-white">
                                <i class="bi bi-card-list"></i>
                            </div>
                            <h6 class="ms-2 mb-0">必填项说明</h6>
                        </div>
                        <ul class="small text-muted ps-4">
                            <li>学生：选择成就所属的学生</li>
                            <li>成就类型：选择或创建新的成就类型</li>
                            <li>成就标题：简明扼要的成就名称</li>
                            <li>获得日期：获得该成就的日期</li>
                        </ul>
                    </div>
                    
                    <div>
                        <div class="d-flex align-items-center mb-2">
                            <div class="icon-circle bg-warning text-white">
                                <i class="bi bi-lightbulb"></i>
                            </div>
                            <h6 class="ms-2 mb-0">小贴士</h6>
                        </div>
                        <p class="small text-muted mb-0">
                            添加详细的描述和附件可以让成就展示更加丰富和直观，提高学生的成就感和自信心。
                        </p>
                    </div>
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
.icon-circle {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}
.animate-float-delay {
    animation-delay: 0.2s;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 设置成就类型选择器联动
    var typeSelect = document.getElementById('achievement_type_select');
    var typeInput = document.getElementById('achievement_type');
    
    typeSelect.addEventListener('change', function() {
        if (this.value) {
            typeInput.value = this.value;
        }
    });
});
</script>

<?php include_once VIEW_PATH . '/footer.php'; ?> 