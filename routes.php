<?php
// 首页和认证路由
$app->get('/', 'AchievementController@index');
$app->get('/login', 'UserController@login');
$app->post('/login', 'UserController@login');
$app->get('/logout', 'UserController@logout');

// 管理员路由
$app->get('/admin', 'AdminController@index');
$app->get('/admin/dashboard', 'AdminController@dashboard');
$app->get('/admin/students', 'AdminController@listStudents');
$app->get('/admin/students/add', 'AdminController@addStudentForm');
$app->post('/admin/add_student', 'AdminController@addStudent');
$app->post('/admin/students/import','AdminController@importStudents');
$app->get('/admin/students/export', 'AdminController@exportStudents');
$app->get('/admin/students/view/{id}', 'AdminController@viewStudent');
$app->get('/admin/students/edit/{id}', 'AdminController@editStudentForm');
$app->post('/admin/students/edit/{id}', 'AdminController@editStudent');
$app->post('/admin/students/delete/{id}', 'AdminController@deleteStudent');
$app->get('/admin/students/achievements/{id}', 'AdminController@studentAchievements');
$app->get('/admin/student_achievements/{id}', 'AdminController@studentAchievements');
$app->post('/admin/import_students', 'AdminController@importStudents');
$app->post('/admin/students/bulk_action', 'AdminController@bulkAction');
$app->get('/admin/download_template', 'AdminController@downloadTemplate');
$app->get('/admin/achievements', 'AdminController@listAchievements');
$app->get('/admin/achievements/add', 'AdminController@showAddAchievement');
$app->post('/admin/achievements/add', 'AdminController@addAchievement');
$app->get('/admin/achievements/edit/{id}', 'AdminController@showEditAchievement');
$app->post('/admin/achievements/edit/{id}', 'AdminController@updateAchievement');
$app->post('/admin/achievements/delete/{id}', 'AdminController@deleteAchievement');

// 系统设置路由
$app->get('/admin/settings', 'AdminController@showSettings');
$app->post('/admin/settings/save', 'AdminController@saveSettings');
$app->post('/admin/settings/reset', 'AdminController@resetSettings');

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

// 404错误处理
$app->notFound(function() {
    // 设置HTTP状态码
    http_response_code(404);
    
    // 加载404视图
    $page_title = '页面未找到';
    include_once VIEW_PATH . '/404.php';
}); 