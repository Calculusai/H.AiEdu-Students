-- 创建数据库（如果不存在）
CREATE DATABASE IF NOT EXISTS `students_hoshino` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE `students_hoshino`;

-- 学生信息表
CREATE TABLE IF NOT EXISTS `students` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL COMMENT '姓名',
  `gender` enum('男','女') DEFAULT NULL COMMENT '性别',
  `birth_date` date DEFAULT NULL COMMENT '出生日期',
  `parent_name` varchar(50) DEFAULT NULL COMMENT '家长姓名',
  `parent_phone` varchar(20) DEFAULT NULL COMMENT '家长电话',
  `account_id` int(11) DEFAULT NULL COMMENT '关联账号ID',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `account_id` (`account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='学生信息表';

-- 荣誉记录表
CREATE TABLE IF NOT EXISTS `honors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL COMMENT '学生ID',
  `honor_title` varchar(100) NOT NULL COMMENT '荣誉名称',
  `honor_type` varchar(30) DEFAULT NULL COMMENT '荣誉类型',
  `honor_date` date DEFAULT NULL COMMENT '获得日期',
  `description` text DEFAULT NULL COMMENT '描述',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `honors_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='荣誉记录表';

-- 学习路径表
CREATE TABLE IF NOT EXISTS `learning_paths` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL COMMENT '学生ID',
  `path_name` varchar(100) NOT NULL COMMENT '路径名称',
  `description` text DEFAULT NULL COMMENT '路径说明',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `learning_paths_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='学习路径表';

-- 学习规划表
CREATE TABLE IF NOT EXISTS `learning_plans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL COMMENT '学生ID',
  `plan_title` varchar(100) NOT NULL COMMENT '规划标题',
  `goal` text DEFAULT NULL COMMENT '阶段目标',
  `progress` int(3) DEFAULT 0 COMMENT '当前进度',
  `result` text DEFAULT NULL COMMENT '阶段成果',
  `start_date` date DEFAULT NULL COMMENT '开始时间',
  `end_date` date DEFAULT NULL COMMENT '结束时间',
  `status` enum('未开始','进行中','已完成','已逾期') NOT NULL DEFAULT '未开始' COMMENT '完成状态',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `learning_plans_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='学习规划表';

-- 考试级别类别表
CREATE TABLE IF NOT EXISTS `exam_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(50) NOT NULL COMMENT '类别名称',
  `description` text DEFAULT NULL COMMENT '类别描述',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `category_name` (`category_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='考试级别类别表';

-- 考试级别表
CREATE TABLE IF NOT EXISTS `exam_levels` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL COMMENT '类别ID',
  `level_name` varchar(50) NOT NULL COMMENT '级别名称',
  `description` text DEFAULT NULL COMMENT '级别描述',
  `level_order` int(11) NOT NULL COMMENT '级别顺序',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `exam_levels_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `exam_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='考试级别表';

-- 成绩记录表
CREATE TABLE IF NOT EXISTS `scores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL COMMENT '学生ID',
  `exam_level_id` int(11) NOT NULL COMMENT '考级ID',
  `score` decimal(5,2) DEFAULT NULL COMMENT '分数',
  `exam_date` date DEFAULT NULL COMMENT '考试日期',
  `comment` text DEFAULT NULL COMMENT '评语',
  `certificate_no` varchar(50) DEFAULT NULL COMMENT '证书编号',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  KEY `exam_level_id` (`exam_level_id`),
  CONSTRAINT `scores_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `scores_ibfk_2` FOREIGN KEY (`exam_level_id`) REFERENCES `exam_levels` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='成绩记录表';

-- 学习进度表
CREATE TABLE IF NOT EXISTS `learning_progress` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL COMMENT '学生ID',
  `resource_id` int(11) DEFAULT NULL COMMENT '资源ID',
  `resource_title` varchar(100) DEFAULT NULL COMMENT '资源标题',
  `progress` int(3) DEFAULT 0 COMMENT '进度百分比',
  `last_studied` datetime DEFAULT NULL COMMENT '最后学习时间',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `learning_progress_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='学习进度表';

-- 用户账号表
CREATE TABLE IF NOT EXISTS `accounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL COMMENT '用户名',
  `password` varchar(255) NOT NULL COMMENT '密码',
  `email` varchar(100) DEFAULT NULL COMMENT '邮箱',
  `phone` varchar(20) DEFAULT NULL COMMENT '电话',
  `role` enum('student','parent','teacher','admin') NOT NULL DEFAULT 'student' COMMENT '角色',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态',
  `avatar` varchar(255) DEFAULT NULL COMMENT '头像',
  `email_notifications` tinyint(1) NOT NULL DEFAULT 1 COMMENT '邮件通知',
  `sms_notifications` tinyint(1) NOT NULL DEFAULT 0 COMMENT '短信通知',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户账号表';

-- 教师信息表
CREATE TABLE IF NOT EXISTS `teachers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL COMMENT '姓名',
  `specialty` varchar(100) DEFAULT NULL COMMENT '专长语言',
  `account_id` int(11) DEFAULT NULL COMMENT '关联账号ID',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `account_id` (`account_id`),
  CONSTRAINT `teachers_ibfk_1` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='教师信息表';

-- 创建管理员账号
INSERT INTO `accounts` (`username`, `password`, `email`, `role`, `status`, `email_notifications`, `sms_notifications`) VALUES
('admin', '$2y$10$1pQX3zIoM6/1W1lWFERywOHDG6JXjWVmRElk2NR0VEcyqo1.LAJhW', 'admin@example.com', 'admin', 1, 1, 0);
-- 注意：上面的密码是 'admin123' 的哈希值 

-- 课程表
CREATE TABLE IF NOT EXISTS `courses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL COMMENT '课程标题',
  `description` text NOT NULL COMMENT '课程描述',
  `difficulty` varchar(20) NOT NULL DEFAULT 'beginner' COMMENT '难度级别',
  `category` varchar(50) NOT NULL COMMENT '课程类别',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `title` (`title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='课程表';

-- 课程内容表
CREATE TABLE IF NOT EXISTS `course_content` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `course_id` int(11) NOT NULL COMMENT '所属课程ID',
  `title` varchar(100) NOT NULL COMMENT '内容标题',
  `content_type` enum('text','markdown','code','quiz','video') NOT NULL DEFAULT 'text' COMMENT '内容类型',
  `content` text NOT NULL COMMENT '实际内容',
  `sequence` int(11) NOT NULL COMMENT '序号',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `course_id` (`course_id`),
  CONSTRAINT `course_content_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='课程内容表';

-- 学习路径-课程关联表
CREATE TABLE IF NOT EXISTS `learning_path_courses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `path_id` int(11) NOT NULL COMMENT '学习路径ID',
  `course_id` int(11) NOT NULL COMMENT '课程ID',
  `order` int(11) NOT NULL COMMENT '顺序',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `path_id` (`path_id`),
  KEY `course_id` (`course_id`),
  CONSTRAINT `learning_path_courses_ibfk_1` FOREIGN KEY (`path_id`) REFERENCES `learning_paths` (`id`) ON DELETE CASCADE,
  CONSTRAINT `learning_path_courses_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='学习路径-课程关联表';

-- 用户课程表
CREATE TABLE IF NOT EXISTS `user_courses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT '用户ID',
  `course_id` int(11) NOT NULL COMMENT '课程ID',
  `progress` int(3) DEFAULT 0 COMMENT '完成进度',
  `start_date` datetime DEFAULT NULL COMMENT '开始学习日期',
  `complete_date` datetime DEFAULT NULL COMMENT '完成日期',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_course` (`user_id`,`course_id`),
  KEY `course_id` (`course_id`),
  CONSTRAINT `user_courses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_courses_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户课程表';

-- 用户学习进度表
CREATE TABLE IF NOT EXISTS `user_progress` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT '用户ID',
  `content_id` int(11) NOT NULL COMMENT '内容ID',
  `completed` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否完成',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_content` (`user_id`,`content_id`),
  KEY `content_id` (`content_id`),
  CONSTRAINT `user_progress_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_progress_ibfk_2` FOREIGN KEY (`content_id`) REFERENCES `course_content` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户学习进度表';

-- 考试成绩表
CREATE TABLE IF NOT EXISTS `exam_scores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL COMMENT '学生ID',
  `exam_level_id` int(11) NOT NULL COMMENT '考试级别ID',
  `score` decimal(5,2) NOT NULL COMMENT '分数',
  `exam_date` date NOT NULL COMMENT '考试日期',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  KEY `exam_level_id` (`exam_level_id`),
  CONSTRAINT `exam_scores_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `exam_scores_ibfk_2` FOREIGN KEY (`exam_level_id`) REFERENCES `exam_levels` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='考试成绩表';

-- 课程统计表
CREATE TABLE IF NOT EXISTS `course_stats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `course_id` int(11) NOT NULL COMMENT '课程ID',
  `user_id` int(11) NOT NULL COMMENT '学生ID',
  `progress` int(3) DEFAULT 0 COMMENT '学习进度',
  `complete_status` tinyint(1) DEFAULT 0 COMMENT '完成状态',
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `course_user` (`course_id`,`user_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `course_stats_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `course_stats_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='课程统计表';

-- 创建触发器以在用户课程表更新时自动更新课程统计
DELIMITER //
CREATE TRIGGER update_course_stats_after_user_course_insert
AFTER INSERT ON user_courses
FOR EACH ROW
BEGIN
    -- 检查统计表中是否存在该课程和用户的记录
    DECLARE stats_exists INT;
    SELECT COUNT(*) INTO stats_exists FROM course_stats WHERE course_id = NEW.course_id AND user_id = NEW.user_id;
    
    IF stats_exists > 0 THEN
        -- 如果已存在，更新统计信息
        UPDATE course_stats 
        SET progress = NEW.progress,
            complete_status = CASE WHEN NEW.progress = 100 THEN 1 ELSE 0 END
        WHERE course_id = NEW.course_id AND user_id = NEW.user_id;
    ELSE
        -- 如果不存在，创建新记录
        INSERT INTO course_stats (course_id, user_id, progress, complete_status)
        VALUES (
            NEW.course_id,
            NEW.user_id,
            NEW.progress,
            CASE WHEN NEW.progress = 100 THEN 1 ELSE 0 END
        );
    END IF;
END //

CREATE TRIGGER update_course_stats_after_user_course_update
AFTER UPDATE ON user_courses
FOR EACH ROW
BEGIN
    -- 更新统计信息
    UPDATE course_stats 
    SET progress = NEW.progress,
        complete_status = CASE WHEN NEW.progress = 100 THEN 1 ELSE 0 END
    WHERE course_id = NEW.course_id AND user_id = NEW.user_id;
END //

CREATE TRIGGER update_course_stats_after_user_course_delete
AFTER DELETE ON user_courses
FOR EACH ROW
BEGIN
    -- 删除对应的统计记录
    DELETE FROM course_stats WHERE course_id = OLD.course_id AND user_id = OLD.user_id;
END //
DELIMITER ;

