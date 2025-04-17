<?php
if (!isset($db)) {
    require_once '../includes/config.php';
    
    // 检查用户是否已登录
    if (!isLoggedIn()) {
        exit('未授权访问');
    }
    
    $userId = $_SESSION['user_id'];
    $db = Database::getInstance();
    
    // 获取用户信息
    $userInfo = [];
    try {
        $stmt = $db->prepare("SELECT a.*, s.* FROM accounts a 
                            LEFT JOIN students s ON a.id = s.account_id 
                            WHERE a.id = ?");
        $stmt->execute([$userId]);
        $userInfo = $stmt->fetch();
    } catch (PDOException $e) {
        error_log("用户信息查询错误: " . $e->getMessage());
    }
}

// 获取考试类别和级别，用于下拉选择
$categories = [];
try {
    $stmt = $db->prepare("SELECT * FROM exam_categories ORDER BY category_name");
    $stmt->execute();
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("考试类别查询错误: " . $e->getMessage());
}

// 获取学生ID
$studentId = null;
if (isset($userInfo['id'])) {
    try {
        $stmt = $db->prepare("SELECT id FROM students WHERE account_id = ?");
        $stmt->execute([$userId]);
        $studentRow = $stmt->fetch();
        if ($studentRow) {
            $studentId = $studentRow['id'];
        }
    } catch (PDOException $e) {
        error_log("学生ID查询错误: " . $e->getMessage());
    }
}
?>

<!-- 添加考试成绩模态框 -->
<div class="modal fade" id="addScoreModal" tabindex="-1" role="dialog" aria-labelledby="addScoreModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addScoreModalLabel">添加考试成绩</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?php if (!$studentId): ?>
                    <div class="alert alert-warning">
                        您需要先完善学生信息才能添加考试成绩。
                    </div>
                <?php else: ?>
                    <form id="addScoreForm" action="../exam_scores/save_score.php" method="post">
                        <input type="hidden" name="student_id" value="<?php echo $studentId; ?>">
                        
                        <div class="form-group">
                            <label for="category_id">考试类别</label>
                            <select class="form-control custom-select" id="category_id" name="category_id" required>
                                <option value="">-- 选择考试类别 --</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['category_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="level_id">考试级别</label>
                            <select class="form-control custom-select" id="level_id" name="level_id" required disabled>
                                <option value="">-- 请先选择考试类别 --</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="score">分数</label>
                            <input type="number" class="form-control" id="score" name="score" min="0" max="100" step="0.01" required placeholder="请输入0-100之间的分数">
                        </div>
                        
                        <div class="form-group">
                            <label for="exam_date">考试日期</label>
                            <input type="date" class="form-control" id="exam_date" name="exam_date" required value="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </form>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
                <?php if ($studentId): ?>
                    <button type="button" class="btn btn-primary" id="saveScoreBtn">保存</button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.modal-header {
    background: linear-gradient(to right, #ff7676, #ff7d7d);
    color: white;
    border-radius: 5px 5px 0 0;
}

.modal-header .close {
    color: white;
    opacity: 0.8;
}

.modal-header .close:hover {
    opacity: 1;
}

.modal-footer .btn-primary {
    background: linear-gradient(to right, #ff7676, #ff9f7f);
    border: none;
}

.modal-footer .btn-primary:hover {
    background: linear-gradient(to right, #ff6767, #ff8f6f);
}

.modal-footer .btn-secondary {
    background-color: #f8f9fa;
    color: #666;
    border: 1px solid #ddd;
}

.modal-footer .btn-secondary:hover {
    background-color: #e9ecef;
}

.custom-select {
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='6' viewBox='0 0 8 6'%3E%3Cpath fill='%23666' d='M0 0h8L4 6z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 0.75rem center;
    background-size: 8px 6px;
    padding-right: 2rem;
}

.form-control:focus {
    border-color: #ff9f7f;
    box-shadow: 0 0 0 0.2rem rgba(255, 159, 127, 0.25);
}

/* 修复表单元素样式 */
.modal-body .form-group {
    margin-bottom: 1.5rem;
}

.modal-body label {
    display: block;
    font-weight: 500;
    color: #333;
    margin-bottom: 0.5rem;
}

.modal-body select.form-control,
.modal-body input.form-control {
    height: calc(1.5em + 0.75rem + 2px);
    padding: 0.375rem 0.75rem;
    border: 1px solid #ced4da;
    border-radius: 0.25rem;
    width: 100%;
}

.modal-body select.form-control:disabled {
    background-color: #e9ecef;
    cursor: not-allowed;
}

/* 防止标签文本重复 */
.modal-body label {
    font-size: 14px;
    line-height: 1.2;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
</style>

<!-- 模态框的JavaScript -->
<script>
    $(document).ready(function() {
        // 确保下拉框样式正确
        $('#category_id, #level_id').addClass('custom-select');
        
        // 当类别选择变化时，获取对应的级别
        $('#category_id').change(function() {
            const categoryId = $(this).val();
            const levelSelect = $('#level_id');
            
            // 重置级别选择框
            levelSelect.html('<option value="">-- 加载中... --</option>');
            levelSelect.prop('disabled', true);
            
            if (categoryId) {
                // 异步加载级别数据
                $.ajax({
                    url: '../exam_scores/get_levels.php',
                    type: 'GET',
                    data: { category_id: categoryId },
                    dataType: 'json',
                    success: function(data) {
                        levelSelect.empty();
                        levelSelect.append('<option value="">-- 选择考试级别 --</option>');
                        if (data.length > 0) {
                            $.each(data, function(index, level) {
                                levelSelect.append('<option value="' + level.id + '">' + level.level_name + '</option>');
                            });
                            levelSelect.prop('disabled', false);
                        } else {
                            levelSelect.html('<option value="">-- 无可用级别 --</option>');
                        }
                    },
                    error: function() {
                        levelSelect.html('<option value="">-- 加载失败 --</option>');
                    }
                });
            } else {
                levelSelect.html('<option value="">-- 请先选择考试类别 --</option>');
            }
        });
        
        // 提交表单
        $('#saveScoreBtn').click(function() {
            if ($('#addScoreForm')[0].checkValidity()) {
                const formData = $('#addScoreForm').serialize();
                
                $.ajax({
                    url: '../exam_scores/save_score.php',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            // 显示成功消息
                            alert('考试成绩添加成功！');
                            // 关闭模态框
                            $('#addScoreModal').modal('hide');
                            // 刷新页面
                            location.reload();
                        } else {
                            // 显示错误消息
                            alert('添加失败：' + response.message);
                        }
                    },
                    error: function() {
                        alert('服务器错误，请稍后重试。');
                    }
                });
            } else {
                // 触发浏览器的表单验证
                $('#addScoreForm')[0].reportValidity();
            }
        });
        
        // 确保模态框正确初始化
        $('#addScoreModal').on('shown.bs.modal', function() {
            // 重置表单
            $('#addScoreForm')[0].reset();
            $('#level_id').prop('disabled', true).html('<option value="">-- 请先选择考试类别 --</option>');
        });
    });
</script> 