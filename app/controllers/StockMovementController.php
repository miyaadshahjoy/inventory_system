<?php
const MOVEMENTS_PER_PAGE = 1;
class StockMovementController
{

    public function index()
    {
        # Get page number

        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;

        $total_movements = $this->totalMovements();
        $limit = MOVEMENTS_PER_PAGE;

        $controller = new ProductController();
        $products = $controller->getAllActiveProducts();
        $movements = InventoryService::getAllMovements($page, $limit);
        $warehouses = WarehouseController::getAllActiveWarehouses();
        $data = [
            'movements' => $movements,
            'products' => $products,
            'warehouses' => $warehouses,
            'total_movements' => $total_movements,
            'limit' => $limit,
            'page' => $page
        ];

        require_once __DIR__ . '/../views/movements/movementHistory.php';
    }


    public function store()
    {
        try {
            # 1) Verify that the request method is POST
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new SystemException('Invalid request method');
            }


            # 2) Extract form inputs
            $product_id = htmlspecialchars($_POST['product_id']);
            $movement_type = htmlspecialchars($_POST['movement_type']);
            $warehouse_id = htmlspecialchars($_POST['warehouse_id']);
            $quantity = htmlspecialchars($_POST['quantity']);
            $notes = htmlspecialchars($_POST['notes']) ?? null;

            # 3) Validate inputs
            if (!isset($product_id) || !isset($movement_type) || !isset($warehouse_id) || !isset($quantity)) {
                throw new ValidationException('All fields except notes are required');
            }

            # 4) Sanitize and normalize inputs
            $product_id = (int) $product_id;
            $warehouse_id = (int) $warehouse_id;
            $quantity = (int) $quantity;

            # 5) Get logged in user ID from session (for created_by field)
            $created_by = $_SESSION['user']['id'];
            if (!$created_by) {
                throw new ValidationException('User not logged in. You must be logged in to create a movement.');
            }

            # 6) Call InventoryService to add movement
            $service = new InventoryService();
            $movement = $service->addMovement($product_id, $movement_type, $warehouse_id, $quantity, $created_by, $notes);
            if (!$movement) {
                throw new ValidationException('Failed to create movement');
            }
            Session::flashSet('success', 'Movement created successfully');
            header("Location: /stock-movements");
            exit;

        } catch (Exception $e) {
            throw $e;
        }

    }

    public function storeTransfer()
    {
        try {
            # 1) Verify that the request method is POST
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new SystemException('Invalid request method');
            }

            # 2) Validate inputs
            if (!isset($_POST['product_id']) || !isset($_POST['from_warehouse']) || !isset($_POST['to_warehouse']) || !isset($_POST['quantity'])) {
                throw new ValidationException('All fields except notes are required');
            }


            # 3) Extract form inputs
            $product_id = htmlspecialchars($_POST['product_id']);
            $from_warehouse_id = htmlspecialchars($_POST['from_warehouse']);
            $to_warehouse_id = htmlspecialchars($_POST['to_warehouse']);
            $quantity = htmlspecialchars($_POST['quantity']);
            $notes = htmlspecialchars($_POST['notes'] ?? null);


            # 4) Sanitize and normalize inputs
            $product_id = (int) $product_id;
            $from_warehouse_id = (int) $from_warehouse_id;
            $to_warehouse_id = (int) $to_warehouse_id;
            $quantity = (int) $quantity;

            # 5) Get logged in user ID from session (for created_by field)
            $created_by = $_SESSION['user']['id'];
            if (!$created_by) {
                throw new ValidationException('User not logged in. You need to be logged in to perform this action.');
            }

            # 6) Call TransferService to add movement
            $service = new TransferService();
            $service_movement = $service->transferStock([
                'product_id' => $product_id,
                'from_warehouse' => $from_warehouse_id,
                'to_warehouse' => $to_warehouse_id,
                'quantity' => $quantity,
                'user_id' => $created_by,
                'notes' => $notes
            ]);

            if (!$service_movement) {
                throw new ValidationException('Failed to create a transfer movement');
            }
            Session::flashSet('success', 'Transfer movement created successfully');
            header("Location: /stock-movements");
            exit;

        } catch (Exception $e) {
            throw $e;
        }

    }


    # GET ALL MOVEMENTS
    /*
    public function getAllMovements()
    {
        try {

            $conn = Database::connect();
            $statement = $conn->prepare("SELECT * FROM stock_movements");
            $statement->execute();
            $result = $statement->get_result();
            if ($result->num_rows === 0) {
                throw new Exception("No movements found.");
            } else {
                $movements = $result->fetch_all(MYSQLI_ASSOC);
                return $movements;
            }


        } catch (Exception $e) {
            die($e->getMessage());
        }
    }
    */

    # Get total number of movements
    public function totalMovements()
    {

        $conn = Database::connect();
        try {

            $statement = $conn->prepare("
                SELECT COUNT(id) as total_movements
                FROM stock_movements
            ");

            if (!$statement->execute()) {
                throw new SystemException("Database error: Failed to retrieve total movements.  $statement->error");
            }

            $result = $statement->get_result();
            $row = $result->fetch_assoc();
            return $row['total_movements'];


        } catch (Exception $e) {
            throw $e;
        }
    }

}
