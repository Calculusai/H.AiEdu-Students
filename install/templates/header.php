<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>少儿编程成就展示系统 - 安装向导</title>
    <link href="https://cdn.bootcdn.net/ajax/libs/bootstrap/5.2.3/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #7209b7;
            --success-color: #06d6a0;
            --warning-color: #ffd166;
            --danger-color: #ef476f;
            --light-color: #f8f9fa;
            --dark-color: #212529;
        }
        
        body {
            background-color: #f5f7fa;
            font-family: "PingFang SC", "Microsoft YaHei", sans-serif;
            line-height: 1.6;
            padding-top: 40px;
            padding-bottom: 40px;
        }
        
        .installer-container {
            max-width: 680px;
            margin: 0 auto;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }
        
        .installer-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .installer-header h1 {
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }
        
        .installer-header p {
            opacity: 0.9;
            margin-bottom: 0;
        }
        
        .installer-body {
            padding: 2rem;
        }
        
        .step-indicator {
            display: flex;
            margin-bottom: 2rem;
            justify-content: space-between;
        }
        
        .step {
            flex: 1;
            text-align: center;
            position: relative;
        }
        
        .step::after {
            content: '';
            position: absolute;
            top: 50%;
            right: -50%;
            transform: translateY(-50%);
            width: 100%;
            height: 3px;
            background-color: #e9ecef;
            z-index: 1;
        }
        
        .step:last-child::after {
            display: none;
        }
        
        .step-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: #e9ecef;
            color: #495057;
            font-weight: bold;
            margin-bottom: 0.5rem;
            position: relative;
            z-index: 2;
        }
        
        .step.active .step-number {
            background-color: var(--primary-color);
            color: white;
        }
        
        .step.completed .step-number {
            background-color: var(--success-color);
            color: white;
        }
        
        .step-title {
            font-size: 0.8rem;
            color: #6c757d;
        }
        
        .step.active .step-title {
            color: var(--primary-color);
            font-weight: bold;
        }
        
        .step.completed .step-title {
            color: var(--success-color);
        }
        
        .installer-footer {
            background-color: #f8f9fa;
            padding: 1rem 2rem;
            text-align: center;
            border-top: 1px solid #e9ecef;
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        
        .btn-success {
            background-color: var(--success-color);
            border-color: var(--success-color);
        }
        
        .status-good {
            color: var(--success-color);
        }
        
        .status-bad {
            color: var(--danger-color);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="installer-container">
            <div class="installer-header">
                <h1>少儿编程成就展示系统</h1>
                <p>安装向导将帮助您快速完成系统部署</p>
            </div>
            
            <div class="step-indicator">
                <div class="step <?php echo ($current_step == STEP_WELCOME) ? 'active' : (($current_step == STEP_REQUIREMENTS || $current_step == STEP_DATABASE || $current_step == STEP_ADMIN || $current_step == STEP_FINISH) ? 'completed' : ''); ?>">
                    <div class="step-number">1</div>
                    <div class="step-title">欢迎</div>
                </div>
                <div class="step <?php echo ($current_step == STEP_REQUIREMENTS) ? 'active' : (($current_step == STEP_DATABASE || $current_step == STEP_ADMIN || $current_step == STEP_FINISH) ? 'completed' : ''); ?>">
                    <div class="step-number">2</div>
                    <div class="step-title">环境检测</div>
                </div>
                <div class="step <?php echo ($current_step == STEP_DATABASE) ? 'active' : (($current_step == STEP_ADMIN || $current_step == STEP_FINISH) ? 'completed' : ''); ?>">
                    <div class="step-number">3</div>
                    <div class="step-title">数据库设置</div>
                </div>
                <div class="step <?php echo ($current_step == STEP_ADMIN) ? 'active' : (($current_step == STEP_FINISH) ? 'completed' : ''); ?>">
                    <div class="step-number">4</div>
                    <div class="step-title">管理员账号</div>
                </div>
                <div class="step <?php echo ($current_step == STEP_FINISH) ? 'active' : ''; ?>">
                    <div class="step-number">5</div>
                    <div class="step-title">完成</div>
                </div>
            </div>
            
            <div class="installer-body">
                <?php if (isset($error_message)): ?>
                <div class="alert alert-danger">
                    <?php echo $error_message; ?>
                </div>
                <?php endif; ?> 