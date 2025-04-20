<?php
/**
 * 系统环境检测模板
 */
?>

<div class="card mb-4">
    <div class="card-header">
        <h2 class="card-title">系统环境检测</h2>
    </div>
    <div class="card-body">
        <p class="lead">安装程序将检查您的服务器环境是否满足运行要求。</p>
        
        <table class="table">
            <thead>
                <tr>
                    <th>检测项目</th>
                    <th>要求</th>
                    <th>当前状态</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($requirements as $requirement): ?>
                <tr class="<?php echo $requirement['status'] ? 'table-success' : 'table-danger'; ?>">
                    <td><?php echo htmlspecialchars($requirement['name']); ?></td>
                    <td>必须</td>
                    <td class="<?php echo $requirement['status'] ? 'text-success' : 'text-danger'; ?>">
                        <?php if ($requirement['status']): ?>
                            <i class="bi bi-check-circle-fill me-1"></i>
                        <?php else: ?>
                            <i class="bi bi-x-circle-fill me-1"></i>
                        <?php endif; ?>
                        <?php echo htmlspecialchars($requirement['value']); ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <?php if (!$requirements_met): ?>
        <div class="alert alert-danger mt-3">
            <h5 class="alert-heading">您的服务器环境不满足系统要求</h5>
            <p>请解决上述标记为不满足的项目，然后刷新此页面重新检测。</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="d-flex justify-content-between">
    <a href="index.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> 返回
    </a>
    
    <?php if ($requirements_met): ?>
    <a href="index.php?step=database" class="btn btn-primary">
        下一步 <i class="bi bi-arrow-right"></i>
    </a>
    <?php else: ?>
    <button class="btn btn-primary" disabled>
        下一步 <i class="bi bi-arrow-right"></i>
    </button>
    <?php endif; ?>
</div> 