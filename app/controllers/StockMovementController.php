<?php
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../services/InventoryService.php';
require_once __DIR__ . '/../controllers/ProductController.php';

class StockMovementController
{

    public function index()
    {
        $controller = new ProductController();
        $products = $controller->getAllProducts();
        $movements = InventoryService::getAllMovements();
        $data = [
            'movements' => $movements,
            'products' => $products
        ];

        require_once __DIR__ . '/../views/movements/movementHistory.php';
    }


    public function store()
    {
        try {
            # 1) Verify that the request method is POST
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Invalid request method');
            }


            # 2) Extract form inputs
            $product_id = $_POST['product_id'];
            $movement_type = $_POST['movement_type'];
            $quantity = $_POST['quantity'];
            $notes = $_POST['notes'] ?? '';

            # 3) Validate inputs
            if (!isset($product_id) || !isset($movement_type) || !isset($quantity)) {
                throw new Exception('All fields except notes are required');
            }

            # 4) Sanitize and normalize inputs
            $product_id = (int) $product_id;
            $quantity = (int) $quantity;

            # 5) Get logged in user ID from session (for created_by field)
            $created_by = $_SESSION['user']['id'];

            # 6) Call InventoryService to add movement
            $service = new InventoryService();
            $service->addMovement($product_id, $movement_type, $quantity, $created_by, $notes);
            /*
             try {
            } catch (Exception $e) {
                throw new Exception('Error adding movement');
            }
            */

            header("Location: /stock-movements");

        } catch (Exception $e) {
            die($e->getMessage());
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
}