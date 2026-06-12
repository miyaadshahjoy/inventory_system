<?php
class WarehouseController
{
    public function index()
    {
        $warehouses = WarehouseService::getAllWarehouses();
        $data = [
            "warehouses" => $warehouses,
        ];
        require_once __DIR__ . "/../views/warehouses/index.php";
    }

    public function createWarehouse()
    {
        # 1) Validate request method
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            throw new ApplicationException("Invalid request method");
        }
        # 2) Validate input data
        if (
            !isset($_POST["name"]) ||
            empty($_POST["name"]) ||
            !isset($_POST["location"]) ||
            empty($_POST["location"])
        ) {
            throw new ValidationException("All fields are required");
        }

        # 3) Sanitize input data
        $name = trim($_POST["name"]);
        $location = trim($_POST["location"]);

        # 4) Create warehouse
        WarehouseService::createWarehouse([
            "name" => $name,
            "location" => $location,
        ]);
        Session::flashSet("success", "Warehouse created successfully");
        header("Location: /warehouses");
        exit();
    }

    public function updateWarehouse()
    {
        # 1) Validate request method
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            throw new ApplicationException("Invalid request method");
        }

        # 2) Validate input data
        if (!isset($_POST["id"])) {
            throw new ValidationException("Invalid warehouse id");
        }

        if (!isset($_POST["name"]) || !isset($_POST["location"])) {
            throw new ValidationException("All fields are required");
        }

        # 3) Sanitize input data
        $warehouse_id = (int) $_POST["id"];
        $name = trim($_POST["name"]);
        $location = trim($_POST["location"]);

        # 4) Update warehouse in DB
        WarehouseService::updateWarehouse($warehouse_id, [
            "name" => $name,
            "location" => $location,
        ]);

        # 5) Redirect to warehouses page
        Session::flashSet("success", "Warehouse updated successfully");
        header("Location: /warehouses");
        exit();
    }

    /*
    # Delete warehouse with AJAX
    public function deleteWarehouse()
    {
        header("Content-Type: application/json");

        $json_data = file_get_contents("php://input");
        $data = json_decode($json_data, true);

        if (!$data) {
            http_response_code(400);
            echo json_encode([
                "status" => "failed",
                "message" => "Invalid request",
            ]);
            exit();
        }

        if (!isset($data["id"])) {
            http_response_code(400);
            echo json_encode([
                "status" => "failed",
                "message" => "Warehouse id is required",
            ]);
            exit();
        }

        $id = (int) $data["id"];

        # Check if warehouse exists
        $warehouse = $this->getWarehouseById($id);
        if (!$warehouse) {
            http_response_code(404);
            echo json_encode([
                "status" => "failed",
                "message" => "Warehouse not found",
            ]);
            exit();
        }

        $conn = Database::connect();
        $statement = $conn->prepare("
            UPDATE warehouses
            SET warehouse_status = 'INACTIVE'
            WHERE id = ?
            ");
        $statement->bind_param("i", $id);
        if (!$statement->execute()) {
            http_response_code(500);
            echo json_encode([
                "status" => "error",
                "message" => "Database error: error deleting warehouse.",
            ]);
            exit();
        } else {
            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "message" => "Warehouse deleted successfully",
                "data" => [
                    "warehouse_status" => "INACTIVE",
                ],
            ]);
            exit();
        }
    }
    */

    # Delete warehouse without AJAX
    public function deleteWarehouse()
    {
        # Check for warehouse id
        if (!isset($_GET["id"])) {
            throw new ValidationException("Warehouse id is required");
        }
        $id = (int) $_GET["id"];

        # Delete warehouse
        WarehouseService::deleteWarehouse($id);

        # Redirect to warehouses page
        Session::flashSet("success", "Warehouse deleted successfully");
        header("Location: /warehouses");
        exit();
    }
}
