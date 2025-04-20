<?php
/**
 * 系统设置视图
 */
include_once VIEW_PATH . '/header.php';

// 设置面包屑导航
$breadcrumbs = [
    ['title' => '首页', 'url' => site_url()],
    ['title' => '管理控制台', 'url' => site_url('admin/dashboard')],
    ['title' => '系统设置', 'active' => true]
];
include_once VIEW_PATH . '/templates/breadcrumb.php';
?>

<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="h3 mb-3"><i class="bi bi-gear me-2"></i>系统设置</h1>
        </div>
        <div class="col-md-6 text-end">
            <button type="button" class="btn btn-outline-secondary me-2" data-bs-toggle="modal" data-bs-target="#resetSettingsModal">
                <i class="bi bi-arrow-counterclockwise me-1"></i> 重置为默认
            </button>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">设置分类</h6>
                </div>
                <div class="list-group list-group-flush" id="settings-nav" role="tablist">
                    <a class="list-group-item list-group-item-action active" id="general-tab" data-bs-toggle="list" href="#general" role="tab" aria-controls="general">
                        <i class="bi bi-house me-2"></i> 基本设置
                    </a>
                    <a class="list-group-item list-group-item-action" id="content-tab" data-bs-toggle="list" href="#content" role="tab" aria-controls="content">
                        <i class="bi bi-card-text me-2"></i> 内容设置
                    </a>
                    <a class="list-group-item list-group-item-action" id="appearance-tab" data-bs-toggle="list" href="#appearance" role="tab" aria-controls="appearance">
                        <i class="bi bi-palette me-2"></i> 外观设置
                    </a>
                    <a class="list-group-item list-group-item-action" id="security-tab" data-bs-toggle="list" href="#security" role="tab" aria-controls="security">
                        <i class="bi bi-shield-lock me-2"></i> 安全设置
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-9">
            <div class="card">
                <div class="card-body">
                    <div class="tab-content">
                        <!-- 基本设置 -->
                        <div class="tab-pane fade show active" id="general" role="tabpanel" aria-labelledby="general-tab">
                            <h5 class="card-title">基本设置</h5>
                            <p class="text-muted">配置系统的基本信息和功能参数</p>
                            
                            <form action="<?php echo site_url('admin/save_settings'); ?>" method="post" id="general-form">
                                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                <input type="hidden" name="setting_group" value="general">
                                
                                <div class="mb-3">
                                    <label for="site_name" class="form-label">网站名称</label>
                                    <input type="text" class="form-control" id="site_name" name="site_name" value="<?php echo htmlspecialchars($settings['general']['site_name'] ?? '少儿编程成就展示系统'); ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="site_description" class="form-label">网站描述</label>
                                    <textarea class="form-control" id="site_description" name="site_description" rows="2"><?php echo htmlspecialchars($settings['general']['site_description'] ?? '记录和展示少儿编程学习成就'); ?></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="admin_email" class="form-label">管理员邮箱</label>
                                    <input type="email" class="form-control" id="admin_email" name="admin_email" value="<?php echo htmlspecialchars($settings['general']['admin_email'] ?? ''); ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="items_per_page" class="form-label">每页显示数量</label>
                                    <select class="form-select" id="items_per_page" name="items_per_page">
                                        <?php 
                                        $current_per_page = $settings['general']['items_per_page'] ?? 10;
                                        $options = [5, 10, 15, 20, 25, 30, 50, 100];
                                        foreach ($options as $option) {
                                            echo '<option value="' . $option . '" ' . ($current_per_page == $option ? 'selected' : '') . '>' . $option . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                                
                                <div class="mt-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save me-1"></i> 保存设置
                                    </button>
                                </div>
                            </form>
                        </div>
                        
                        <!-- 内容设置 -->
                        <div class="tab-pane fade" id="content" role="tabpanel" aria-labelledby="content-tab">
                            <h5 class="card-title">内容设置</h5>
                            <p class="text-muted">配置系统内容显示规则和权限</p>
                            
                            <form action="<?php echo site_url('admin/save_settings'); ?>" method="post" id="content-form">
                                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                <input type="hidden" name="setting_group" value="content">
                                
                                <div class="mb-3 form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="enable_achievements_public" name="enable_achievements_public" value="1" <?php echo ($settings['content']['enable_achievements_public'] ?? 1) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="enable_achievements_public">允许访客查看成就页面</label>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="achievement_types" class="form-label">预设成就类型（多个类型用逗号分隔）</label>
                                    <textarea class="form-control" id="achievement_types" name="achievement_types" rows="3"><?php echo htmlspecialchars($settings['content']['achievement_types'] ?? '编程考试成绩,编程证书,竞赛获奖'); ?></textarea>
                                    <div class="form-text">这些类型将作为成就添加页面的默认选项</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="footer_text" class="form-label">页脚信息</label>
                                    <textarea class="form-control" id="footer_text" name="footer_text" rows="2"><?php echo htmlspecialchars($settings['content']['footer_text'] ?? '&copy; ' . date('Y') . ' ' . ($settings['general']['site_name'] ?? '少儿编程成就展示系统') . ' - 所有权利保留'); ?></textarea>
                                    <div class="form-text">支持HTML标签</div>
                                </div>
                                
                                <div class="mt-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save me-1"></i> 保存设置
                                    </button>
                                </div>
                            </form>
                        </div>
                        
                        <!-- 外观设置 -->
                        <div class="tab-pane fade" id="appearance" role="tabpanel" aria-labelledby="appearance-tab">
                            <h5 class="card-title">外观设置</h5>
                            <p class="text-muted">配置系统界面外观和显示风格</p>
                            
                            <form action="<?php echo site_url('admin/save_settings'); ?>" method="post" id="appearance-form">
                                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                <input type="hidden" name="setting_group" value="appearance">
                                
                                <div class="mb-3">
                                    <label for="default_theme" class="form-label">默认主题模式</label>
                                    <input type="hidden" id="default_theme" name="default_theme" value="light">
                                    <p class="form-text">系统使用浅色主题</p>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="primary_color" class="form-label">主题色</label>
                                    <input type="color" class="form-control form-control-color" id="primary_color" name="primary_color" value="<?php echo htmlspecialchars($settings['appearance']['primary_color'] ?? '#4361ee'); ?>">
                                </div>
                                
                                <div class="mb-3 form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="enable_animations" name="enable_animations" value="1" <?php echo ($settings['appearance']['enable_animations'] ?? 1) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="enable_animations">启用界面动画效果</label>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="card_style" class="form-label">卡片样式</label>
                                    <select class="form-select" id="card_style" name="card_style">
                                        <option value="standard" <?php echo ($settings['appearance']['card_style'] ?? 'standard') == 'standard' ? 'selected' : ''; ?>>标准</option>
                                        <option value="gradient" <?php echo ($settings['appearance']['card_style'] ?? '') == 'gradient' ? 'selected' : ''; ?>>渐变</option>
                                        <option value="dopamine" <?php echo ($settings['appearance']['card_style'] ?? '') == 'dopamine' ? 'selected' : ''; ?>>多巴胺风格</option>
                                    </select>
                                </div>
                                
                                <div class="mt-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save me-1"></i> 保存设置
                                    </button>
                                </div>
                            </form>
                        </div>
                        
                        <!-- 安全设置 -->
                        <div class="tab-pane fade" id="security" role="tabpanel" aria-labelledby="security-tab">
                            <h5 class="card-title">安全设置</h5>
                            <p class="text-muted">配置系统安全相关参数</p>
                            
                            <form action="<?php echo site_url('admin/save_settings'); ?>" method="post" id="security-form">
                                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                <input type="hidden" name="setting_group" value="security">
                                
                                <div class="mb-3 form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="allow_registration" name="allow_registration" value="1" <?php echo ($settings['security']['allow_registration'] ?? 0) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="allow_registration">允许前台注册（不建议开启）</label>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="login_attempts" class="form-label">最大登录尝试次数</label>
                                    <select class="form-select" id="login_attempts" name="login_attempts">
                                        <?php 
                                        $current_attempts = $settings['security']['login_attempts'] ?? 5;
                                        $options = [3, 5, 10, 15, 20];
                                        foreach ($options as $option) {
                                            echo '<option value="' . $option . '" ' . ($current_attempts == $option ? 'selected' : '') . '>' . $option . ' 次</option>';
                                        }
                                        ?>
                                    </select>
                                    <div class="form-text">超过此次数将临时锁定账号</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="session_expire" class="form-label">会话过期时间</label>
                                    <select class="form-select" id="session_expire" name="session_expire">
                                        <?php 
                                        $current_expire = $settings['security']['session_expire'] ?? 1440;
                                        $options = [
                                            30 => '30 分钟',
                                            60 => '1 小时',
                                            720 => '12 小时',
                                            1440 => '1 天',
                                            10080 => '7 天',
                                            43200 => '30 天'
                                        ];
                                        foreach ($options as $value => $label) {
                                            echo '<option value="' . $value . '" ' . ($current_expire == $value ? 'selected' : '') . '>' . $label . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="password_min_length" class="form-label">密码最小长度</label>
                                    <input type="number" class="form-control" id="password_min_length" name="password_min_length" min="6" max="20" value="<?php echo (int)($settings['security']['password_min_length'] ?? 8); ?>">
                                </div>
                                
                                <div class="mt-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save me-1"></i> 保存设置
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 重置设置确认模态框 -->
<div class="modal fade" id="resetSettingsModal" tabindex="-1" aria-labelledby="resetSettingsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="resetSettingsModalLabel">确认重置</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="关闭"></button>
            </div>
            <div class="modal-body">
                <p>确定要将所有设置重置为默认值吗？此操作不可恢复。</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                <form action="<?php echo site_url('admin/reset_settings'); ?>" method="post" style="display: inline;">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    <button type="submit" class="btn btn-danger">确认重置</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 保存表单后的提示
    <?php if (isset($_SESSION['settings_saved']) && $_SESSION['settings_saved']): ?>
    const alertHtml = '<div class="alert alert-success alert-dismissible fade show" role="alert">' +
                      '设置已成功保存！<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="关闭"></button></div>';
    document.querySelector('.card-body').insertAdjacentHTML('afterbegin', alertHtml);
    <?php 
        unset($_SESSION['settings_saved']);
    endif; 
    ?>
    
    // 切换到指定的设置标签
    <?php if (isset($_GET['tab'])): ?>
    document.querySelector('#<?php echo htmlspecialchars($_GET['tab']); ?>-tab').click();
    <?php endif; ?>
});
</script>

<?php include_once VIEW_PATH . '/footer.php'; ?> 