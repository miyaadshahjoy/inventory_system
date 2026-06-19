<?php
const MOVEMENTS_PER_PAGE = 10;
class StockMovementController
{
    public function index()
    {
        # Get page number
        $page = isset($_GET["page"]) ? (int) $_GET["page"] : 1;

        $total_movements = MovementService::totalMovements();
        $limit = MOVEMENTS_PER_PAGE;

        $products = ProductService::getAllActiveProducts();
        $movements = MovementService::getAllMovements($page, $limit);
        $warehouses = WarehouseService::getAllActiveWarehouses();
        $data = [
            "movements" => $movements,
            "products" => $products,
            "warehouses" => $warehouses,
            "total_movements" => $total_movements,
            "limit" => $limit,
            "page" => $page,
        ];

        require_once __DIR__ . "/../views/movements/movementHistory.php";
    }

    public function store()
    {
        # 1) Verify that the request method is POST
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            throw new ApplicationException("Invalid request method");
        }

        Logger::info(json_encode($_POST));

        # 2) Validate inputs
        if (
            !isset($_POST["product_id"]) ||
            !isset($_POST["movement_type"]) ||
            !isset($_POST["warehouse_id"]) ||
            !isset($_POST["quantity"])
        ) {
            throw new ValidationException(
                "All fields except notes are required",
            );
        }

        # 3) Extract and sanitize form inputs
        $product_id = (int) $_POST["product_id"];
        $movement_type = $_POST["movement_type"];
        $warehouse_id = (int) $_POST["warehouse_id"];
        $quantity = (int) $_POST["quantity"];
        $notes = $_POST["notes"] ?? null;

        # 4) Validate quantity
        if ($quantity <= 0) {
            throw new ValidationException("Quantity must be greater than 0");
        }

        # 5) Check if product exists
        $product = ProductService::getProductById($product_id);
        if (!$product) {
            throw new ValidationException("Product not found");
        }

        # 6) Check if warehouse exists
        $warehouse = WarehouseService::getWarehouseById($warehouse_id);
        if (!$warehouse) {
            throw new ValidationException("Warehouse not found");
        }

        # 6) Get logged in user ID from session (for created_by field)
        $created_by = $_SESSION["user"]["id"];
        if (!$created_by) {
            throw new ValidationException(
                "User not logged in. You must be logged in to create a movement.",
            );
        }

        # 7) Call InventoryService to add movement

        $movement = InventoryService::addMovement(
            $product_id,
            $movement_type,
            $warehouse_id,
            $quantity,
            $created_by,
            $notes,
        );
        if (!$movement) {
            throw new ValidationException("Failed to create movement");
        }
        Session::flashSet("success", "Movement created successfully");
        header("Location: /stock-movements");
        exit();
    }

    public function storeTransfer()
    {
        # 1) Verify that the request method is POST
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            throw new ApplicationException("Invalid request method");
        }

        # 2) Validate inputs
        if (
            !isset($_POST["product_id"]) ||
            !isset($_POST["from_warehouse"]) ||
            !isset($_POST["to_warehouse"]) ||
            !isset($_POST["quantity"])
        ) {
            throw new ValidationException(
                "All fields except notes are required",
            );
        }

        # 3) Extract form inputs and sanitize
        $product_id = (int) $_POST["product_id"];
        $from_warehouse_id = (int) $_POST["from_warehouse"];
        $to_warehouse_id = (int) $_POST["to_warehouse"];
        $quantity = (int) $_POST["quantity"];
        $notes = $_POST["notes"] ?? null;

        # 4) Validate quantity
        if ($quantity <= 0) {
            throw new ValidationException("Quantity must be greater than 0");
        }

        # 5) Check if product exists
        $product = ProductService::getProductById($product_id);
        if (!$product) {
            throw new ValidationException("Product not found");
        }

        # 6) Check if from warehouse exists
        $from_warehouse = WarehouseService::getWarehouseById(
            $from_warehouse_id,
        );
        if (!$from_warehouse) {
            throw new ValidationException("From warehouse not found");
        }

        # 7) Check if to warehouse exists
        $to_warehouse = WarehouseService::getWarehouseById($to_warehouse_id);
        if (!$to_warehouse) {
            throw new ValidationException("To warehouse not found");
        }

        # 8) Get logged in user ID from session (for created_by field)
        $created_by = $_SESSION["user"]["id"];
        if (!$created_by) {
            throw new ValidationException(
                "User not logged in. You need to be logged in to perform this action.",
            );
        }

        # 9) Call TransferService to add movement

        $service_movement = TransferService::transferStock([
            "product_id" => $product_id,
            "from_warehouse" => $from_warehouse_id,
            "to_warehouse" => $to_warehouse_id,
            "quantity" => $quantity,
            "user_id" => $created_by,
            "notes" => $notes,
        ]);

        if (!$service_movement) {
            throw new ValidationException(
                "Failed to create a transfer movement",
            );
        }
        # 10) Redirect to movement history
        Session::flashSet("success", "Transfer movement created successfully");
        header("Location: /stock-movements");
        exit();
    }

    public function storeAdjustment()
    {
        # 1) Verify that the request method is POST
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            throw new ApplicationException("Invalid request method");
        }

        # 2) Validate inputs
        if (
            !isset($_POST["product_id"]) ||
            !isset($_POST["movement_type"]) ||
            !isset($_POST["warehouse_id"]) ||
            !isset($_POST["quantity"]) ||
            !isset($_POST["notes"])
        ) {
            throw new ValidationException("All fields are required");
        }

        # 3) Extract form inputs and sanitize
        $product_id = (int) $_POST["product_id"];
        $movement_type = trim($_POST["movement_type"]);
        $warehouse_id = (int) $_POST["warehouse_id"];
        $quantity = (int) $_POST["quantity"];
        $notes = $_POST["notes"];

        # 4) Validate quantity
        if ($quantity <= 0) {
            throw new ValidationException("Quantity must be greater than 0");
        }

        # 5) Check if product exists
        $product = ProductService::getProductById($product_id);
        if (!$product) {
            throw new ValidationException("Product not found");
        }

        # 6) Check if warehouse exists
        $warehouse = WarehouseService::getWarehouseById($warehouse_id);
        if (!$warehouse) {
            throw new ValidationException("Warehouse not found");
        }

        # 7) Get logged in user ID from session (for created_by field)
        $created_by = $_SESSION["user"]["id"];
        if (!$created_by) {
            throw new ValidationException(
                "User not logged in. You need to be logged in to perform this action.",
            );
        }

        # 8) Add adjustment movement
        $movement = InventoryService::addMovement(
            $product_id,
            $movement_type,
            $warehouse_id,
            $quantity,
            $created_by,
            $notes,
        );

        if (!$movement) {
            throw new ValidationException(
                "Failed to create adjustment movement",
            );
        }

        # 9) Redirect to movement history
        Session::flashSet(
            "success",
            "Adjustment movement created successfully",
        );
        header("Location: /stock-movements");
        exit();
    }

    public function exportCSV()
    {
        // $page , $limit
        $page = 1;
        $limit = MovementService::totalMovements();
        MovementService::exportCSV($page, $limit);
    }
}
