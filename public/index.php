<?php

session_start();

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/Session.php';
require_once __DIR__ . '/../app/core/Logger.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/controllers/StockMovementController.php';
require_once __DIR__ . '/../app/controllers/InventoryController.php';
require_once __DIR__ . '/../app/controllers/CategoryController.php';
require_once __DIR__ . '/../app/controllers/ProductController.php';
require_once __DIR__ . '/../app/middlewares/AuthMiddleware.php';
require_once __DIR__ . '/../app/core/ErrorHandler.php';
require_once __DIR__ . '/../app/exceptions/SystemException.php';
require_once __DIR__ . '/../app/exceptions/ValidationException.php';
require_once __DIR__ . '/../app/services/TransferService.php';
require_once __DIR__ . '/../app/services/InventoryService.php';
require_once __DIR__ . '/../app/services/ProductService.php';


/*
# Bootstrapping the error handler to catch all errors and exceptions in a centralized way

? bootstraping: Creating a self sustaining process that initializes, builds or runs itself without external help

*/
ErrorHandler::register();


# Basic Routing
$url = $_GET['url'] ?? '';
$url = trim($url, '/');


switch ($url) {
    case '':
    case 'login':
        $controller = new AuthController();
        $controller->index();
        break;

    case 'logout':
        $controller = new AuthController();
        $controller->logout();
        break;
    case 'login/form-submit':
        $controller = new AuthController();
        $controller->login();
        break;

    case 'stock-movements':
        AuthMiddleware::check();
        $controller = new StockMovementController();
        $controller->index();
        break;
    case 'inventory-overview':
        AuthMiddleware::check();
        $controller = new InventoryController();
        $controller->index();
        break;
    case 'stock-movements/form-submit':
        AuthMiddleware::check();
        $controller = new StockMovementController();
        $controller->store();
        break;
    case 'stock-movements/transfer/form-submit':
        AuthMiddleware::check();
        $controller = new StockMovementController();
        $controller->storeTransfer();
        break;
    case 'categories':
        AuthMiddleware::check();
        $controller = new CategoriesController();
        $controller->index();
        break;

    case 'categories/form-submit':
        AuthMiddleware::check();
        $controller = new CategoriesController();
        $controller->createCategory();
        break;

    case 'categories/update/form-submit':
        AuthMiddleware::check();
        $controller = new CategoriesController();
        $controller->updateCategory();
        break;

    case 'categories/delete':
        AuthMiddleware::check();
        $controller = new CategoriesController();
        $controller->deleteCategory();
        break;
    case 'warehouses':
        AuthMiddleware::check();
        $controller = new WarehouseController();
        $controller->index();
        break;

    case 'warehouses/form-submit':
        AuthMiddleware::check();
        $controller = new WarehouseController();
        $controller->createWarehouse();
        break;

    case 'warehouses/update/form-submit':
        AuthMiddleware::check();
        $controller = new WarehouseController();
        $controller->updateWarehouse();
        break;

    case 'warehouses/delete':
        AuthMiddleware::check();
        $controller = new WarehouseController();
        $controller->deleteWarehouse();
        break;

    case 'products':
        AuthMiddleware::check();
        $controller = new ProductController();
        $controller->index();
        break;

    case 'products/form-submit':
        AuthMiddleware::check();
        $controller = new ProductController();
        $controller->createProduct();
        break;
    case 'products/update/form-submit':
        AuthMiddleware::check();
        $controller = new ProductController();
        $controller->updateProduct();
        break;

    case 'products/delete':
        AuthMiddleware::check();
        $controller = new ProductController();
        $controller->deleteProduct();
        break;
    default:
        http_response_code(404);
        require_once __DIR__ . '/../app/views/errors/404.php';
        break;
}