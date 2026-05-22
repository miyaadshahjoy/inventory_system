<?php

const PRODUCTS_PER_PAGE = 10;

class ProductController
{

    public function index()
    {

        # Get page number
        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        # Limit of products
        $limit = PRODUCTS_PER_PAGE;

        function createUrlWithout(array $keys)
        {
            $_GET['page'] = 1; # 
            $queryParams = $_GET;
            foreach ($keys as $key) {
                unset($queryParams[$key]);
            }
            return http_build_query($queryParams);

        }

        $filter_data = $this->getProductFilters();

        $total_products = $this->totalProducts();
        $products = ProductService::getAllProducts($filter_data);
        $categories = (new CategoriesController())->getAllActiveCategories();
        $data = [
            'products' => $products,
            'categories' => $categories,
            'total_products' => $total_products,
            'limit' => $limit,
            'page' => $page
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
            if (!isset($_POST['name']) || !isset($_POST['category_id']) || !isset($_POST['sku']) || !isset($_POST['price']) || !isset($_POST['reorder_level']) || !isset($_POST['unit'])) {
                throw new ValidationException('All fields are required');
            }

            # 3) Sanitize input data
            $name = htmlspecialchars($_POST['name']);
            $category_id = htmlspecialchars($_POST['category_id']);
            $sku = htmlspecialchars($_POST['sku']);
            $price = htmlspecialchars($_POST['price']);
            $reorder_level = htmlspecialchars($_POST['reorder_level']);
            $unit = htmlspecialchars($_POST['unit']);

            $name = trim($name);
            $category_id = (int) $category_id;
            $sku = trim($sku);
            $price = (float) $price;
            $reorder_level = (int) $reorder_level;

            # 3.A) Check if price is greater than 0
            if ($price <= 0) {
                throw new ValidationException('Price must be greater than 0');
            }

            # 3.B) Check if reorder level is greater than 0
            if ($reorder_level <= 0) {
                throw new ValidationException('Reorder level must be greater than 0');
            }

            # 4) Create and store product in database

            $conn = Database::connect();
            $statement = $conn->prepare("
                INSERT INTO products(name, category_id, sku, price, unit, reorder_level) VALUES (?, ?, ?, ?, ?, ?)");

            $statement->bind_param("sisisi", $name, $category_id, $sku, $price, $unit, $reorder_level);
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
            // if ($result->num_rows === 0) {
            //     throw new ValidationException("No products found.");
            // }
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
            // if ($result->num_rows === 0) {
            //     throw new ValidationException("No products found.");
            // }
            $products = $result->fetch_all(MYSQLI_ASSOC);
            return $products;


        } catch (Exception $e) {
            throw $e;
        }

    }

    # Get total products
    public function totalProducts(): int
    {

        $conn = Database::connect();
        try {
            $statement = $conn->prepare("
                SELECT COUNT(id) as total_products
                FROM products
            ");

            if (!$statement->execute()) {
                throw new SystemException("Database error: Failed to retrieve total products.  $statement->error");
            }

            $result = $statement->get_result();
            $row = $result->fetch_assoc();
            return $row['total_products'];

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

            if (!isset($_POST['name']) || !isset($_POST['category_id']) || !isset($_POST['sku']) || !isset($_POST['price']) || !isset($_POST['unit']) || !isset($_POST['reorder_level'])) {
                throw new ValidationException('All fields are required');
            }

            # 3) Sanitize input data
            $id = htmlspecialchars($_POST['id']);
            $name = htmlspecialchars($_POST['name']);
            $category_id = htmlspecialchars($_POST['category_id']);
            $sku = htmlspecialchars($_POST['sku']);
            $price = htmlspecialchars($_POST['price']);
            $unit = htmlspecialchars($_POST['unit']);
            $reorder_level = htmlspecialchars($_POST['reorder_level']);

            $id = (int) $id;
            $name = trim($name);
            $category_id = (int) $category_id;
            $sku = trim($sku);
            $price = (float) $price;
            $reorder_level = (int) $reorder_level;

            # 3.A) Check if price is greater than 0
            if ($price <= 0) {
                throw new ValidationException('Price must be greater than 0');
            }
            # 3.B) Check if reorder level is greater than 0
            if ($reorder_level <= 0) {
                throw new ValidationException('Reorder level must be greater than 0');
            }

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
            unit = ?,
            reorder_level = ?
            WHERE id = ?
            ");
            $statement->bind_param("sisisii", $name, $category_id, $sku, $price, $unit, $reorder_level, $id);
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
        }
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

    public function getProductFilters(): array
    {
        # Get page number
        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        # Limit of products
        $limit = PRODUCTS_PER_PAGE;

        # Validate filter inputs
        # Search data
        $product_search = isset($_GET['product_search']) ? trim(htmlspecialchars($_GET['product_search'])) : null;

        # Filter data
        $product_category = isset($_GET['product_category']) ? (int) htmlspecialchars($_GET['product_category']) : null;
        $start_date = isset($_GET['start_date']) ? htmlspecialchars($_GET['start_date']) : null;
        $end_date = isset($_GET['end_date']) ? htmlspecialchars($_GET['end_date']) : null;
        $sort_by = isset($_GET['sort_by']) ? htmlspecialchars($_GET['sort_by']) : null; # name, price, created_at
        $min_price = isset($_GET['min_price']) ? (float) htmlspecialchars($_GET['min_price']) : null;
        $max_price = isset($_GET['max_price']) ? (float) htmlspecialchars($_GET['max_price']) : null;
        $product_status = isset($_GET['product_status']) ? htmlspecialchars($_GET['product_status']) : null; # ACTIVE, INACTIVE

        $filter_data = [
            'product_search' => $product_search,
            'product_category' => $product_category,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'sort_by' => $sort_by,
            'min_price' => $min_price,
            'max_price' => $max_price,
            'product_status' => $product_status,
            'page' => $page,
            'limit' => $limit
        ];

        return $filter_data;

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
        $product_filters = $this->getProductFilters();
        $product_filters['limit'] = $this->totalProducts();
        ProductService::exportCSV($product_filters);
    }

}