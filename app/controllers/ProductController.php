<?php

const PRODUCTS_PER_PAGE = 10;

class ProductController
{
    public function index()
    {
        # Get page number
        $page = isset($_GET["page"]) ? (int) $_GET["page"] : 1;
        # Limit of products
        $limit = PRODUCTS_PER_PAGE;

        $filter_data = ProductService::getProductFilters();
        $total_products = ProductService::totalProducts();
        $products = ProductService::getAllFilteredProducts($filter_data);
        $categories = CategoryService::getAllActiveCategories();
        $data = [
            "products" => $products,
            "categories" => $categories,
            "total_products" => $total_products,
            "limit" => $limit,
            "page" => $page,
        ];
        require __DIR__ . "/../views/products/index.php";
    }
    public function createProduct()
    {
        # 1) Check if request method is POST
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            throw new ApplicationException("Invalid request method");
        }

        # 2) Validate input data
        if (
            !isset($_POST["name"]) ||
            !isset($_POST["category_id"]) ||
            !isset($_POST["sku"]) ||
            !isset($_POST["price"]) ||
            !isset($_POST["reorder_level"]) ||
            !isset($_POST["unit"])
        ) {
            throw new ValidationException("All fields are required");
        }

        # 3) Sanitize input data
        $name = trim($_POST["name"]);
        $category_id = (int) $_POST["category_id"];
        $sku = trim($_POST["sku"]);
        $price = (float) $_POST["price"];
        $reorder_level = (int) $_POST["reorder_level"];
        $unit = $_POST["unit"];

        # 4) Create new Product
        $product_id = ProductService::createProduct([
            "name" => $name,
            "category_id" => $category_id,
            "sku" => $sku,
            "price" => $price,
            "reorder_level" => $reorder_level,
            "unit" => $unit,
        ]);
        if (!$product_id) {
            throw new ApplicationException("Failed to create product");
        }

        # 5) Redirect to products page
        Session::flashSet("success", "Product created successfully");
        header("Location: /products");
        exit();
    }

    public function updateProduct()
    {
        # 1) Check if request method is POST
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            throw new ApplicationException("Request method must be POST");
        }

        # 2) Validate input data
        if (!isset($_POST["id"])) {
            throw new ApplicationException("Product id is required");
        }

        if (
            !isset($_POST["name"]) ||
            !isset($_POST["category_id"]) ||
            !isset($_POST["sku"]) ||
            !isset($_POST["price"]) ||
            !isset($_POST["unit"]) ||
            !isset($_POST["reorder_level"])
        ) {
            throw new ValidationException("All fields are required");
        }

        # 3) Sanitize input data
        $id = (int) $_POST["id"];
        $name = trim($_POST["name"]);
        $category_id = (int) $_POST["category_id"];
        $sku = trim($_POST["sku"]);
        $price = (float) $_POST["price"];
        $unit = trim($_POST["unit"]);
        $reorder_level = (int) $_POST["reorder_level"];

        ProductService::updateProduct($id, [
            "name" => $name,
            "category_id" => $category_id,
            "sku" => $sku,
            "price" => $price,
            "unit" => $unit,
            "reorder_level" => $reorder_level,
        ]);

        Session::flashSet("success", "Product updated successfully");
        header("Location: /products");
        exit();
    }

    # Delete product using AJAX
    /*
    public function deleteProduct()
    {
        header("Content-Type: application/json");

        $json_data = file_get_contents("php://input");
        $data = json_decode($json_data, true); # convert json to associative array
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
                "message" => "Product id is required",
            ]);
            exit();
        }

        $id = (int) $data["id"];

        # Check if product exists
        $product = $this->getProductById($id);
        if (!$product) {
            http_response_code(404);
            echo json_encode([
                "status" => "failed",
                "message" => "Product not found",
            ]);
            exit();
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
                "message" => "Database error: Error deleting product.",
            ]);
            exit();
        }
        http_response_code(200);
        echo json_encode([
            "status" => "success",
            "message" => "Product deleted successfully",
            "data" => [
                "product_status" => "INACTIVE",
            ],
        ]);
        exit();
    }
    */

    # Delete product without AJAX
    public function deleteProduct()
    {
        # Check for product id
        if (!isset($_GET["id"])) {
            throw new ValidationException("Product id is required");
        }
        $id = (int) $_GET["id"];

        # Update product in DB
        # Update product status: ACTIVE -> INACTIVE
        ProductService::deleteProduct($id);

        Session::flashSet("success", "Product deleted successfully");
        header("Location: /products");
        exit();
    }

    /*
    # Architecture for data CSV export
    - Data Flow: Request -> Controller -> Service -> Query -> Stream -> Download
    - endpoint: products/export
    - controller method: exportCSV( )
    - service method: exportCSV( )
    */

    public function exportCSV()
    {
        $product_filters = ProductService::getProductFilters();
        $product_filters["limit"] = ProductService::totalProducts();
        ProductService::exportCSV($product_filters);
    }
}
