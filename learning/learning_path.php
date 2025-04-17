<?php
require_once '../includes/config.php';

$pageTitle = '学习路径推荐';

// 页面特定样式
$extraStyles = <<<HTML
<style>
    .page-header {
        background: linear-gradient(135deg, var(--green), var(--blue));
        color: white;
        padding: var(--space-md) 0;
        margin-bottom: var(--space-lg);
        border-radius: var(--radius-xl);
        text-align: center;
    }
    
    .tabs {
        display: flex;
        background-color: white;
        border-radius: var(--radius-lg);
        margin-bottom: var(--space-md);
        overflow: hidden;
        box-shadow: 0 5px 15px var(--shadow-color);
    }
    
    .tab-btn {
        flex: 1;
        padding: var(--space-sm);
        text-align: center;
        background: none;
        border: none;
        color: var(--text-primary);
        cursor: pointer;
        font-weight: 600;
        transition: all 0.3s ease;
        border-bottom: 3px solid transparent;
    }
    
    .tab-btn.active {
        color: var(--green);
        border-bottom: 3px solid var(--green);
        background-color: rgba(0, 224, 158, 0.1);
    }
    
    .tab-content {
        display: none;
        background-color: white;
        padding: var(--space-md);
        border-radius: var(--radius-lg);
        box-shadow: 0 5px 15px var(--shadow-color);
        margin-bottom: var(--space-lg);
    }
    
    .tab-content.active {
        display: block;
    }
    
    .path-overview {
        margin-bottom: var(--space-md);
        padding: var(--space-md);
        background: linear-gradient(135deg, rgba(0, 224, 158, 0.1), rgba(62, 198, 255, 0.1));
        border-radius: var(--radius-lg);
    }
    
    .path-title {
        color: var(--green);
        margin-bottom: var(--space-xs);
    }
    
    .learning-path {
        position: relative;
        padding-left: 30px;
        margin-bottom: var(--space-lg);
    }
    
    .learning-path::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 3px;
        background: linear-gradient(to bottom, var(--green), var(--blue));
        border-radius: 3px;
    }
    
    .path-step {
        position: relative;
        margin-bottom: var(--space-md);
        padding-left: 10px;
    }
    
    .path-step::before {
        content: '';
        position: absolute;
        left: -37px;
        top: 0;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background-color: var(--green);
        border: 3px solid white;
        box-shadow: 0 0 0 3px var(--green);
    }
    
    .path-step-title {
        font-weight: 700;
        color: var(--green);
        margin-bottom: var(--space-xs);
    }
    
    .skill-list {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin: var(--space-sm) 0;
    }
    
    .skill {
        background-color: rgba(62, 198, 255, 0.1);
        color: var(--blue);
        padding: 5px 12px;
        border-radius: 20px;
        font-size: var(--font-small);
        font-weight: 600;
    }
    
    .level-indicator {
        display: flex;
        align-items: center;
        margin: var(--space-sm) 0;
    }
    
    .level-label {
        font-weight: 600;
        margin-right: 10px;
        color: var(--text-secondary);
    }
    
    .level-dots {
        display: flex;
    }
    
    .level-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        margin-right: 5px;
        background-color: var(--border-color);
    }
    
    .level-dot.active {
        background-color: var(--green);
    }
    
    .resources {
        margin-top: var(--space-xs);
    }
    
    .resource-badge {
        display: inline-flex;
        align-items: center;
        background-color: rgba(0, 224, 158, 0.1);
        color: var(--green);
        padding: 5px 10px;
        border-radius: 4px;
        margin-right: 8px;
        margin-bottom: 8px;
        font-size: var(--font-small);
    }
</style>
HTML;

include TEMPLATES_PATH . '/header.php';
?>

<!-- 页面标题 -->
<div class="page-header">
    <div class="container">
        <h1>学习路径推荐</h1>
        <p>选择适合你的编程学习路径，系统掌握编程知识</p>
    </div>
</div>

<!-- 学习路径选择标签页 -->
<div class="tabs">
    <button class="tab-btn active" data-tab="scratch">Scratch</button>
    <button class="tab-btn" data-tab="python">Python</button>
    <button class="tab-btn" data-tab="web">Web前端</button>
    <button class="tab-btn" data-tab="cpp">C++</button>
</div>

<!-- Scratch 学习路径 -->
<div class="tab-content active" id="scratch">
    <div class="path-overview">
        <h2 class="path-title">Scratch编程学习路径</h2>
        <p>Scratch是一种由麻省理工学院设计开发的图形化编程语言，特别适合8-16岁的孩子学习编程基础知识，培养逻辑思维和创造力。</p>
        
        <div class="level-indicator">
            <span class="level-label">难度：</span>
            <div class="level-dots">
                <div class="level-dot active"></div>
                <div class="level-dot"></div>
                <div class="level-dot"></div>
                <div class="level-dot"></div>
                <div class="level-dot"></div>
            </div>
        </div>
        
        <div class="level-indicator">
            <span class="level-label">适合年龄：</span>
            <span>8-16岁</span>
        </div>
    </div>
    
    <div class="learning-path">
        <div class="path-step">
            <h3 class="path-step-title">第一阶段：Scratch基础</h3>
            <p>了解Scratch界面和基本操作，学习使用积木块创建简单动画和游戏。</p>
            
            <div class="skill-list">
                <span class="skill">界面熟悉</span>
                <span class="skill">角色控制</span>
                <span class="skill">简单动画</span>
                <span class="skill">事件处理</span>
            </div>
            
            <div class="resources">
                <span class="resource-badge">视频教程: 10</span>
                <span class="resource-badge">练习项目: 5</span>
                <span class="resource-badge">预计时间: 2周</span>
            </div>
        </div>
        
        <div class="path-step">
            <h3 class="path-step-title">第二阶段：Scratch进阶</h3>
            <p>学习更复杂的编程概念，如变量、条件判断和循环，制作互动性更强的项目。</p>
            
            <div class="skill-list">
                <span class="skill">变量使用</span>
                <span class="skill">条件判断</span>
                <span class="skill">循环结构</span>
                <span class="skill">克隆角色</span>
            </div>
            
            <div class="resources">
                <span class="resource-badge">视频教程: 12</span>
                <span class="resource-badge">练习项目: 8</span>
                <span class="resource-badge">预计时间: 3周</span>
            </div>
        </div>
        
        <div class="path-step">
            <h3 class="path-step-title">第三阶段：游戏开发</h3>
            <p>运用所学知识，开发完整的游戏项目，包括闯关游戏、益智游戏等。</p>
            
            <div class="skill-list">
                <span class="skill">游戏设计</span>
                <span class="skill">碰撞检测</span>
                <span class="skill">计分系统</span>
                <span class="skill">多关卡设计</span>
            </div>
            
            <div class="resources">
                <span class="resource-badge">视频教程: 8</span>
                <span class="resource-badge">练习项目: 3</span>
                <span class="resource-badge">预计时间: 4周</span>
            </div>
        </div>
        
        <div class="path-step">
            <h3 class="path-step-title">第四阶段：创意项目</h3>
            <p>发挥创造力，设计并开发个人创意项目，可以是游戏、动画或互动故事。</p>
            
            <div class="skill-list">
                <span class="skill">项目规划</span>
                <span class="skill">创意设计</span>
                <span class="skill">问题解决</span>
                <span class="skill">作品展示</span>
            </div>
            
            <div class="resources">
                <span class="resource-badge">指导课程: 5</span>
                <span class="resource-badge">作品展示: 1</span>
                <span class="resource-badge">预计时间: 5周</span>
            </div>
        </div>
    </div>
    
    <div style="text-align: center; margin-top: var(--space-lg);">
        <a href="#" class="btn btn-learning">开始学习 Scratch</a>
    </div>
</div>

<!-- Python 学习路径 -->
<div class="tab-content" id="python">
    <div class="path-overview">
        <h2 class="path-title">Python编程学习路径</h2>
        <p>Python是一种简洁易学且功能强大的编程语言，广泛应用于数据分析、人工智能、网站开发等领域，是进阶编程学习的理想选择。</p>
        
        <div class="level-indicator">
            <span class="level-label">难度：</span>
            <div class="level-dots">
                <div class="level-dot active"></div>
                <div class="level-dot active"></div>
                <div class="level-dot active"></div>
                <div class="level-dot"></div>
                <div class="level-dot"></div>
            </div>
        </div>
        
        <div class="level-indicator">
            <span class="level-label">适合年龄：</span>
            <span>12岁以上</span>
        </div>
    </div>
    
    <div class="learning-path">
        <div class="path-step">
            <h3 class="path-step-title">第一阶段：Python基础</h3>
            <p>了解Python环境安装与配置，学习基本语法、数据类型和简单程序编写。</p>
            
            <div class="skill-list">
                <span class="skill">环境配置</span>
                <span class="skill">基本语法</span>
                <span class="skill">数据类型</span>
                <span class="skill">简单输入输出</span>
            </div>
            
            <div class="resources">
                <span class="resource-badge">视频教程: 15</span>
                <span class="resource-badge">练习项目: 10</span>
                <span class="resource-badge">预计时间: 4周</span>
            </div>
        </div>
        
        <div class="path-step">
            <h3 class="path-step-title">第二阶段：Python进阶</h3>
            <p>学习函数、模块、异常处理等进阶概念，开始编写更复杂的程序。</p>
            
            <div class="skill-list">
                <span class="skill">函数定义</span>
                <span class="skill">模块导入</span>
                <span class="skill">异常处理</span>
                <span class="skill">文件操作</span>
            </div>
            
            <div class="resources">
                <span class="resource-badge">视频教程: 18</span>
                <span class="resource-badge">练习项目: 12</span>
                <span class="resource-badge">预计时间: 6周</span>
            </div>
        </div>
        
        <div class="path-step">
            <h3 class="path-step-title">第三阶段：数据结构与算法</h3>
            <p>掌握Python中的列表、字典、集合等数据结构，学习基本算法思想。</p>
            
            <div class="skill-list">
                <span class="skill">列表操作</span>
                <span class="skill">字典使用</span>
                <span class="skill">简单排序</span>
                <span class="skill">查找算法</span>
            </div>
            
            <div class="resources">
                <span class="resource-badge">视频教程: 12</span>
                <span class="resource-badge">练习项目: 8</span>
                <span class="resource-badge">预计时间: 5周</span>
            </div>
        </div>
        
        <div class="path-step">
            <h3 class="path-step-title">第四阶段：项目实践</h3>
            <p>运用所学知识完成综合项目，如简单游戏、数据分析或网络爬虫等。</p>
            
            <div class="skill-list">
                <span class="skill">项目规划</span>
                <span class="skill">代码组织</span>
                <span class="skill">问题解决</span>
                <span class="skill">成果展示</span>
            </div>
            
            <div class="resources">
                <span class="resource-badge">指导课程: 8</span>
                <span class="resource-badge">项目实践: 3</span>
                <span class="resource-badge">预计时间: 8周</span>
            </div>
        </div>
    </div>
    
    <div style="text-align: center; margin-top: var(--space-lg);">
        <a href="#" class="btn btn-learning">开始学习 Python</a>
    </div>
</div>

<!-- Web前端学习路径 -->
<div class="tab-content" id="web">
    <div class="path-overview">
        <h2 class="path-title">Web前端开发学习路径</h2>
        <p>Web前端开发涉及网站的视觉和交互部分，包括HTML、CSS和JavaScript等技术，让学生能够创建自己的网页和Web应用。</p>
        
        <div class="level-indicator">
            <span class="level-label">难度：</span>
            <div class="level-dots">
                <div class="level-dot active"></div>
                <div class="level-dot active"></div>
                <div class="level-dot active"></div>
                <div class="level-dot"></div>
                <div class="level-dot"></div>
            </div>
        </div>
        
        <div class="level-indicator">
            <span class="level-label">适合年龄：</span>
            <span>13岁以上</span>
        </div>
    </div>
    
    <div class="learning-path">
        <!-- 此处填写Web前端学习路径的具体内容 -->
        <div class="path-step">
            <h3 class="path-step-title">第一阶段：HTML基础</h3>
            <p>学习HTML的基本元素和结构，创建简单的网页布局。</p>
            
            <div class="skill-list">
                <span class="skill">HTML标签</span>
                <span class="skill">页面结构</span>
                <span class="skill">链接与图片</span>
                <span class="skill">表单基础</span>
            </div>
            
            <div class="resources">
                <span class="resource-badge">视频教程: 12</span>
                <span class="resource-badge">练习项目: 8</span>
                <span class="resource-badge">预计时间: 3周</span>
            </div>
        </div>
        
        <!-- 更多步骤可继续添加 -->
    </div>
    
    <div style="text-align: center; margin-top: var(--space-lg);">
        <a href="#" class="btn btn-learning">开始学习 Web前端</a>
    </div>
</div>

<!-- C++学习路径 -->
<div class="tab-content" id="cpp">
    <div class="path-overview">
        <h2 class="path-title">C++编程学习路径</h2>
        <p>C++是一种强大的编程语言，广泛用于系统软件、游戏开发和高性能应用程序，适合有一定编程基础的学生进阶学习。</p>
        
        <div class="level-indicator">
            <span class="level-label">难度：</span>
            <div class="level-dots">
                <div class="level-dot active"></div>
                <div class="level-dot active"></div>
                <div class="level-dot active"></div>
                <div class="level-dot active"></div>
                <div class="level-dot"></div>
            </div>
        </div>
        
        <div class="level-indicator">
            <span class="level-label">适合年龄：</span>
            <span>14岁以上</span>
        </div>
    </div>
    
    <div class="learning-path">
        <!-- 此处填写C++学习路径的具体内容 -->
        <div class="path-step">
            <h3 class="path-step-title">第一阶段：C++基础</h3>
            <p>学习C++环境配置，基本语法和简单程序编写。</p>
            
            <div class="skill-list">
                <span class="skill">开发环境</span>
                <span class="skill">基本语法</span>
                <span class="skill">数据类型</span>
                <span class="skill">控制流程</span>
            </div>
            
            <div class="resources">
                <span class="resource-badge">视频教程: 15</span>
                <span class="resource-badge">练习项目: 10</span>
                <span class="resource-badge">预计时间: 5周</span>
            </div>
        </div>
        
        <!-- 更多步骤可继续添加 -->
    </div>
    
    <div style="text-align: center; margin-top: var(--space-lg);">
        <a href="#" class="btn btn-learning">开始学习 C++</a>
    </div>
</div>

<?php
// 页面特定脚本
$extraScripts = <<<HTML
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // 标签页切换功能
        const tabBtns = document.querySelectorAll('.tab-btn');
        const tabContents = document.querySelectorAll('.tab-content');
        
        tabBtns.forEach(function(btn) {
            btn.addEventListener('click', function() {
                // 移除所有活动状态
                tabBtns.forEach(b => b.classList.remove('active'));
                tabContents.forEach(c => c.classList.remove('active'));
                
                // 添加当前活动状态
                btn.classList.add('active');
                const tabId = btn.getAttribute('data-tab');
                document.getElementById(tabId).classList.add('active');
            });
        });
    });
</script>
HTML;

include TEMPLATES_PATH . '/footer.php';
?> 