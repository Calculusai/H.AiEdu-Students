<?php
/**
 * 动态CSS生成器 - 根据系统配置生成自定义样式
 */
header('Content-Type: text/css');

// 包含配置文件
require_once __DIR__ . '/../../config.php';

// 从配置中获取变量
$primaryColor = defined('PRIMARY_COLOR') ? PRIMARY_COLOR : '#6366f1';
$secondaryColor = defined('SECONDARY_COLOR') ? SECONDARY_COLOR : '#ec4899';
$borderRadius = defined('BORDER_RADIUS') ? BORDER_RADIUS : '1rem';
$cardStyle = defined('CARD_STYLE') ? CARD_STYLE : 'standard';
$enableAnimations = defined('ENABLE_ANIMATIONS') && ENABLE_ANIMATIONS;

// 根据阴影强度生成不同的阴影值
$shadowIntensity = defined('SHADOW_INTENSITY') ? SHADOW_INTENSITY : 'medium';
$boxShadow = '';
$boxShadowLg = '';
$boxShadowSm = '';

switch($shadowIntensity) {
    case 'light':
        $boxShadow = '0 5px 10px -3px rgba(0, 0, 0, 0.05), 0 2px 3px -2px rgba(0, 0, 0, 0.05)';
        $boxShadowLg = '0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -4px rgba(0, 0, 0, 0.05)';
        $boxShadowSm = '0 2px 4px -1px rgba(0, 0, 0, 0.03), 0 1px 2px -1px rgba(0, 0, 0, 0.03)';
        break;
    case 'strong':
        $boxShadow = '0 15px 20px -3px rgba(0, 0, 0, 0.15), 0 6px 8px -4px rgba(0, 0, 0, 0.15)';
        $boxShadowLg = '0 25px 30px -5px rgba(0, 0, 0, 0.15), 0 10px 12px -6px rgba(0, 0, 0, 0.15)';
        $boxShadowSm = '0 6px 8px -2px rgba(0, 0, 0, 0.1), 0 3px 5px -2px rgba(0, 0, 0, 0.1)';
        break;
    default: // medium
        $boxShadow = '0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -4px rgba(0, 0, 0, 0.1)';
        $boxShadowLg = '0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1)';
        $boxShadowSm = '0 4px 6px -1px rgba(0, 0, 0, 0.07), 0 2px 4px -2px rgba(0, 0, 0, 0.07)';
}

// 将颜色转换为RGB格式
function hexToRgb($hex) {
    $hex = ltrim($hex, '#');
    if (strlen($hex) == 3) {
        $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
    }
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    return [$r, $g, $b];
}

// 获取颜色的RGB值
$primaryRgb = implode(', ', hexToRgb($primaryColor));
$secondaryRgb = implode(', ', hexToRgb($secondaryColor));
$successRgb = '16, 185, 129'; // #10b981
$warningRgb = '245, 158, 11'; // #f59e0b
$dangerRgb = '239, 68, 68';   // #ef4444
$infoRgb = '59, 130, 246';    // #3b82f6
$darkRgb = '17, 24, 39';      // #111827
$lightRgb = '249, 250, 251';  // #f9fafb

// 生成主色调衍生色
function adjustColor($hex, $percent) {
    // 从十六进制转换为RGB
    $hex = ltrim($hex, '#');
    if (strlen($hex) == 3) {
        $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
    }
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));

    // 调整亮度
    $r = max(0, min(255, $r + $r * $percent / 100));
    $g = max(0, min(255, $g + $g * $percent / 100));
    $b = max(0, min(255, $b + $b * $percent / 100));

    // 转回十六进制
    return sprintf('#%02x%02x%02x', $r, $g, $b);
}

$primaryLighter = adjustColor($primaryColor, 15);
$primaryDarker = adjustColor($primaryColor, -15);
$secondaryLighter = adjustColor($secondaryColor, 15);
$secondaryDarker = adjustColor($secondaryColor, -15);

// 动画设置
$animationCSS = $enableAnimations ? '' : '* { animation: none !important; transition: none !important; }';

// 根据主题确定卡片背景色
$cardBg = '#ffffff';
$darkCardBg = '#1e293b';
?>

/* 动态生成的CSS变量 */
:root {
    /* 主题颜色 */
    --primary-color: <?php echo $primaryColor; ?>;
    --primary-rgb: <?php echo $primaryRgb; ?>;
    --primary-gradient: linear-gradient(135deg, <?php echo $primaryColor; ?>, <?php echo $primaryLighter; ?>);
    
    --secondary-color: <?php echo $secondaryColor; ?>;
    --secondary-rgb: <?php echo $secondaryRgb; ?>;
    --secondary-gradient: linear-gradient(135deg, <?php echo $secondaryColor; ?>, <?php echo $secondaryLighter; ?>);
    
    /* 其他颜色的RGB变量 */
    --success-rgb: <?php echo $successRgb; ?>;
    --warning-rgb: <?php echo $warningRgb; ?>;
    --danger-rgb: <?php echo $dangerRgb; ?>;
    --info-rgb: <?php echo $infoRgb; ?>;
    --dark-color-rgb: <?php echo $darkRgb; ?>;
    --light-color-rgb: <?php echo $lightRgb; ?>;
    
    /* 边框圆角 */
    --border-radius: <?php echo $borderRadius; ?>;
    --card-border-radius: calc(<?php echo $borderRadius; ?> * 1.25);
    --button-border-radius: calc(<?php echo $borderRadius; ?> * 0.75);
    
    /* 阴影 */
    --box-shadow: <?php echo $boxShadow; ?>;
    --box-shadow-lg: <?php echo $boxShadowLg; ?>;
    --box-shadow-sm: <?php echo $boxShadowSm; ?>;
    --box-shadow-colored: 0 10px 15px -3px var(--shadow-color, rgba(<?php echo $primaryRgb; ?>, 0.2)), 
                          0 4px 6px -4px var(--shadow-color, rgba(<?php echo $primaryRgb; ?>, 0.2));
    
    /* 卡片背景 */
    --card-bg: <?php echo $cardBg; ?>;
    
    /* 动画曲线 */
    --bounce: cubic-bezier(0.34, 1.56, 0.64, 1);
    --smooth: cubic-bezier(0.45, 0, 0.55, 1);
}

/* 深色模式变量 */
[data-theme="dark"] {
    --card-bg: <?php echo $darkCardBg; ?>;
    --body-color: rgba(255, 255, 255, 0.85);
    --heading-color: rgba(255, 255, 255, 0.95);
    --border-color: rgba(255, 255, 255, 0.1);
}

/* 禁用动画（如果设置） */
<?php if (!$enableAnimations): ?>
* {
    animation: none !important;
    transition: none !important;
}
<?php endif; ?>

/* 卡片样式 */
<?php if ($cardStyle === 'gradient'): ?>
.card {
    background: linear-gradient(135deg, <?php echo $primaryColor; ?>, <?php echo $secondaryColor; ?>);
    color: #fff;
}
.card .text-muted {
    color: rgba(255, 255, 255, 0.7) !important;
}
<?php elseif ($cardStyle === 'dopamine'): ?>
.card {
    background: var(--card-bg);
    border: 2px solid rgba(<?php echo $primaryRgb; ?>, 0.1);
    border-radius: var(--card-border-radius);
    transition: all 0.3s var(--bounce);
}
.card:hover {
    border-color: rgba(<?php echo $primaryRgb; ?>, 0.3);
    transform: translateY(-5px);
    box-shadow: var(--box-shadow-colored);
}
<?php endif; ?>

/* 导航栏样式 */
.navbar {
    background: <?php echo $cardStyle === 'gradient' ? "linear-gradient(135deg, $primaryColor, $secondaryColor)" : 'var(--card-bg)'; ?>;
    <?php if ($cardStyle === 'gradient'): ?>
    color: #fff;
    <?php endif; ?>
}

/* 按钮样式 */
.btn-primary {
    background: var(--primary-gradient);
    border: none;
}
.btn-primary:hover {
    background: linear-gradient(135deg, <?php echo $primaryDarker; ?>, <?php echo $primaryColor; ?>);
}

.btn-secondary {
    background: var(--secondary-gradient);
    border: none;
}
.btn-secondary:hover {
    background: linear-gradient(135deg, <?php echo $secondaryDarker; ?>, <?php echo $secondaryColor; ?>);
}

/* 表单元素 */
.form-control:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.25rem rgba(<?php echo $primaryRgb; ?>, 0.25);
}

/* 适应边框圆角 */
.btn, 
.form-control, 
.card, 
.navbar,
.alert,
.badge,
.dropdown-menu {
    border-radius: var(--border-radius);
}

/* 适应阴影 */
.dropdown-menu,
.tooltip,
.popover,
.modal-content {
    box-shadow: var(--box-shadow);
} 