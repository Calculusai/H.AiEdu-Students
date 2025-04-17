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

// 获取荣誉类型列表
$honorTypes = [];
try {
    $stmt = $db->prepare("SELECT DISTINCT honor_type FROM honors ORDER BY honor_type");
    $stmt->execute();
    $honorTypes = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    error_log("荣誉类型查询错误: " . $e->getMessage());
}
?>

<!-- 添加荣誉证书模态框 -->
<div class="modal fade" id="addHonorModal" tabindex="-1" role="dialog" aria-labelledby="addHonorModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addHonorModalLabel">添加荣誉证书</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?php if (!$studentId): ?>
                    <div class="alert alert-warning">
                        您还未完善学生信息，无法添加荣誉证书。请先完善个人信息。
                    </div>
                <?php else: ?>
                    <form id="addHonorForm" action="../honors/save_honor.php" method="post">
                        <input type="hidden" name="student_id" value="<?php echo $studentId; ?>">
                        
                        <div class="form-group">
                            <label for="honor_title">荣誉名称</label>
                            <input type="text" class="form-control" id="honor_title" name="honor_title" required placeholder="请输入荣誉名称">
                        </div>
                        
                        <div class="form-group">
                            <label for="honor_type">荣誉类型</label>
                            <select class="form-control custom-select" id="honor_type" name="honor_type" required>
                                <option value="">-- 选择荣誉类型 --</option>
                                <?php if (!empty($honorTypes)): ?>
                                    <?php foreach ($honorTypes as $type): ?>
                                        <option value="<?php echo htmlspecialchars($type); ?>"><?php echo htmlspecialchars($type); ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                <option value="比赛获奖">比赛获奖</option>
                                <option value="考级证书">考级证书</option>
                                <option value="竞赛证书">竞赛证书</option>
                                <option value="学习证书">学习证书</option>
                                <option value="其他证书">其他证书</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="honor_date">获得日期</label>
                            <input type="date" class="form-control" id="honor_date" name="honor_date" required value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="description">荣誉描述</label>
                            <textarea class="form-control" id="description" name="description" rows="3" placeholder="请输入荣誉描述（可选）"></textarea>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
                <?php if ($studentId): ?>
                    <button type="submit" form="addHonorForm" class="btn btn-primary">保存荣誉</button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // 自定义荣誉类型
        $('#honor_type').on('change', function() {
            if ($(this).val() === 'custom') {
                var customType = prompt("请输入自定义荣誉类型:");
                if (customType) {
                    // 添加新的选项
                    var newOption = new Option(customType, customType, true, true);
                    $(this).append(newOption);
                } else {
                    $(this).val(''); // 如果用户取消，则清空选择
                }
            }
        });
        
        // 提交表单
        $('#addHonorForm').on('submit', function(e) {
            e.preventDefault();
            
            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert(response.message);
                        $('#addHonorModal').modal('hide');
                        // 可以选择重新加载页面以显示新添加的荣誉
                        window.location.reload();
                    } else {
                        alert(response.message || '保存失败，请重试');
                    }
                },
                error: function() {
                    alert('发生错误，请稍后重试');
                }
            });
        });
    });
</script> 