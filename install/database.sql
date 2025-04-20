-- 数据库表结构
-- {TABLE_PREFIX} 将在安装时被替换为实际表前缀

-- 用户表
CREATE TABLE IF NOT EXISTS `{TABLE_PREFIX}users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `role` enum('student','admin') NOT NULL DEFAULT 'student',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `theme_preference` varchar(10) NOT NULL DEFAULT 'light',
  `require_password_change` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_login` timestamp NULL DEFAULT NULL,
  `login_count` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 学生信息表
CREATE TABLE IF NOT EXISTS `{TABLE_PREFIX}students` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `student_id` varchar(50) NOT NULL,
  `name` varchar(50) NOT NULL,
  `class_name` varchar(100) DEFAULT NULL,
  `contact` varchar(50) DEFAULT NULL,
  `gender` enum('男','女') DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `grade` varchar(20) DEFAULT NULL,
  `school` varchar(100) DEFAULT NULL,
  `notes` TEXT DEFAULT NULL COMMENT '学生备注信息',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `student_id` (`student_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 成就记录表
CREATE TABLE IF NOT EXISTS `{TABLE_PREFIX}achievements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text,
  `achievement_type` varchar(50) NOT NULL,
  `score` decimal(5,2) DEFAULT NULL,
  `certificate_no` varchar(50) DEFAULT NULL,
  `issue_authority` varchar(100) DEFAULT NULL,
  `achieved_date` date DEFAULT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  KEY `achievement_type` (`achievement_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 会话表
CREATE TABLE IF NOT EXISTS `{TABLE_PREFIX}sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 系统设置表
CREATE TABLE IF NOT EXISTS `{TABLE_PREFIX}settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text,
  `setting_group` varchar(50) DEFAULT 'general',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 插入默认设置
INSERT INTO `{TABLE_PREFIX}settings` (`setting_key`, `setting_value`, `setting_group`) VALUES
('site_name', '少儿编程成就展示系统', 'general'),
('site_description', '记录和展示少儿编程学习成就', 'general'),
('items_per_page', '10', 'general'),
('allow_registration', '0', 'security'),
('enable_achievements_public', '1', 'content');

-- 添加外键约束
ALTER TABLE `{TABLE_PREFIX}students`
  ADD CONSTRAINT `{TABLE_PREFIX}students_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `{TABLE_PREFIX}users` (`id`) ON DELETE CASCADE;

ALTER TABLE `{TABLE_PREFIX}achievements`
  ADD CONSTRAINT `{TABLE_PREFIX}achievements_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `{TABLE_PREFIX}students` (`id`) ON DELETE CASCADE;

ALTER TABLE `{TABLE_PREFIX}sessions`
  ADD CONSTRAINT `{TABLE_PREFIX}sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `{TABLE_PREFIX}users` (`id`) ON DELETE CASCADE; 

-- 登录日志表
CREATE TABLE IF NOT EXISTS `{TABLE_PREFIX}login_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `login_status` enum('success','failed') NOT NULL DEFAULT 'success',
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `login_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `{TABLE_PREFIX}login_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `{TABLE_PREFIX}users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; 