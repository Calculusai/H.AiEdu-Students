<?php
// 首页和认证路由
$app->get('/', 'AchievementController@index');
$app->get('/login', 'UserController@login');
$app->post('/login', 'UserController@login');
$app->get('/logout', 'UserController@logout');

// 管理员路由
$app->get('/admin', 'AdminController@index');
$app->get('/admin/dashboard', 'AdminController@dashboard');
$app->get('/admin/students', 'AdminController@students');
$app->get('/admin/add_student', 'AdminController@addStudentForm');
$app->get('/admin/students/add', 'AdminController@addStudentForm');
$app->post('/admin/add_student', 'AdminController@addStudent');
$app->get('/admin/edit_student/{id}', 'AdminController@editStudentForm');
$app->post('/admin/edit_student/{id}', 'AdminController@editStudent');
$app->get('/admin/students/edit/{id}', 'AdminController@editStudentForm');
$app->post('/admin/students/edit/{id}', 'AdminController@editStudent');
$app->post('/admin/delete_student/{id}', 'AdminController@deleteStudent');
$app->post('/admin/students/delete/{id}', 'AdminController@deleteStudent');
$app->get('/admin/students/achievements/{id}', 'AdminController@studentAchievements');
$app->post('/admin/import_students', 'AdminController@importStudents');
$app->get('/admin/download_template', 'AdminController@downloadTemplate');
$app->get('/admin/achievements', 'AdminController@achievements');
$app->get('/admin/add_achievement', 'AdminController@showAddAchievement');
$app->post('/admin/add_achievement', 'AdminController@addAchievement');
$app->get('/admin/edit_achievement/{id}', 'AdminController@editAchievementForm');
$app->post('/admin/edit_achievement/{id}', 'AdminController@editAchievement');
$app->post('/admin/delete_achievement/{id}', 'AdminController@deleteAchievement');

// 系统设置路由
$app->get('/admin/settings', 'AdminController@showSettings');
$app->post('/admin/save_settings', 'AdminController@saveSettings');
$app->post('/admin/reset_settings', 'AdminController@resetSettings');

// 数据统计路由
$app->get('/admin/statistics', 'AdminController@showStatistics');

// 用户个人资料相关路由
$app->get('/profile', 'UserController@profile');
$app->post('/profile/update', 'UserController@updateProfile');

// 前台成就展示路由
$app->get('/achievements', 'AchievementController@showPublicAchievements');
$app->get('/student/{id}', 'AchievementController@showStudentProfile');

// API路由
$app->post('/api/save-theme-preference', function() {
    // 返回成功响应，但实际上不改变任何设置（只保留浅色主题）
    json_response(['success' => true, 'theme' => 'light']);
}); 