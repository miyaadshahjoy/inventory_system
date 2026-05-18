<?php

require_once __DIR__ . "/../core/Database.php";
require_once __DIR__ . "/../services/InventoryService.php";
require_once __DIR__ . "/../services/ProductService.php";

class ProductController
{

    public function index()
    {
        $products = ProductService::getAllProducts();
        $categories = CategoriesController::getAllActiveCategories();
        $data = [
            'products' => $products,
            'categories' => $categories
        ];
        require __DIR__ . '/../views/products/index.php';
    }
    public function createProduct()
    {
        try {

            # 1) Check if request method is POST
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new SystemException('Invalid request method');
            }

            # 2) Validate input data
            if (!isset($_POST['name']) || !isset($_POST['category_id']) || !isset($_POST['sku']) || !isset($_POST['price']) || !isset($_POST['unit'])) {
                throw new ValidationException('All fields are required');
            }

            # 3) Sanitize input data
            $name = htmlspecialchars($_POST['name']);
            $category_id = htmlspecialchars($_POST['category_id']);
            $sku = htmlspecialchars($_POST['sku']);
            $price = htmlspecialchars($_POST['price']);
            $unit = htmlspecialchars($_POST['unit']);

            $name = trim($name);
            $category_id = (int) $category_id;
            $sku = trim($sku);
            $price = (float) $price;

            # 4) Create and store product in database

            $conn = Database::connect();
            $statement = $conn->prepare("
                INSERT INTO products(name, category_id, sku, price, unit) VALUES (?, ?, ?, ?, ?)");

            $statement->bind_param("sisis", $name, $category_id, $sku, $price, $unit);
            try {
                $statement->execute();
            } catch (mysqli_sql_exception $e) {
                if ($e->getCode() == 1062) {
                    throw new ValidationException("Product with same SKU already exists.");
                }

                throw new SystemException("Database error: Error creating product. $statement->error");
            }

            Session::flashSet('success', 'Product created successfully');
            header("Location: /products");
            exit();


        } catch (Exception $e) {
            throw $e;
        }

    }



    public function getAllProducts()
    {
        try {
            $conn = Database::connect();
            $statement = $conn->prepare("
                SELECT * FROM products
                ORDER BY created_at DESC
            ");

            if (!$statement->execute()) {
                throw new SystemException("Database error: Error fetching products. $statement->error");
            }
            $result = $statement->get_result();
            if ($result->num_rows === 0) {
                throw new ValidationException("No products found.");
            }
            $products = $result->fetch_all(MYSQLI_ASSOC);
            return $products;


        } catch (Exception $e) {
            throw $e;
        }

    }

    public function getAllActiveProducts()
    {
        try {
            $conn = Database::connect();
            $statement = $conn->prepare("
                SELECT * FROM products
                WHERE product_status = 'ACTIVE'
                ORDER BY created_at DESC
            ");

            if (!$statement->execute()) {
                throw new SystemException("Database error: Error fetching products. $statement->error");
            }
            $result = $statement->get_result();
            if ($result->num_rows === 0) {
                throw new ValidationException("No products found.");
            }
            $products = $result->fetch_all(MYSQLI_ASSOC);
            return $products;


        } catch (Exception $e) {
            throw $e;
        }

    }


    public function getProductById(int $id)
    {
        try {
            $conn = Database::connect();
            $statement = $conn->prepare("
            SELECT * FROM products 
            WHERE id = ?
            AND product_status = 'ACTIVE'
            FOR UPDATE
            ");
            $statement->bind_param("i", $id);
            if (!$statement->execute()) {
                throw new SystemException("Database error: Error fetching product. $statement->error");
            }

            $result = $statement->get_result();
            if ($result->num_rows === 0) {
                throw new ValidationException("Product not found.");
            }
            $product = $result->fetch_assoc();
            return $product;

        } catch (Exception $e) {
            throw $e;
        }


    }
    public function updateProduct()
    {
        try {
            # 1) Check if request method is POST
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new SystemException("Request method must be POST");
            }

            # 2) Validate input data
            if (!isset($_POST['id'])) {
                throw new SystemException('Product id is required');
            }

            if (!isset($_POST['name']) || !isset($_POST['category_id']) || !isset($_POST['sku']) || !isset($_POST['price']) || !isset($_POST['unit'])) {
                throw new ValidationException('All fields are required');
            }

            # 3) Sanitize input data
            $id = htmlspecialchars($_POST['id']);
            $name = htmlspecialchars($_POST['name']);
            $category_id = htmlspecialchars($_POST['category_id']);
            $sku = htmlspecialchars($_POST['sku']);
            $price = htmlspecialchars($_POST['price']);
            $unit = htmlspecialchars($_POST['unit']);

            $id = (int) $id;
            $name = trim($name);
            $category_id = (int) $category_id;
            $sku = trim($sku);
            $price = (float) $price;

            # 4) Update product in DB
            $conn = Database::connect();

            # 4.A) Check if product exists
            $product = $this->getProductById($id);
            if (!$product) {
                throw new ValidationException('Product not found');
            }


            # 4.B) Update product
            $statement = $conn->prepare("
            UPDATE products 
            SET name = ?,
            category_id = ?,
            sku = ?, 
            price = ?,
            unit = ?
            WHERE id = ?
            ");
            $statement->bind_param("sisisi", $name, $category_id, $sku, $price, $unit, $id);
            try {
                $statement->execute();
            } catch (mysqli_sql_exception $e) {
                if ($e->getCode() == 1062) {
                    throw new ValidationException('Product with same SKU already exists');
                }
                throw new SystemException("Database error: Error updating product. $statement->error()");
            }

            Session::flashSet('success', 'Product updated successfully');
            header("Location: /products");
            exit();


        } catch (Exception $e) {
            throw $e;
        }



    }

    public function deleteProduct()
    {
        header('Content-Type: application/json');

        $json_data = file_get_contents("php://input");
        $data = json_decode($json_data, true); # convert json to associative array

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
                "message" => "Product id is required"
            ]);
            exit;
        }

        $id = (int) $data["id"];

        # Check if product exists
        $product = $this->getProductById($id);
        if (!$product) {
            http_response_code(404);
            echo json_encode([
                "status" => "failed",
                "message" => "Product not found"
            ]);
            exit;
        }

        $conn = Database::connect();
        $statement = $conn->prepare("
            UPDATE products
            SET product_status = 'INACTIVE'
            WHERE id = ?
        ");

        $statement->bind_param("i", $id);
        if (!$statement->execute()) {
            http_response_code(500);
            echo json_encode([
                "status" => "error",
                "message" => "Database error: Error deleting product."
            ]);
            exit;
        } else {
            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "message" => "Product deleted successfully",
                "data" => [
                    "product_status" => 'INACTIVE'
                ]
            ]);
            exit;
        }

    }

}