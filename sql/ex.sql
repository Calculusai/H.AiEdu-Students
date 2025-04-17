-- 添加考试类别和级别数据
-- 图形化考级
INSERT INTO `exam_categories` (`category_name`, `description`) VALUES 
('图形化编程', '使用图形化工具进行编程的考级');

-- 图形化考级的四个级别
INSERT INTO `exam_levels` (`category_id`, `level_name`, `description`, `level_order`) VALUES
((SELECT id FROM `exam_categories` WHERE `category_name` = '图形化编程'), '1级', '图形化编程1级考试', 1),
((SELECT id FROM `exam_categories` WHERE `category_name` = '图形化编程'), '2级', '图形化编程2级考试', 2),
((SELECT id FROM `exam_categories` WHERE `category_name` = '图形化编程'), '3级', '图形化编程3级考试', 3),
((SELECT id FROM `exam_categories` WHERE `category_name` = '图形化编程'), '4级', '图形化编程4级考试', 4);

-- Python考级
INSERT INTO `exam_categories` (`category_name`, `description`) VALUES 
('Python编程', 'Python编程语言的考级');

-- Python考级的六个级别
INSERT INTO `exam_levels` (`category_id`, `level_name`, `description`, `level_order`) VALUES
((SELECT id FROM `exam_categories` WHERE `category_name` = 'Python编程'), '1级', 'Python编程1级考试', 1),
((SELECT id FROM `exam_categories` WHERE `category_name` = 'Python编程'), '2级', 'Python编程2级考试', 2),
((SELECT id FROM `exam_categories` WHERE `category_name` = 'Python编程'), '3级', 'Python编程3级考试', 3),
((SELECT id FROM `exam_categories` WHERE `category_name` = 'Python编程'), '4级', 'Python编程4级考试', 4),
((SELECT id FROM `exam_categories` WHERE `category_name` = 'Python编程'), '5级', 'Python编程5级考试', 5),
((SELECT id FROM `exam_categories` WHERE `category_name` = 'Python编程'), '6级', 'Python编程6级考试', 6);

-- C++考级
INSERT INTO `exam_categories` (`category_name`, `description`) VALUES 
('C++编程', 'C++编程语言的考级');

-- C++考级的八个级别
INSERT INTO `exam_levels` (`category_id`, `level_name`, `description`, `level_order`) VALUES
((SELECT id FROM `exam_categories` WHERE `category_name` = 'C++编程'), '1级', 'C++编程1级考试', 1),
((SELECT id FROM `exam_categories` WHERE `category_name` = 'C++编程'), '2级', 'C++编程2级考试', 2),
((SELECT id FROM `exam_categories` WHERE `category_name` = 'C++编程'), '3级', 'C++编程3级考试', 3),
((SELECT id FROM `exam_categories` WHERE `category_name` = 'C++编程'), '4级', 'C++编程4级考试', 4),
((SELECT id FROM `exam_categories` WHERE `category_name` = 'C++编程'), '5级', 'C++编程5级考试', 5),
((SELECT id FROM `exam_categories` WHERE `category_name` = 'C++编程'), '6级', 'C++编程6级考试', 6),
((SELECT id FROM `exam_categories` WHERE `category_name` = 'C++编程'), '7级', 'C++编程7级考试', 7),
((SELECT id FROM `exam_categories` WHERE `category_name` = 'C++编程'), '8级', 'C++编程8级考试', 8);

-- 机器人考级
INSERT INTO `exam_categories` (`category_name`, `description`) VALUES 
('机器人编程', '机器人编程和控制的考级');

-- 机器人考级的八个级别
INSERT INTO `exam_levels` (`category_id`, `level_name`, `description`, `level_order`) VALUES
((SELECT id FROM `exam_categories` WHERE `category_name` = '机器人编程'), '1级', '机器人编程1级考试', 1),
((SELECT id FROM `exam_categories` WHERE `category_name` = '机器人编程'), '2级', '机器人编程2级考试', 2),
((SELECT id FROM `exam_categories` WHERE `category_name` = '机器人编程'), '3级', '机器人编程3级考试', 3),
((SELECT id FROM `exam_categories` WHERE `category_name` = '机器人编程'), '4级', '机器人编程4级考试', 4),
((SELECT id FROM `exam_categories` WHERE `category_name` = '机器人编程'), '5级', '机器人编程5级考试', 5),
((SELECT id FROM `exam_categories` WHERE `category_name` = '机器人编程'), '6级', '机器人编程6级考试', 6),
((SELECT id FROM `exam_categories` WHERE `category_name` = '机器人编程'), '7级', '机器人编程7级考试', 7),
((SELECT id FROM `exam_categories` WHERE `category_name` = '机器人编程'), '8级', '机器人编程8级考试', 8);