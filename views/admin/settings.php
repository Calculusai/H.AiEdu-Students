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

<div class="container py-5">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card glass animate-float mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h1 class="gradient-text mb-3 animate-pulse">系统设置</h1>
                            <p class="lead">配置系统参数和个性化选项，保持最佳运行状态。</p>
                        </div>
                        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#resetSettingsModal">
                            <i class="bi bi-arrow-counterclockwise me-2"></i>重置为默认
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row g-4">
        <div class="col-md-3 mb-4">
            <div class="card glass">
                <div class="card-header bg-transparent border-bottom-0 p-4">
                    <h5 class="gradient-text mb-0">设置分类</h5>
                </div>
                <div class="nav flex-column nav-pills p-3" id="settings-nav" role="tablist">
                    <a class="nav-link active rounded-pill mb-2 d-flex align-items-center" id="general-tab" data-bs-toggle="pill" href="#general" role="tab" aria-controls="general">
                        <i class="bi bi-house-fill me-2"></i> 基本设置
                    </a>
                    <a class="nav-link rounded-pill mb-2 d-flex align-items-center" id="content-tab" data-bs-toggle="pill" href="#content" role="tab" aria-controls="content">
                        <i class="bi bi-card-text me-2"></i> 内容设置
                    </a>
                    <a class="nav-link rounded-pill mb-2 d-flex align-items-center" id="appearance-tab" data-bs-toggle="pill" href="#appearance" role="tab" aria-controls="appearance">
                        <i class="bi bi-palette-fill me-2"></i> 外观设置
                    </a>
                    <a class="nav-link rounded-pill mb-2 d-flex align-items-center" id="security-tab" data-bs-toggle="pill" href="#security" role="tab" aria-controls="security">
                        <i class="bi bi-shield-lock-fill me-2"></i> 安全设置
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-9">
            <div class="card glass">
                <div class="card-body p-4">
                    <div class="tab-content">
                        <!-- 基本设置 -->
                        <div class="tab-pane fade show active" id="general" role="tabpanel" aria-labelledby="general-tab">
                            <h5 class="gradient-text mb-3">基本设置</h5>
                            <p class="text-muted mb-4">配置系统的基本信息和功能参数</p>
                            
                            <form action="<?php echo site_url('admin/settings/save'); ?>" method="post" id="general-form">
                                <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                                <input type="hidden" name="setting_group" value="general">
                                
                                <div class="mb-4">
                                    <label for="site_name" class="form-label">网站名称</label>
                                    <input type="text" class="form-control custom-select" id="site_name" name="site_name" value="<?php echo htmlspecialchars($settings['general']['site_name'] ?? '少儿编程成就展示系统'); ?>">
                                </div>
                                
                                <div class="mb-4">
                                    <label for="site_description" class="form-label">网站描述</label>
                                    <textarea class="form-control custom-select" id="site_description" name="site_description" rows="2"><?php echo htmlspecialchars($settings['general']['site_description'] ?? '记录和展示少儿编程学习成就'); ?></textarea>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="admin_email" class="form-label">管理员邮箱</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-transparent border-end-0">
                                            <i class="bi bi-envelope"></i>
                                        </span>
                                        <input type="email" class="form-control custom-select border-start-0" id="admin_email" name="admin_email" value="<?php echo htmlspecialchars($settings['general']['admin_email'] ?? ''); ?>">
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="items_per_page" class="form-label">每页显示数量</label>
                                    <select class="form-select custom-select" id="items_per_page" name="items_per_page">
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
                                    <button type="submit" class="btn btn-primary btn-shine">
                                        <i class="bi bi-save me-2"></i>保存设置
                                    </button>
                                </div>
                            </form>
                        </div>
                        
                        <!-- 内容设置 -->
                        <div class="tab-pane fade" id="content" role="tabpanel" aria-labelledby="content-tab">
                            <h5 class="gradient-text mb-3">内容设置</h5>
                            <p class="text-muted mb-4">配置系统内容显示规则和权限</p>
                            
                            <form action="<?php echo site_url('admin/settings/save'); ?>" method="post" id="content-form">
                                <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                                <input type="hidden" name="setting_group" value="content">
                                
                                <div class="mb-4">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="enable_achievements_public" name="enable_achievements_public" value="1" <?php echo ($settings['content']['enable_achievements_public'] ?? 1) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="enable_achievements_public">允许访客查看成就页面</label>
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="achievement_types" class="form-label">预设成就类型（多个类型用逗号分隔）</label>
                                    <textarea class="form-control custom-select" id="achievement_types" name="achievement_types" rows="3"><?php echo htmlspecialchars($settings['content']['achievement_types'] ?? '编程考试成绩,编程证书,竞赛获奖'); ?></textarea>
                                    <div class="form-text">这些类型将作为成就添加页面的默认选项</div>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="footer_text" class="form-label">页脚信息</label>
                                    <textarea class="form-control custom-select" id="footer_text" name="footer_text" rows="2"><?php echo htmlspecialchars($settings['content']['footer_text'] ?? '&copy; ' . date('Y') . ' ' . ($settings['general']['site_name'] ?? '少儿编程成就展示系统') . ' - 所有权利保留'); ?></textarea>
                                    <div class="form-text">支持HTML标签</div>
                                </div>
                                
                                <div class="mt-4">
                                    <button type="submit" class="btn btn-primary btn-shine">
                                        <i class="bi bi-save me-2"></i>保存设置
                                    </button>
                                </div>
                            </form>
                        </div>
                        
                        <!-- 外观设置 -->
                        <div class="tab-pane fade" id="appearance" role="tabpanel" aria-labelledby="appearance-tab">
                            <h5 class="gradient-text mb-3">外观设置</h5>
                            <p class="text-muted mb-4">配置系统界面外观和显示风格</p>
                            
                            <form action="<?php echo site_url('admin/settings/save'); ?>" method="post" id="appearance-form">
                                <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                                <input type="hidden" name="setting_group" value="appearance">
                                
                                <div class="mb-4">
                                    <label for="primary_color" class="form-label">主题色</label>
                                    <div class="d-flex align-items-center">
                                        <input type="color" class="form-control form-control-color me-3" id="primary_color" name="primary_color" value="<?php echo htmlspecialchars($settings['appearance']['primary_color'] ?? '#4361ee'); ?>">
                                        <div class="color-preview rounded-circle" style="width: 40px; height: 40px; background-color: <?php echo htmlspecialchars($settings['appearance']['primary_color'] ?? '#4361ee'); ?>"></div>
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="enable_animations" name="enable_animations" value="1" <?php echo ($settings['appearance']['enable_animations'] ?? 1) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="enable_animations">启用界面动画效果</label>
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="card_style" class="form-label">卡片样式</label>
                                    <div class="row g-3 mt-2">
                                        <div class="col-md-4">
                                            <div class="card-style-option">
                                                <input type="radio" class="btn-check" name="card_style" id="card_style_standard" value="standard" <?php echo ($settings['appearance']['card_style'] ?? 'standard') == 'standard' ? 'checked' : ''; ?>>
                                                <label class="card h-100 text-center p-3" for="card_style_standard">
                                                    <div class="py-3 px-4 bg-light rounded mb-2"></div>
                                                    <span>标准</span>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="card-style-option">
                                                <input type="radio" class="btn-check" name="card_style" id="card_style_gradient" value="gradient" <?php echo ($settings['appearance']['card_style'] ?? '') == 'gradient' ? 'checked' : ''; ?>>
                                                <label class="card h-100 text-center p-3" for="card_style_gradient">
                                                    <div class="py-3 px-4 rounded mb-2" style="background: linear-gradient(45deg, #4e73df, #6f42c1);"></div>
                                                    <span>渐变</span>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="card-style-option">
                                                <input type="radio" class="btn-check" name="card_style" id="card_style_dopamine" value="dopamine" <?php echo ($settings['appearance']['card_style'] ?? '') == 'dopamine' ? 'checked' : ''; ?>>
                                                <label class="card h-100 text-center p-3" for="card_style_dopamine">
                                                    <div class="py-3 px-4 rounded mb-2" style="background: linear-gradient(45deg, #FF6B9A, #7367F0);"></div>
                                                    <span>多巴胺风格</span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mt-4">
                                    <button type="submit" class="btn btn-primary btn-shine">
                                        <i class="bi bi-save me-2"></i>保存设置
                                    </button>
                                </div>
                            </form>
                        </div>
                        
                        <!-- 安全设置 -->
                        <div class="tab-pane fade" id="security" role="tabpanel" aria-labelledby="security-tab">
                            <h5 class="gradient-text mb-3">安全设置</h5>
                            <p class="text-muted mb-4">配置系统安全相关参数</p>
                            
                            <form action="<?php echo site_url('admin/settings/save'); ?>" method="post" id="security-form">
                                <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                                <input type="hidden" name="setting_group" value="security">
                                
                                <div class="mb-4">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="allow_registration" name="allow_registration" value="1" <?php echo ($settings['security']['allow_registration'] ?? 0) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="allow_registration">允许前台注册（不建议开启）</label>
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="login_attempts" class="form-label">最大登录尝试次数</label>
                                    <select class="form-select custom-select" id="login_attempts" name="login_attempts">
                                        <?php 
                                        $current_attempts = $settings['security']['login_attempts'] ?? 5;
                                        $options = [3, 5, 10, 15, 20];
                                        foreach ($options as $option) {
                                            echo '<option value="' . $option . '" ' . ($current_attempts == $option ? 'selected' : '') . '>' . $option . ' 次</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="password_policy" class="form-label">密码策略</label>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="require_uppercase" name="password_policy[require_uppercase]" value="1" <?php echo ($settings['security']['password_policy']['require_uppercase'] ?? 1) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="require_uppercase">要求包含大写字母</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="require_number" name="password_policy[require_number]" value="1" <?php echo ($settings['security']['password_policy']['require_number'] ?? 1) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="require_number">要求包含数字</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="require_special" name="password_policy[require_special]" value="1" <?php echo ($settings['security']['password_policy']['require_special'] ?? 0) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="require_special">要求包含特殊字符</label>
                                    </div>
                                    
                                    <div class="mt-3">
                                        <label for="min_password_length" class="form-label">最小密码长度</label>
                                        <input type="range" class="form-range" id="min_password_length" name="password_policy[min_length]" min="6" max="16" step="1" value="<?php echo ($settings['security']['password_policy']['min_length'] ?? 8); ?>">
                                        <div class="d-flex justify-content-between">
                                            <span>6</span>
                                            <span id="password_length_value"><?php echo ($settings['security']['password_policy']['min_length'] ?? 8); ?></span>
                                            <span>16</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mt-4">
                                    <button type="submit" class="btn btn-primary btn-shine">
                                        <i class="bi bi-save me-2"></i>保存设置
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

<!-- 重置确认模态框 -->
<div class="modal fade" id="resetSettingsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass">
            <div class="modal-header border-0">
                <h5 class="modal-title gradient-text">确认重置</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <div class="badge-icon bg-warning text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width:60px;height:60px">
                        <i class="bi bi-exclamation-triangle-fill fs-4"></i>
                    </div>
                </div>
                <p class="text-center">确定要将所有设置重置为默认值吗？</p>
                <p class="text-center text-danger">此操作不可恢复！</p>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">取消</button>
                <form action="<?php echo site_url('admin/settings/reset'); ?>" method="post">
                    <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                    <button type="submit" class="btn btn-warning btn-shine">确认重置</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // 显示密码长度滑块的值
    $('#min_password_length').on('input', function() {
        $('#password_length_value').text($(this).val());
    });
    
    // 更新主题颜色预览
    $('#primary_color').on('input', function() {
        $('.color-preview').css('background-color', $(this).val());
    });
    
    // 安全设置表单提交时添加标识符
    $('#security-form').on('submit', function() {
        $(this).append('<input type="hidden" name="is_security_setting" value="1">');
        $('#security-submit-btn').prop('disabled', true).text('正在保存...');
        return true;
    });
    
    // 检查是否有成功消息并且是安全设置已保存
    <?php if (isset($_SESSION['security_settings_updated']) && $_SESSION['security_settings_updated']): ?>
    // 清除标记，避免重复刷新
    <?php unset($_SESSION['security_settings_updated']); ?>
    alert('安全设置已更新，系统将重新加载页面以应用新设置。');
    // 稍微延迟刷新，让用户看到消息
    setTimeout(function() {
        window.location.reload();
    }, 1000);
    <?php endif; ?>
    
    // 根据URL参数激活对应的标签页
    var activeTab = getUrlParameter('tab') || 'general';
    $('#' + activeTab + '-tab').addClass('active');
    $('#' + activeTab).addClass('show active');
    
    // 从URL获取参数的辅助函数
    function getUrlParameter(name) {
        name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
        var regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
        var results = regex.exec(location.search);
        return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
    }
    
    // 切换标签页时更新URL，不刷新页面
    $('.nav-link').on('click', function() {
        var tabId = $(this).attr('id').replace('-tab', '');
        history.replaceState(null, null, '?tab=' + tabId);
    });
});
</script>

<style>
.card-style-option label {
    cursor: pointer;
    transition: all 0.3s ease;
}

.card-style-option input[type="radio"]:checked + label {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 2px var(--primary-color);
}

.form-check-input:checked {
    background-color: var(--primary-color);
    border-color: var(--primary-color);
}

.form-range::-webkit-slider-thumb {
    background: var(--primary-color);
}

.nav-pills .nav-link {
    color: var(--dark-color);
    transition: all 0.3s ease;
}

.nav-pills .nav-link:hover {
    background-color: rgba(78, 115, 223, 0.1);
}

.nav-pills .nav-link.active {
    background-color: var(--primary-color);
    color: white;
}
</style>

<?php include_once VIEW_PATH . '/footer.php'; ?> 