<?php
/**
 * 路由处理类
 */
class Router {
    private $routes = [];
    private $notFoundCallback;
    
    /**
     * 添加路由
     *
     * @param string $method HTTP方法
     * @param string $path 路径
     * @param callable $callback 回调函数
     * @return void
     */
    public function add($method, $path, $callback) {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'callback' => $callback
        ];
    }
    
    /**
     * 添加GET路由
     *
     * @param string $path 路径
     * @param callable $callback 回调函数
     * @return void
     */
    public function get($path, $callback) {
        $this->add('GET', $path, $callback);
    }
    
    /**
     * 添加POST路由
     *
     * @param string $path 路径
     * @param callable $callback 回调函数
     * @return void
     */
    public function post($path, $callback) {
        $this->add('POST', $path, $callback);
    }
    
    /**
     * 设置404回调函数
     *
     * @param callable $callback 回调函数
     * @return void
     */
    public function notFound($callback) {
        $this->notFoundCallback = $callback;
    }
    
    /**
     * 检查路径是否匹配
     *
     * @param string $pattern 路由模式
     * @param string $path 请求路径
     * @return array|false 参数数组或false
     */
    private function matchPath($pattern, $path) {
        // 将路由模式转换为正则表达式
        $pattern = preg_replace('/\/{([\w-]+)}/', '/(?P<$1>[^/]+)', $pattern);
        $pattern = '#^' . $pattern . '$#';
        
        if (preg_match($pattern, $path, $matches)) {
            // 过滤掉数字键
            $params = array_filter($matches, function($key) {
                return !is_numeric($key);
            }, ARRAY_FILTER_USE_KEY);
            
            return $params;
        }
        
        return false;
    }
    
    /**
     * 分发请求
     *
     * @return void
     */
    public function dispatch() {
        // 获取当前请求方法和路径
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // 去除尾部斜杠
        $path = rtrim($path, '/') ?: '/';
        
        // 尝试匹配路由
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }
            
            $params = $this->matchPath($route['path'], $path);
            
            if ($params !== false) {
                // 路由匹配成功
                $callback = $route['callback'];
                
                // 如果回调是字符串格式的"控制器@方法"
                if (is_string($callback) && strpos($callback, '@') !== false) {
                    list($controller, $method) = explode('@', $callback);
                    
                    // 加载控制器文件
                    $controllerFile = CONTROLLER_PATH . '/' . $controller . '.php';
                    if (file_exists($controllerFile)) {
                        require_once $controllerFile;
                        
                        // 实例化控制器
                        $controllerInstance = new $controller();
                        
                        // 调用方法
                        call_user_func_array([$controllerInstance, $method], array_values($params));
                    } else {
                        // 控制器文件不存在
                        $this->handleNotFound("控制器文件 {$controllerFile} 不存在");
                    }
                } else {
                    // 直接调用回调函数
                    call_user_func_array($callback, array_values($params));
                }
                return;
            }
        }
        
        // 没有匹配的路由，调用404回调
        $this->handleNotFound();
    }
    
    /**
     * 处理未找到路由的情况
     *
     * @param string $message 错误信息
     * @return void
     */
    private function handleNotFound($message = null) {
        if ($this->notFoundCallback) {
            call_user_func($this->notFoundCallback, $message);
        } else {
            // 默认404处理
            header('HTTP/1.1 404 Not Found');
            echo '<h1>404 页面未找到</h1>';
            if ($message && defined('DEBUG_MODE') && DEBUG_MODE) {
                echo '<p>' . $message . '</p>';
            }
            echo '<p>您请求的页面不存在。</p>';
            echo '<p><a href="' . site_url() . '">返回首页</a></p>';
        }
    }
    
    /**
     * 初始化路由表
     *
     * @return void
     */
    public function init() {
        global $db;
        
        // 首页
        $this->get('/', function() {
            require_once VIEW_PATH . '/home.php';
        });
        
        // 登录页
        $this->get('/login', function() {
            if (is_logged_in()) {
                redirect(site_url('/dashboard'));
            }
            require_once VIEW_PATH . '/login.php';
        });
        
        // 登录处理
        $this->post('/login', function() {
            require_once CONTROLLER_PATH . '/UserController.php';
            $userController = new UserController();
            $userController->login();
        });
        
        // 登出
        $this->get('/logout', function() {
            require_once CONTROLLER_PATH . '/UserController.php';
            $userController = new UserController();
            $userController->logout();
        });
        
        // 学生成就展示
        $this->get('/achievements', function() {
            require_once CONTROLLER_PATH . '/AchievementController.php';
            $achievementController = new AchievementController();
            $achievementController->listPublicAchievements();
        });
        
        // 学生个人成就
        $this->get('/student/{id}', function($id) {
            require_once CONTROLLER_PATH . '/AchievementController.php';
            $achievementController = new AchievementController();
            $achievementController->listStudentAchievements($id);
        });
        
        // 管理后台
        $this->get('/admin', function() {
            if (!is_admin()) {
                redirect(site_url('/login'));
            }
            require_once VIEW_PATH . '/admin/dashboard.php';
        });
        
        // 学生管理
        $this->get('/admin/students', function() {
            if (!is_admin()) {
                redirect(site_url('/login'));
            }
            require_once CONTROLLER_PATH . '/AdminController.php';
            $adminController = new AdminController();
            $adminController->listStudents();
        });
        
        // 成就管理
        $this->get('/admin/achievements', function() {
            if (!is_admin()) {
                redirect(site_url('/login'));
            }
            require_once CONTROLLER_PATH . '/AdminController.php';
            $adminController = new AdminController();
            $adminController->listAchievements();
        });
        
        // 添加成就页面
        $this->get('/admin/achievements/add', function() {
            if (!is_admin()) {
                redirect(site_url('/login'));
            }
            require_once CONTROLLER_PATH . '/AdminController.php';
            $adminController = new AdminController();
            $adminController->showAddAchievement();
        });
        
        // 添加成就处理
        $this->post('/admin/achievements/add', function() {
            if (!is_admin()) {
                redirect(site_url('/login'));
            }
            require_once CONTROLLER_PATH . '/AdminController.php';
            $adminController = new AdminController();
            $adminController->addAchievement();
        });
        
        // 编辑成就页面
        $this->get('/admin/achievements/edit/{id}', function($id) {
            if (!is_admin()) {
                redirect(site_url('/login'));
            }
            require_once CONTROLLER_PATH . '/AdminController.php';
            $adminController = new AdminController();
            $adminController->showEditAchievement($id);
        });
        
        // 编辑成就处理
        $this->post('/admin/achievements/edit/{id}', function($id) {
            if (!is_admin()) {
                redirect(site_url('/login'));
            }
            require_once CONTROLLER_PATH . '/AdminController.php';
            $adminController = new AdminController();
            $adminController->updateAchievement($id);
        });
        
        // 删除成就
        $this->post('/admin/achievements/delete/{id}', function($id) {
            if (!is_admin()) {
                redirect(site_url('/login'));
            }
            require_once CONTROLLER_PATH . '/AdminController.php';
            $adminController = new AdminController();
            $adminController->deleteAchievement($id);
        });
        
        // 404 页面
        $this->notFound(function() {
            header('HTTP/1.1 404 Not Found');
            require_once VIEW_PATH . '/404.php';
        });
    }
    
    /**
     * 构造函数
     */
    public function __construct() {
        // 初始化路由表
        $this->init();
    }
} 