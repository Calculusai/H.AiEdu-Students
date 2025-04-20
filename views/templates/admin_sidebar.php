<?php if (!file_exists($file)): ?>
<!-- 模板文件不存在 -->
<?php else: ?>
<!-- 添加数据统计菜单项 -->
<li class="nav-item <?php echo $active_page === 'admin_statistics' ? 'active' : ''; ?>">
    <a class="nav-link" href="<?php echo site_url('admin/statistics'); ?>">
        <i class="fas fa-fw fa-chart-bar"></i>
        <span>数据统计</span>
    </a>
</li>
<?php endif; ?> 