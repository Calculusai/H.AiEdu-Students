<?php
/**
 * 安装完成模板
 */
?>

<div class="card mb-4 bg-light">
    <div class="card-body text-center py-5">
        <i class="bi bi-check-circle text-success display-1 mb-3"></i>
        <h1 class="card-title">安装完成！</h1>
        <p class="lead mb-4">少儿编程成就展示系统已成功安装</p>
        
        <div class="alert alert-info d-inline-block mx-auto">
            <div class="d-flex align-items-center">
                <i class="bi bi-info-circle-fill me-3 fs-3"></i>
                <div class="text-start">
                    <h5>安装锁定文件已创建</h5>
                    <p class="mb-0">为了安全起见，安装程序已被锁定，若需重新安装，请删除网站根目录下的 <code>.install_lock</code> 文件。</p>
                </div>
            </div>
        </div>
        
        <div class="row justify-content-center mt-5">
            <div class="col-md-4">
                <div class="card h-100 border-primary mb-3">
                    <div class="card-body text-center">
                        <i class="bi bi-gear-fill text-primary mb-3 display-4"></i>
                        <h4 class="card-title">管理后台</h4>
                        <p>登录管理后台添加学生和成就记录</p>
                        <a href="../admin" class="btn btn-primary mt-2">
                            访问管理后台
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card h-100 border-success mb-3">
                    <div class="card-body text-center">
                        <i class="bi bi-house-fill text-success mb-3 display-4"></i>
                        <h4 class="card-title">网站首页</h4>
                        <p>访问网站首页浏览学生成就展示</p>
                        <a href="../" class="btn btn-success mt-2">
                            访问网站首页
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="alert alert-warning mb-4">
    <div class="d-flex">
        <i class="bi bi-exclamation-triangle-fill me-3 fs-3"></i>
        <div>
            <h5>安全提示</h5>
            <p class="mb-0">请记得保持PHP文件和网站目录的安全性，定期更新密码并保持服务器软件更新。</p>
        </div>
    </div>
</div> 