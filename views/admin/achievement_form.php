<?php
/**
 * 成就添加/编辑表单视图
 */
include_once VIEW_PATH . '/header.php';

// 判断是添加还是编辑
$isEdit = isset($achievement);
$formTitle = $isEdit ? '编辑成就' : '添加成就';
$formAction = $isEdit ? site_url('admin/achievements/edit/' . $achievement['id']) : site_url('admin/achievements/add');

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

<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="h3 mb-3"><i class="bi bi-award me-2"></i><?php echo $formTitle; ?></h1>
        </div>
        <div class="col-md-6 text-end">
            <a href="<?php echo site_url('admin/achievements'); ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> 返回列表
            </a>
        </div>
    </div>
    
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 fw-bold text-primary">成就信息</h6>
        </div>
        <div class="card-body">
            <form action="<?php echo $formAction; ?>" method="post" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="student_id" class="form-label">学生 <span class="text-danger">*</span></label>
                        <select class="form-select" id="student_id" name="student_id" required>
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
                            <select class="form-select" id="achievement_type_select">
                                <option value="">-- 选择或输入新类型 --</option>
                                <?php foreach ($types as $type): ?>
                                <option value="<?php echo htmlspecialchars($type); ?>" <?php echo ($achievement_type == $type) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($type); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <input type="text" class="form-control" id="achievement_type" name="achievement_type" value="<?php echo htmlspecialchars($achievement_type); ?>" required>
                        </div>
                        <div class="form-text">选择现有类型或输入新类型</div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="title" class="form-label">成就标题 <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($title); ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="description" class="form-label">成就描述</label>
                    <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($description); ?></textarea>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="score" class="form-label">分数/评级</label>
                        <input type="text" class="form-control" id="score" name="score" value="<?php echo htmlspecialchars($score); ?>">
                    </div>
                    
                    <div class="col-md-4">
                        <label for="certificate_no" class="form-label">证书编号</label>
                        <input type="text" class="form-control" id="certificate_no" name="certificate_no" value="<?php echo htmlspecialchars($certificate_no); ?>">
                    </div>
                    
                    <div class="col-md-4">
                        <label for="issue_authority" class="form-label">颁发机构</label>
                        <input type="text" class="form-control" id="issue_authority" name="issue_authority" value="<?php echo htmlspecialchars($issue_authority); ?>">
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="achieved_date" class="form-label">获得日期 <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="achieved_date" name="achieved_date" value="<?php echo htmlspecialchars($achieved_date); ?>" required>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="attachment" class="form-label">附件（证书扫描/照片）</label>
                        <input type="file" class="form-control" id="attachment" name="attachment">
                        <?php if ($isEdit && !empty($attachment)): ?>
                        <div class="form-text">
                            当前附件: <a href="<?php echo asset_url('uploads/' . $attachment); ?>" target="_blank"><?php echo htmlspecialchars($attachment); ?></a>
                            <small class="text-muted">(上传新文件将替换当前附件)</small>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="mt-4 text-end">
                    <a href="<?php echo site_url('admin/achievements'); ?>" class="btn btn-secondary me-2">取消</a>
                    <button type="submit" class="btn btn-primary">
                        <?php echo $isEdit ? '保存修改' : '添加成就'; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

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