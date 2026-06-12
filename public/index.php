<?php
session_start();
date_default_timezone_set("Asia/Dhaka");

require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../app/core/Database.php";
require_once __DIR__ . "/../app/core/Session.php";
require_once __DIR__ . "/../app/core/Logger.php";
require_once __DIR__ . "/../app/core/ErrorHandler.php";
require_once __DIR__ . "/../app/exceptions/ApplicationException.php";
require_once __DIR__ . "/../app/exceptions/SystemException.php";
require_once __DIR__ . "/../app/exceptions/ValidationException.php";
require_once __DIR__ . "/../app/exceptions/AuthorizationException.php";
require_once __DIR__ . "/../app/exceptions/NotFoundException.php";
require_once __DIR__ . "/../app/exceptions/DatabaseException.php";

# Controllers
require_once __DIR__ . "/../app/controllers/AuthController.php";
require_once __DIR__ . "/../app/controllers/DashboardController.php";
require_once __DIR__ . "/../app/controllers/StockMovementController.php";
require_once __DIR__ . "/../app/controllers/InventoryController.php";
require_once __DIR__ . "/../app/controllers/PurchaseOrderController.php";
require_once __DIR__ . "/../app/controllers/CategoryController.php";
require_once __DIR__ . "/../app/controllers/ProductController.php";
require_once __DIR__ . "/../app/controllers/WarehouseController.php";
require_once __DIR__ . "/../app/controllers/SupplierController.php";
require_once __DIR__ . "/../app/controllers/ReturnController.php";
require_once __DIR__ . "/../app/controllers/UserController.php";

# Services
require_once __DIR__ . "/../app/services/AuthService.php";
require_once __DIR__ . "/../app/services/TransferService.php";
require_once __DIR__ . "/../app/services/MovementService.php";
require_once __DIR__ . "/../app/services/InventoryService.php";
require_once __DIR__ . "/../app/services/ProductService.php";
require_once __DIR__ . "/../app/services/CategoryService.php";
require_once __DIR__ . "/../app/services/WarehouseService.php";
require_once __DIR__ . "/../app/services/ReturnService.php";
require_once __DIR__ . "/../app/services/UserService.php";
require_once __DIR__ . "/../app/services/SupplierService.php";
require_once __DIR__ . "/../app/services/PurchaseOrderService.php";

# Middlewares
require_once __DIR__ . "/../app/middlewares/RoleMiddleware.php";
require_once __DIR__ . "/../app/middlewares/AuthMiddleware.php";

# Bootstrap

// ob_start();

# Enabling Mysqli error reporting
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

# Register the error handler
ErrorHandler::register();

# Basic Routing
$url = $_GET["url"] ?? "";
$url = trim($url, "/");
// echo $url;

switch ($url) {
    case "":
    case "login":
        $controller = new AuthController();
        $controller->index();
        break;

    case "logout":
        $controller = new AuthController();
        $controller->logout();
        break;

    case "login/form-submit":
        $controller = new AuthController();
        $controller->login();
        break;

    # Dashboard
    case "dashboard":
        AuthMiddleware::check();
        $controller = new DashboardController();
        $controller->index();
        break;

    # Stock Movements
    case "stock-movements":
        AuthMiddleware::check();
        $controller = new StockMovementController();
        $controller->index();
        break;

    case "stock-movements/form-submit":
        AuthMiddleware::check();
        $controller = new StockMovementController();
        $controller->store();
        break;
    case "stock-movements/transfer/form-submit":
        AuthMiddleware::check();
        $controller = new StockMovementController();
        $controller->storeTransfer();
        break;
    case "stock-movements/adjustment/form-submit":
        AuthMiddleware::check();
        RoleMiddleware::check(["ADMIN"]);
        $controller = new StockMovementController();
        $controller->storeAdjustment();
        break;
    /*
    # Architecture for data CSV export
    - Data Flow: Request -> Controller -> Service -> Query -> Stream -> Download
    - endpoint: stock-movements/export
    - controller method: exportCSV( )
    */
    case "stock-movements/export":
        AuthMiddleware::check();
        $controller = new StockMovementController();
        $controller->exportCSV();
        break;

    # Inventory
    case "inventory-overview":
        AuthMiddleware::check();
        $controller = new InventoryController();
        $controller->index();
        break;
    /*
    # Architecture for data CSV export
    - Data Flow: Request -> Controller -> Service -> Query -> Stream -> Download
    - endpoint: stock-movements/export
    - controller method: exportCSV( )

    */

    case "inventory-overview/export":
        AuthMiddleware::check();
        $controller = new InventoryController();
        $controller->exportCSV();
        break;

    # Categories
    case "categories":
        AuthMiddleware::check();
        $controller = new CategoryController();
        $controller->index();
        break;

    case "categories/form-submit":
        AuthMiddleware::check();
        $controller = new CategoryController();
        $controller->createCategory();
        break;

    case "categories/update/form-submit":
        AuthMiddleware::check();
        $controller = new CategoryController();
        $controller->updateCategory();
        break;

    case "categories/delete":
        AuthMiddleware::check();
        $controller = new CategoryController();
        $controller->deleteCategory();
        break;

    # Warehouses
    case "warehouses":
        AuthMiddleware::check();
        $controller = new WarehouseController();
        $controller->index();
        break;

    case "warehouses/form-submit":
        AuthMiddleware::check();
        $controller = new WarehouseController();
        $controller->createWarehouse();
        break;

    case "warehouses/update/form-submit":
        AuthMiddleware::check();
        $controller = new WarehouseController();
        $controller->updateWarehouse();
        break;

    case "warehouses/delete":
        AuthMiddleware::check();
        $controller = new WarehouseController();
        $controller->deleteWarehouse();
        break;

    # Products
    case "products":
        AuthMiddleware::check();
        $controller = new ProductController();
        $controller->index();
        break;

    case "products/form-submit":
        AuthMiddleware::check();
        $controller = new ProductController();
        $controller->createProduct();
        break;

    case "products/update/form-submit":
        AuthMiddleware::check();
        $controller = new ProductController();
        $controller->updateProduct();
        break;

    case "products/delete":
        AuthMiddleware::check();
        $controller = new ProductController();
        $controller->deleteProduct();
        break;
    /*
    # Architecture for data CSV export
    - Data Flow: Request -> Controller -> Service -> Query -> Stream -> Download
    - endpoint: products/export
    - controller method: exportCSV( )
    */
    case "products/export":
        AuthMiddleware::check();
        $controller = new ProductController();
        $controller->exportCSV();
        break;

    # Returns
    case "returns":
        AuthMiddleware::check();
        $controller = new ReturnController();
        $controller->index();
        break;

    case "returns/form-submit":
        AuthMiddleware::check();
        $controller = new ReturnController();
        $controller->create();
        break;

    # Users
    case "users":
        AuthMiddleware::check();
        RoleMiddleware::check(["ADMIN"]);
        $controller = new UserController();
        $controller->index();
        break;

    case "users/form-submit":
        AuthMiddleware::check();
        RoleMiddleware::check(["ADMIN"]);
        $controller = new UserController();
        $controller->create();
        break;

    case "users/delete":
        AuthMiddleware::check();
        RoleMiddleware::check(["ADMIN"]);
        $controller = new UserController();
        $controller->deleteUser();
        break;

    /////////////////////////////////
    # Purchase order
    case "purchase-orders":
        AuthMiddleware::check();
        $controller = new PurchaseOrderController();
        $controller->index();
        break;

    case "purchase-orders/form-submit":
        AuthMiddleware::check();
        $controller = new PurchaseOrderController();
        $controller->create();
        break;

    case "purchase-orders/details":
        AuthMiddleware::check();
        $controller = new PurchaseOrderController();
        $controller->purchaseOrderDetails();
        break;

    case "purchase-orders/receive-items/form-submit":
        AuthMiddleware::check();
        $controller = new PurchaseOrderController();
        $controller->receiveItems();
        break;

    case "purchase-orders/approve":
        AuthMiddleware::check();
        RoleMiddleware::check(["ADMIN"]);
        $controller = new PurchaseOrderController();
        $controller->approvePurchaseOrder();
        break;

    case "purchase-orders/cancel":
        AuthMiddleware::check();
        RoleMiddleware::check(["ADMIN"]);
        $controller = new PurchaseOrderController();
        $controller->cancelPurchaseOrder();
        break;

    # Suppliers
    case "suppliers":
        AuthMiddleware::check();
        $controller = new SupplierController();
        $controller->index();
        break;

    # Default
    default:
        http_response_code(404);
        require_once __DIR__ . "/../app/views/errors/404.php";
        break;
}
