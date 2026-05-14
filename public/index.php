<?php

session_start();

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/Auth.php';

# Basic Routing
echo $_SERVER['REQUEST_URI'];
$url = $_GET['url'] ?? '';
$url = trim($url, '/');
echo "$url <br>";

switch ($url) {
    case '':
        require_once __DIR__ . '/../app/controllers/HomeController.php';
        $controller = new HomeController();
        $controller->showHompage();
        break;
    case 'login':
        require_once __DIR__ . '/../app/controllers/AuthController.php';
        $controller = new AuthController();
        $controller->showLogin();
        break;
    case 'login-submit':
        require_once __DIR__ . '/../app/controllers/AuthController.php';
        $controller = new AuthController();
        $controller->login();
        break;

    case 'stock-movements':
        require_once __DIR__ . '/../app/controllers/StockMovementController.php';
        $controller = new StockMovementController();
        $controller->index();
        break;
    case 'stock-movements/create':
        require_once __DIR__ . '/../app/controllers/StockMovementController.php';
        require_once __DIR__ . '/../app/middlewares/AuthMiddleware.php';
        AuthMiddleware::check();
        $controller = new StockMovementController();
        $controller->createMovement();
        break;
    case 'stock-movements/submit-movement':
        require_once __DIR__ . '/../app/controllers/StockMovementController.php';
        require_once __DIR__ . '/../app/middlewares/AuthMiddleware.php';
        AuthMiddleware::check();
        $controller = new StockMovementController();
        $controller->store();
        break;
    case 'categories':
        require_once __DIR__ . '/../app/controllers/CategoryController.php';
        $controller = new CategoriesController();
        $controller->index();
        break;

    case 'categories/submit':
        require_once __DIR__ . '/../app/controllers/CategoryController.php';
        $controller = new CategoriesController();
        $controller->createCategory();
        break;

    case 'products':
        require_once __DIR__ . '/../app/controllers/ProductController.php';
        $controller = new ProductController();
        $controller->index();
        break;

    case 'products/submit':
        require_once __DIR__ . '/../app/controllers/ProductController.php';
        $controller = new ProductController();
        $controller->createProduct();
        break;
    default:
        http_response_code(404);
        echo '404 - Page not found';
        break;
}