# 少儿编程成就展示系统

## 📖 项目简介

少儿编程成就展示系统是面向6-16岁青少年的编程学习成就管理平台。系统专注于记录和展示学生的各类编程成就，由管理员进行统一管理。

### 🎯 核心价值

- **成就展示**：记录并展示学生编程成就，提升学习积极性和成就感
- **简单管理**：管理员可轻松添加和管理学生成就记录

## 🧰 技术栈

- **前端**：HTML5 + CSS3 + JavaScript + Bootstrap 5，多巴胺设计风格
- **后端**：PHP 8.0+，轻量级MVC架构
- **数据库**：MySQL 5.7+
- **服务器**：Nginx + PHP-FPM
- **部署**：宝塔面板快速部署

## 📁 项目结构

```
/网站根目录
├── index.php         # 入口文件 ✅
├── config.php        # 配置文件 ✅
├── routes.php        # 路由配置 ✅
├── .htaccess         # Nginx配置 ✅
├── .install_lock     # 安装锁定文件 ✅
├── install/          # 安装系统 ✅
│   ├── index.php     # 安装向导入口 ✅
│   ├── templates/    # 安装界面模板 ✅
│   ├── database.sql  # 数据库结构 ✅
│   └── installer.php # 安装程序类 ✅
├── core/             # 核心类库 
│   ├── Database.php  # 数据库连接类 ✅
│   ├── Router.php    # 路由类 ✅
│   └── helper.php    # 辅助函数 ✅
├── controllers/      # 控制器 
│   ├── UserController.php      # 用户控制器 ✅
│   ├── AdminController.php     # 管理员控制器 ✅
│   └── AchievementController.php # 成就控制器 ✅
├── models/           # 数据模型 
│   ├── Model.php       # 基础模型类 ✅
│   ├── User.php        # 用户模型 ✅
│   ├── Student.php     # 学生模型 ✅
│   ├── Achievement.php # 成就模型 ✅
│   └── Setting.php     # 系统设置模型 ✅
├── views/            # 视图模板
│   ├── header.php ✅
│   ├── footer.php ✅
│   ├── home.php ✅
│   ├── login.php ✅
│   ├── 404.php ✅
│   ├── achievements.php ✅
│   ├── student_profile.php ✅
│   ├── student_achievements.php ✅
│   ├── user_profile.php ✅
│   └── admin/ ✅
│       ├── dashboard.php ✅
│       ├── students.php ✅
│       ├── list_students.php ✅
│       ├── add_student.php ✅
│       ├── edit_student.php ✅
│       ├── achievements.php ✅
│       ├── achievement_form.php ✅
│       ├── statistics.php ✅ 
│       ├── settings.php ✅
│       └── student_achievements.php ✅
├── assets/           # 静态资源
│   ├── css/ ✅
│   │   ├── style.css       # 主样式 ✅
│   │   ├── dark-theme.css  # 深色主题 ✅
│   │   └── light-theme.css # 浅色主题 ✅
│   ├── js/
│   │   └── theme-switcher.js # 主题切换脚本 ✅
│   └── images/ ✅
├── uploads/          # 上传文件 ✅
└── 参考样式/          # 设计参考 ✅
```

## 🧩 开发状态

### 已完成功能

1. **系统框架**
   - 系统入口与配置 ✅
   - 安装向导 ✅
   - 核心类库 ✅
   - 主题切换 ✅

2. **管理员功能**
   - 登录系统 ✅
   - 仪表盘页面 ✅
   - 学生管理 ✅ 
     - 学生列表 ✅
     - 添加学生 ✅
     - 编辑学生 ✅
   - 成就管理 (部分完成)
     - 成就列表 ✅
     - 添加成就 ✅
     - 编辑成就 ✅
   - 系统设置 ✅
   - 数据统计 ✅

3. **模型层**
   - 基础模型类 ✅
   - 用户模型 ✅
   - 学生模型 ✅ 
   - 成就模型 ✅
   - 设置模型 ✅

4. **前台界面**
   - 首页 ✅
   - 登录页 ✅
   - 主题切换 ✅
   - 公共成就展示 ✅
   - 个人成就页面 ✅

5. **文件功能**
   - 附件上传功能 ✅
   - 证书图片显示 ✅

6. **其他功能**
   - 个人资料页 ✅

### 待完成功能

所有功能已完成 ✅

## 🚀 部署与安装

### 系统要求
   - PHP 8.0或更高版本
   - MySQL 5.7或更高版本
   - 支持mod_rewrite的Web服务器
- 共享主机即可满足需求

### 安装步骤
1. 上传所有文件到网站根目录
2. 访问 `http://您的域名/install/`
3. 按照安装向导操作完成配置
4. 安装完成后，自动删除或锁定安装目录


## 📞 联系与支持

- 技术支持：support@example.com
- 项目维护：1697391069@qq.com