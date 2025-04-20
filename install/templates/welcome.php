<h2 class="mb-4">欢迎使用安装向导</h2>

<div class="card mb-4">
    <div class="card-body">
        <h5 class="card-title">安装前的准备</h5>
        <p>感谢您选择少儿编程成就展示系统。在开始安装之前，请确保您已准备好以下信息：</p>
        
        <ul>
            <li>数据库服务器主机名或IP地址</li>
            <li>数据库名称、用户名和密码</li>
            <li>管理员账号信息</li>
        </ul>
        
        <p>本系统将帮助您管理和展示学生的编程成就，让学生有更多的成就感和动力继续学习编程。</p>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <h5 class="card-title">许可协议</h5>
        <div class="p-3 bg-light rounded mb-3" style="height: 200px; overflow-y: auto;">
            <p><strong>使用条款</strong></p>
            <p>本软件为开源项目，您可以自由使用、修改和分发本软件。</p>
            <p>使用本软件即表示您同意：</p>
            <ol>
                <li>不得利用本软件从事任何违法活动</li>
                <li>不得将本软件用于任何可能对他人造成伤害的场景</li>
                <li>作者不对使用本软件可能造成的任何损失承担责任</li>
            </ol>
            <p>建议您定期备份数据，以防意外情况发生。</p>
        </div>
        
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="agreeCheck" required>
            <label class="form-check-label" for="agreeCheck">
                我已阅读并同意上述条款
            </label>
        </div>
    </div>
</div>

<div class="text-end">
    <a href="index.php?step=<?php echo STEP_REQUIREMENTS; ?>" class="btn btn-primary" id="nextButton" disabled>下一步</a>
</div>

<script>
    document.getElementById('agreeCheck').addEventListener('change', function() {
        document.getElementById('nextButton').disabled = !this.checked;
    });
</script> 