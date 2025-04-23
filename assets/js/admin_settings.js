/**
 * 少儿编程成就展示系统 - 管理员设置页面脚本
 */
$(document).ready(function () {
    // 显示密码长度滑块的值
    $('#min_password_length').on('input', function () {
        $('#password_length_value').text($(this).val());
    });

    // 更新主题颜色预览
    $('#primary_color').on('input', function () {
        $('.color-preview').css('background-color', $(this).val());
        updatePreview();
    });

    // 更新次要颜色预览
    $('#secondary_color').on('input', function () {
        $('.secondary-color-preview').css('background-color', $(this).val());
        updatePreview();
    });

    // 更新边框圆角值显示
    $('#border_radius').on('input', function () {
        var value = $(this).val() + 'rem';
        $('#radius_value').text(value);
        updatePreview();
    });

    // 监听阴影强度变化
    $('#shadow_intensity').on('change', function () {
        updatePreview();
    });

    // 监听卡片样式变化
    $('input[name="card_style"]').on('change', function () {
        updatePreview();
    });

    // 更新预览卡片
    function updatePreview() {
        var primaryColor = $('#primary_color').val();
        var secondaryColor = $('#secondary_color').val();
        var borderRadius = $('#border_radius').val() + 'rem';
        var cardStyle = $('input[name="card_style"]:checked').val();
        var shadowIntensity = $('#shadow_intensity').val();

        // 设置预览卡片样式
        var previewCard = $('#preview-card');

        // 重置样式
        previewCard.attr('style', '');

        // 设置边框圆角
        previewCard.css('border-radius', borderRadius);

        // 设置阴影
        var shadow = '';
        switch (shadowIntensity) {
            case 'light':
                shadow = '0 5px 10px -3px rgba(0, 0, 0, 0.05), 0 2px 3px -2px rgba(0, 0, 0, 0.05)';
                break;
            case 'strong':
                shadow = '0 15px 20px -3px rgba(0, 0, 0, 0.15), 0 6px 8px -4px rgba(0, 0, 0, 0.15)';
                break;
            default: // medium
                shadow = '0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -4px rgba(0, 0, 0, 0.1)';
        }
        previewCard.css('box-shadow', shadow);

        // 设置卡片样式
        if (cardStyle === 'gradient') {
            previewCard.css('background', 'linear-gradient(135deg, ' + primaryColor + ', ' + secondaryColor + ')');
            previewCard.css('color', '#fff');
            previewCard.find('.preview-card-header').css('border-color', 'rgba(255, 255, 255, 0.2)');
        } else if (cardStyle === 'dopamine') {
            previewCard.css('background', '#ffffff');
            previewCard.css('border', '2px solid ' + primaryColor + '33'); // 20% opacity
            previewCard.find('.preview-card-header').css('border-color', primaryColor + '33');
        } else {
            previewCard.css('background', '#ffffff');
            previewCard.css('border', '1px solid rgba(0, 0, 0, 0.125)');
            previewCard.find('.preview-card-header').css('border-color', 'rgba(0, 0, 0, 0.125)');
        }

        // 更新按钮样式
        $('.btn-primary').css('background', 'linear-gradient(135deg, ' + primaryColor + ', ' + adjustColor(primaryColor, 15) + ')');
        $('.btn-secondary').css('background', 'linear-gradient(135deg, ' + secondaryColor + ', ' + adjustColor(secondaryColor, 15) + ')');
        $('.btn').css('border-radius', 'calc(' + borderRadius + ' * 0.75)');
    }

    // 调整颜色亮度的辅助函数
    function adjustColor(hex, percent) {
        // 从十六进制转换为RGB
        hex = hex.replace('#', '');
        var r = parseInt(hex.substring(0, 2), 16);
        var g = parseInt(hex.substring(2, 4), 16);
        var b = parseInt(hex.substring(4, 6), 16);

        // 调整亮度
        r = Math.max(0, Math.min(255, r + Math.round(r * percent / 100)));
        g = Math.max(0, Math.min(255, g + Math.round(g * percent / 100)));
        b = Math.max(0, Math.min(255, b + Math.round(b * percent / 100)));

        // 转回十六进制
        return '#' + ((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1);
    }

    // 初始化预览
    updatePreview();

    // 安全设置表单提交时添加标识符
    $('#security-form').on('submit', function () {
        $(this).append('<input type="hidden" name="is_security_setting" value="1">');
        $('#security-submit-btn').prop('disabled', true).text('正在保存...');
        return true;
    });

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
    $('.nav-link').on('click', function () {
        var tabId = $(this).attr('id').replace('-tab', '');
        history.replaceState(null, null, '?tab=' + tabId);
    });

    // 提交内容设置表单时
    $('#content-form').on('submit', function (e) {
        e.preventDefault();

        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(),
            success: function (response) {
                // 显示成功消息
                showToast('内容设置已保存，页面将自动刷新以应用新设置');

                // 延迟刷新页面
                setTimeout(function () {
                    window.location.reload();
                }, 1500);
            },
            error: function () {
                // 显示错误消息
                showToast('保存设置时发生错误，请重试', 'error');
            }
        });
    });

    // 提交外观设置表单时
    $('#appearance-form').on('submit', function (e) {
        e.preventDefault();

        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(),
            success: function (response) {
                // 显示成功消息
                showToast('外观设置已保存，页面将自动刷新以应用新设置');

                // 延迟刷新页面
                setTimeout(function () {
                    window.location.reload();
                }, 1500);
            },
            error: function () {
                // 显示错误消息
                showToast('保存设置时发生错误，请重试', 'error');
            }
        });
    });

    // 添加显示Toast消息的函数
    function showToast(message, type = 'success') {
        // 创建一个toast元素
        const toast = $('<div class="toast align-items-center" role="alert" aria-live="assertive" aria-atomic="true">');
        toast.addClass(type === 'success' ? 'bg-success' : 'bg-danger');
        toast.addClass('text-white');

        // 填充内容
        toast.html(`
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        `);

        // 添加到页面
        if ($('#toast-container').length === 0) {
            $('body').append('<div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3"></div>');
        }
        $('#toast-container').append(toast);

        // 初始化并显示toast
        const bsToast = new bootstrap.Toast(toast[0], {
            delay: 3000
        });
        bsToast.show();

        // 移除被隐藏的toast
        toast.on('hidden.bs.toast', function () {
            $(this).remove();
        });
    }
}); 