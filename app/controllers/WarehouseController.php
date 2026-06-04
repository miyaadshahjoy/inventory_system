<?php
class WarehouseController
{

    public function index()
    {

        $warehouses = $this->getAllWarehouses();
        $data = [
            'warehouses' => $warehouses
        ];
        require_once __DIR__ . '/../views/warehouses/index.php';
    }

    public function createWarehouse()
    {
        try {

            # 1) Validate request method
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new SystemException('Invalid request method');


            }
            # 2) Validate input data
            if (!isset($_POST['name']) || !isset($_POST['location'])) {
                throw new ValidationException('All fields are required');
            }

            # 3) Sanitize input data
            $name = htmlspecialchars($_POST['name']);
            $location = htmlspecialchars($_POST['location']);

            $name = trim($name);
            $location = trim($location);

            # 4) Create warehouse
            $conn = Database::connect();
            $statement = $conn->prepare("
                INSERT INTO warehouses(name, location)
                VALUES (?, ?)
                ");

            $statement->bind_param('ss', $name, $location);
            try {
                $statement->execute();
            } catch (mysqli_sql_exception $e) {
                if ($e->getCode() == 1062) {
                    throw new ValidationException('Warehouse with the same name already exists.');
                }
                throw new SystemException("Database error: error creating warehouse. $statement->error");
            }
            Session::flashSet('success', 'Warehouse created successfully');
            header('Location: /warehouses');
            exit;


        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getAllWarehouses()
    {

        try {

            $conn = Database::connect();
            $statement = $conn->prepare("
            SELECT *
            FROM warehouses
            ORDER BY created_at DESC
            ");

            if (!$statement->execute()) {
                throw new SystemException("Database error: error fetching warehouses. $statement->error");
            }

            $result = $statement->get_result();

            // if ($result->num_rows === 0) {
            //     throw new ValidationException("No warehouses found.");
            // }

            $warehouses = $result->fetch_all(MYSQLI_ASSOC);
            return $warehouses;


        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getAllActiveWarehouses()
    {

        try {

            $conn = Database::connect();
            $statement = $conn->prepare("
            SELECT *
            FROM warehouses
            WHERE warehouse_status = 'ACTIVE'
            ");

            if (!$statement->execute()) {
                throw new SystemException("Database error: error fetching warehouses. $statement->error");
            }

            $result = $statement->get_result();

            // if ($result->num_rows === 0) {
            //     throw new ValidationException("No warehouses found.");
            // }

            $warehouses = $result->fetch_all(MYSQLI_ASSOC);
            return $warehouses;


        } catch (Exception $e) {
            throw $e;
        }
    }

    public function updateWarehouse()
    {

        try {

            # 1) Validate request method
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new SystemException('Invalid request method');
            }

            # 2) Validate input data
            if (!isset($_POST['id'])) {
                throw new SystemException('Invalid warehouse id');
            }

            if (!isset($_POST['name']) || !isset($_POST['location'])) {
                throw new ValidationException('All fields are required');
            }

            # 3) Sanitize input data
            $warehouse_id = $_POST['id'];
            $name = htmlspecialchars($_POST['name']);
            $location = htmlspecialchars($_POST['location']);

            $warehouse_id = (int) trim($warehouse_id);
            $name = trim($name);
            $location = trim($location);

            # 4) Update warehouse in DB
            $conn = Database::connect();

            # 4.A) Check if warehouse exists
            $warehouse = $this->getWarehouseById($warehouse_id);
            if (!$warehouse) {
                throw new ValidationException('Warehouse not found');
            }

            # 4.B) Update warehouse in DB
            $statement = $conn->prepare("
            UPDATE warehouses
            SET name = ?, location = ?
            WHERE id = ?
            ");
            $statement->bind_param("ssi", $name, $location, $warehouse_id);
            try {
                $statement->execute();
            } catch (mysqli_sql_exception $e) {
                if ($e->getCode() == 1062) {
                    throw new ValidationException('Warehouse with the same name already exists.');
                }
                throw new SystemException("Database error: Error updating warehouse. $statement->error");
            }


            # 5) Redirect to warehouses page
            Session::flashSet('success', 'Warehouse updated successfully');
            header('Location: /warehouses');
            exit;

        } catch (Exception $e) {
            throw $e;
        }
    }

    public function deleteWarehouse()
    {
        header('Content-Type: application/json');

        $json_data = file_get_contents("php://input");
        $data = json_decode($json_data, true);

        if (!$data) {
            http_response_code(400);
            echo json_encode([
                "status" => "failed",
                "message" => "Invalid request",
            ]);
            exit;
        }

        if (!isset($data["id"])) {
            http_response_code(400);
            echo json_encode([
                "status" => "failed",
                "message" => "Warehouse id is required"
            ]);
            exit;
        }

        $id = (int) $data["id"];

        # Check if warehouse exists
        $warehouse = $this->getWarehouseById($id);
        if (!$warehouse) {
            http_response_code(404);
            echo json_encode([
                "status" => "failed",
                "message" => "Warehouse not found"
            ]);
            exit;
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
                "message" => "Database error: error deleting warehouse."
            ]);
            exit;
        } else {
            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "message" => "Warehouse deleted successfully",
                "data" => [
                    "warehouse_status" => "INACTIVE"
                ]
            ]);
            exit;
        }

    }

    public function getWarehouseById(int $id)
    {
        $conn = Database::connect();
        try {
            $statement = $conn->prepare("
            SELECT *
            FROM warehouses
            WHERE id = ?
            AND warehouse_status = 'ACTIVE'
            FOR UPDATE
            ");
            $statement->bind_param("i", $id);
            if (!$statement->execute()) {
                throw new SystemException("Database error: error fetching warehouse. $statement->error");
            }
            $result = $statement->get_result();
            if ($result->num_rows === 0) {
                throw new ValidationException("Warehouse does not exist or is inactive.");
            }
            $warehouse = $result->fetch_assoc();
            return $warehouse;

        } catch (Exception $e) {
            throw $e;
        }
    }
}