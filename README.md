# 少儿编程成就展示系统

## 📖 项目简介

少儿编程成就展示系统是面向6-16岁青少年的编程学习成就管理平台。系统专注于记录和展示学生的各类编程成就，由管理员进行统一管理。学生可通过个人账号查看自己的成就记录，激发学习动力。

### 🎯 核心价值

- **成就展示**：记录并展示学生编程成就，提升学习积极性和成就感
- **简单管理**：管理员可轻松添加和管理学生成就记录
- **个人档案**：学生可查看个人成就页面，跟踪自己的学习进度
- **证书管理**：支持上传和展示成就证书，便于分享和记录
- **数据分析**：提供基础的数据统计功能，帮助教师了解学生学习情况

## 🧰 技术栈

- **前端**：HTML5 + CSS3 + JavaScript + Bootstrap 5，多巴胺设计风格
- **后端**：PHP 8.0+，轻量级MVC架构
- **数据库**：MySQL 5.7+
- **服务器**：Nginx + PHP-FPM
- **部署**：宝塔面板快速部署
- **安全**：HTTPS加密、XSS防护、CSRF防护

## 💡 主要功能

### 管理员功能
- **学生账号管理**：创建、编辑、删除学生账号，支持批量导入
- **成就管理**：添加、编辑、删除学生成就记录，支持多种成就类型
- **证书上传**：为学生成就上传证书图片，支持多种格式
- **数据统计**：查看学生成就统计和系统使用情况，支持图表展示
- **系统设置**：管理系统基本设置，包括网站标题、logo等

### 学生功能
- **个人资料**：查看和编辑个人信息，上传头像
- **成就展示**：查看个人获得的所有成就，支持时间线展示
- **成就筛选**：按类型、日期等筛选查看成就
- **证书查看**：在线查看和下载成就证书，支持分享
- **密码修改**：修改个人账号密码，提升安全性

## 📁 项目结构

```
/网站根目录
├── index.php         # 系统入口文件 ✅
├── config.php        # 系统配置文件 ✅
├── routes.php        # 路由配置文件 ✅
├── custom_nginx.conf # Nginx服务器配置 ✅
├── .install_lock     # 安装锁定文件 ✅
├── install/          # 安装系统目录 ✅
│   ├── index.php     # 安装向导入口 ✅
│   ├── installer.php # 安装程序类 ✅
│   └── templates/    # 安装界面模板 ✅
│       ├── welcome.php   # 欢迎页面 ✅
│       ├── requirements.php # 环境检测 ✅
│       ├── database.php  # 数据库配置 ✅
│       ├── admin.php     # 管理员设置 ✅
│       ├── finish.php    # 完成安装 ✅
│       ├── header.php    # 模板头部 ✅
│       └── footer.php    # 模板底部 ✅
├── core/             # 核心类库目录 
│   ├── Database.php  # 数据库连接类 ✅
│   ├── Router.php    # 路由处理类 ✅
│   └── helper.php    # 辅助函数库 ✅
├── controllers/      # 控制器目录 
│   ├── UserController.php      # 用户控制器 ✅
│   ├── AdminController.php     # 管理员控制器 ✅
│   └── AchievementController.php # 成就控制器 ✅
├── models/           # 数据模型目录 
│   ├── Model.php       # 基础模型类 ✅
│   ├── User.php        # 用户模型 ✅
│   ├── Student.php     # 学生模型 ✅
│   ├── Achievement.php # 成就模型 ✅
│   └── Setting.php     # 系统设置模型 ✅
├── views/            # 视图模板目录
│   ├── header.php        # 公共页头 ✅
│   ├── footer.php        # 公共页脚 ✅
│   ├── home.php          # 首页模板 ✅
│   ├── login.php         # 登录页面 ✅
│   ├── 404.php           # 错误页面 ✅
│   ├── achievements.php  # 成就展示页 ✅
│   ├── student_profile.php  # 学生个人资料页 ✅
│   ├── student_achievements.php # 学生成就页 ✅
│   ├── user_profile.php     # 用户资料页 ✅
│   ├── templates/        # 公共模板组件 ✅
│   │   ├── admin_sidebar.php  # 管理后台侧边栏 ✅
│   │   └── breadcrumb.php     # 面包屑导航 ✅
│   └── admin/            # 管理后台视图 ✅
│       ├── dashboard.php       # 管理控制台 ✅
│       ├── students.php        # 学生管理主页 ✅
│       ├── add_student.php     # 添加学生 ✅
│       ├── edit_student.php    # 编辑学生 ✅
│       ├── view_student.php    # 查看学生详情 ✅
│       ├── achievements.php    # 成就管理 ✅
│       ├── achievement_form.php  # 成就编辑表单 ✅
│       ├── statistics.php     # 统计数据页面 ✅ 
│       ├── settings.php       # 系统设置页面 ✅
│       └── student_achievements.php  # 学生成就管理 ✅
├── assets/           # 静态资源目录
│   ├── css/          # 样式文件 ✅
│   │   ├── style.css          # 主样式文件 ✅
│   │   ├── navbar.css         # 导航栏样式 ✅
│   │   └── student-detail.css # 学生详情样式 ✅
│   ├── js/           # JavaScript文件 ✅
│   │   ├── main.js            # 主要功能脚本 ✅
│   │   └── navbar.js          # 导航栏脚本 ✅
│   └── images/       # 图片资源 ✅
├── uploads/          # 上传文件目录 ✅
│   └── certificates/ # 成就证书存储 ✅
└── public/           # 公共资源目录 ✅
    └── css/          # 第三方CSS库 ✅
```

## 🧩 开发状态

### 已完成功能

1. **系统框架**
   - 系统入口与配置 ✅
   - 安装向导 ✅
   - 核心类库 ✅
   - 主题切换 ✅
   - 响应式布局 ✅

2. **管理员功能**
   - 登录系统 ✅
   - 仪表盘页面 ✅
   - 学生管理 ✅ 
     - 学生列表 ✅
     - 添加学生 ✅
     - 编辑学生 ✅
     - 查看学生详情 ✅
     - 批量导入学生 ✅
   - 成就管理 ✅
     - 成就列表 ✅
     - 添加成就 ✅
     - 编辑成就 ✅
     - 删除成就 ✅
     - 批量操作 ✅
   - 系统设置 ✅
   - 数据统计 ✅
   - 操作日志 ✅

3. **学生功能**
   - 登录系统 ✅
   - 查看个人资料 ✅
   - 修改个人信息 ✅
   - 查看个人成就 ✅
   - 筛选成就记录 ✅
   - 查看和下载证书 ✅
   - 分享成就 ✅

4. **模型层**
   - 基础模型类 ✅
   - 用户模型 ✅
   - 学生模型 ✅ 
   - 成就模型 ✅
   - 设置模型 ✅
   - 日志模型 ✅

5. **前台界面**
   - 首页 ✅
   - 登录页 ✅
   - 主题切换 ✅
   - 公共成就展示 ✅
   - 个人成就页面 ✅
   - 个人资料页 ✅
   - 移动端适配 ✅

6. **文件功能**
   - 附件上传功能 ✅
   - 证书图片显示 ✅
   - 图片预览 ✅
   - 媒体管理 ✅

7. **系统安全**
   - HTTPS加密 ✅
   - XSS防护 ✅
   - CSRF防护 ✅
   - SQL注入防护 ✅
   - 数据验证 ✅

### 待完成功能

所有功能已完成 ✅

## 🚀 部署与安装

### 系统要求
   - PHP 8.0或更高版本
   - MySQL 5.7或更高版本
   - 支持mod_rewrite的Web服务器
   - 200MB以上磁盘空间
   - 建议使用Linux服务器
- 共享主机即可满足需求

### 安装步骤
1. 上传所有文件到网站根目录
2. 访问 `https://您的域名/install/`
3. 按照安装向导操作完成配置：
   - 环境检测
   - 数据库配置
   - 管理员账号设置
   - 基本系统设置
4. 安装完成后，自动删除或锁定安装目录
5. 通过管理员账号登录后台开始使用

### Nginx配置步骤

1. **创建Nginx配置文件**
   - 复制`custom_nginx.conf`到您的Nginx配置目录
   - 或在宝塔面板中导入此配置

2. **基本网站设置**
   ```nginx
   server {
       listen 80;
       listen 443 ssl;
       listen 443 quic;
       http2 on;
       server_name students.hoshinoai.xin;
       index index.php index.html index.htm;
       root /www/wwwroot/students.hoshinoai.xin;
   }
   ```

3. **配置SSL证书**
   ```nginx
   #HTTP转HTTPS
   if ($server_port !~ 443){
       rewrite ^(/.*)$ https://$host$1 permanent;
   }
   
   ssl_certificate    /path/to/cert/fullchain.pem;
   ssl_certificate_key    /path/to/cert/privkey.pem;
   ssl_protocols TLSv1.1 TLSv1.2 TLSv1.3;
   ```

4. **添加安全headers**
   ```nginx
   add_header X-Content-Type-Options "nosniff";
   add_header X-XSS-Protection "1; mode=block";
   add_header X-Frame-Options "SAMEORIGIN";
   add_header Strict-Transport-Security "max-age=31536000";
   ```

5. **配置PHP处理**
   ```nginx
   # PHP配置
   include enable-php-80.conf;
   ```

6. **URL重写规则（路由配置）**
   ```nginx
   # MVC框架路由
   location / {
       try_files $uri $uri/ /index.php?$query_string;
   }
   ```

7. **静态文件缓存配置**
   ```nginx
   # 图片缓存
   location ~ .*\.(gif|jpg|jpeg|png|bmp|swf)$ {
       expires 30d;
   }
   
   # JS和CSS缓存
   location ~ .*\.(js|css)?$ {
       expires 12h;
   }
   ```

8. **安全限制**
   ```nginx
   # 禁止访问敏感文件
   location ~ ^/(\.user.ini|\.htaccess|\.git|\.env|\.svn|\.project|LICENSE|README.md|config\.php) {
       return 404;
   }
   ```

9. **设置日志路径**
   ```nginx
   access_log  /www/wwwlogs/your-domain.log;
   error_log   /www/wwwlogs/your-domain.error.log;
   ```

10. **重启Nginx服务**
    ```bash
    systemctl restart nginx
    # 或在宝塔面板中重启
    ```

11. **验证配置**
    - 访问网站确认正常运行
    - 检查HTTPS证书是否生效
    - 确认URL重写规则工作正常

## 💻 使用指南

### 管理员
1. 通过安装时创建的管理员账号登录系统（默认后台路径为`/admin`）
2. 在"学生管理"中添加学生账号
   - 可以单个添加或批量导入
   - 初始密码会自动生成
3. 在"成就管理"中为学生添加成就记录
   - 支持多种成就类型：比赛获奖、项目完成、技能认证等
   - 可以添加详细描述和日期
4. 上传成就证书并分配给学生
   - 支持JPG、PNG、PDF等多种格式
   - 证书可在前台展示
5. 查看系统统计数据，了解学生学习情况

### 学生
1. 使用管理员创建的学生账号登录系统
   - 初次登录需修改初始密码
2. 在"个人资料"页面查看和更新个人信息
   - 可以上传个人头像
   - 修改联系方式和基本信息
3. 在"成就展示"页面查看自己获得的成就
   - 支持时间线模式浏览
   - 可以按类别筛选成就
4. 查看和下载成就证书
   - 支持线上预览
   - 可以分享给家长和朋友

## 📱 移动端支持

系统采用响应式设计，完全支持移动端访问：
- 自适应布局，适配不同尺寸屏幕
- 触摸友好的操作界面
- 移动端优化的表单和交互
- 快速加载，节省流量

## 🔒 安全与隐私

- 全站HTTPS加密传输
- 密码加盐哈希存储
- 防XSS和SQL注入攻击
- CSRF防护机制
- 学生数据保密，符合隐私保护要求

## 🔄 更新与维护

- 系统支持在线检查更新
- 定期发布安全补丁和功能更新
- 数据库自动备份功能
- 详细的系统日志记录

## 📞 联系与支持

- 技术支持：support@example.com
- 项目维护：1697391069@qq.com
- 官方网站：https://students.hoshinoai.xin
- 问题反馈：https://github.com/yourusername/students-achievement-system/issues

## 📜 许可证

本项目采用 MIT 许可证开源 - 查看 [LICENSE](LICENSE) 文件了解更多详情。

```
MIT License

Copyright (c) 2023-2024 少儿编程成就展示系统

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction...
```

MIT 许可证是一个宽松的软件许可证，只要用户在软件的所有副本中都包含原始许可证和版权声明，他们就可以处理该软件而没有任何限制。