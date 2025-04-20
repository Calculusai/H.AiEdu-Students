<?php
/**
 * 面包屑导航组件
 * 
 * 使用方法:
 * $breadcrumbs = [
 *    ['title' => '首页', 'url' => site_url()],
 *    ['title' => '管理控制台', 'url' => site_url('admin/dashboard')],
 *    ['title' => '当前页面', 'active' => true]
 * ];
 * include VIEW_PATH . '/templates/breadcrumb.php';
 */
?>

<?php if (isset($breadcrumbs) && is_array($breadcrumbs) && count($breadcrumbs) > 0): ?>
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb bg-light p-2 rounded">
        <?php foreach($breadcrumbs as $index => $item): ?>
            <?php if(isset($item['active']) && $item['active']): ?>
                <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($item['title']); ?></li>
            <?php else: ?>
                <li class="breadcrumb-item">
                    <a href="<?php echo $item['url']; ?>"><?php echo htmlspecialchars($item['title']); ?></a>
                </li>
            <?php endif; ?>
        <?php endforeach; ?>
    </ol>
</nav>
<?php endif; ?> 