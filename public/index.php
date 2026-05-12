<?php

session_start();

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/Auth.php';

# Basic Routing
$url = $_GET['url'] ?? '';
$url = trim($url, '/');
echo $url;

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

    default:
        http_response_code(404);
        echo '404 - Page not found';
        break;
}