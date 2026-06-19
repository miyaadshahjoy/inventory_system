<?php

class ProductService
{
    public static function createUrlWithout(array $keys)
    {
        $_GET["page"] = 1; #
        $queryParams = $_GET;
        foreach ($keys as $key) {
            unset($queryParams[$key]);
        }
        # http_build_query: Creates a URL-encoded query string
        return http_build_query($queryParams);
    }
    public static function createProduct(array $data): int
    {
        $name = $data["name"];
        $category_id = $data["category_id"];
        $sku = $data["sku"];
        $price = $data["price"];
        $reorder_level = $data["reorder_level"];
        $unit = $data["unit"];

        # Check if price is greater than 0
        if ($price <= 0) {
            throw new ValidationException("Price must be greater than 0");
        }

        # Check if reorder level is greater than 0
        if ($reorder_level <= 0) {
            throw new ValidationException(
                "Reorder level must be greater than 0",
            );
        }

        # Create and store product in database

        $conn = Database::connect();
        $statement = $conn->prepare("
            INSERT INTO products
            (name, category_id, sku, price, unit, reorder_level) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $statement->bind_param(
            "sisdsi",
            $name,
            $category_id,
            $sku,
            $price,
            $unit,
            $reorder_level,
        );
        try {
            $statement->execute();
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() == 1062) {
                throw new ValidationException(
                    "Product with same SKU already exists.",
                );
            }
        }
        $product_id = $statement->insert_id;
        return $product_id;
    }

    public static function getAllProducts()
    {
        $conn = Database::connect();
        $statement = $conn->prepare("
            SELECT * 
            FROM products
            ORDER BY created_at DESC
        ");

        $statement->execute();
        $result = $statement->get_result();
        $products = $result->fetch_all(MYSQLI_ASSOC);
        return $products;
    }

    public static function getAllActiveProducts()
    {
        $conn = Database::connect();
        $statement = $conn->prepare("
            SELECT * 
            FROM products
            WHERE product_status = 'ACTIVE'
            ORDER BY created_at DESC
        ");

        $statement->execute();
        $result = $statement->get_result();

        $products = $result->fetch_all(MYSQLI_ASSOC);
        return $products;
    }
    public static function getProductById(int $id): array
    {
        $conn = Database::connect();
        $statement = $conn->prepare("
            SELECT * 
            FROM products 
            WHERE id = ?
            AND product_status = 'ACTIVE'
            FOR UPDATE
        ");
        $statement->bind_param("i", $id);
        $statement->execute();

        $result = $statement->get_result();
        if ($result->num_rows === 0) {
            throw new ValidationException("Product not found.");
        }
        $product = $result->fetch_assoc();
        return $product;
    }
    public static function getAllFilteredProducts(array $filter_data)
    {
        # Product name | SKU | Category | Price | Total Stock | Status | Reorder | Updated | Actions

        # Pagination data
        $page = $filter_data["page"] ?? 1;
        $limit = $filter_data["limit"] ?? 10;
        $page = max($page, 1);
        $offset = ($page - 1) * $limit;

        /*
            #filter data
            - product_search
            - product_category
            - start_date
            - end_date
            - sort_by
            - min_price
            - max_price
            - product_status
            */
        $product_search = $filter_data["product_search"] ?? null;
        $product_category = $filter_data["product_category"] ?? null;
        $start_date = $filter_data["start_date"] ?? null;
        $end_date = $filter_data["end_date"] ?? null;
        $sort_by = $filter_data["sort_by"] ?? null; # name, price, created_at
        $min_price = $filter_data["min_price"] ?? null;
        $max_price = $filter_data["max_price"] ?? null;
        $product_status = $filter_data["product_status"] ?? null; # ACTIVE, INACTIVE
        $conn = Database::connect();

        # Query
        $query = "
            SELECT p.id,
                p.name as product_name,
                p.sku,
                p.category_id,
                c.name as category,
                p.price,(
                    SELECT SUM(ss.quantity)
                    FROM stock_snapshots ss 
                    WHERE ss.product_id = p.id
                    ) as total_stock,
                p.unit,
                p.product_status,
                p.reorder_level,
                p.updated_at,
                ROW_NUMBER() OVER (ORDER BY p.updated_at DESC) as rn
            FROM products p JOIN categories c 
            ON p.category_id = c.id 
            WHERE 1=1
        ";
        # Parameter types
        $param_types = "";
        # Parameters
        $params = [];
        # Search
        if ($product_search) {
            $query .= " AND (p.name LIKE ? OR p.sku LIKE ?)";
            $param_types .= "ss";
            array_push(
                $params,
                "%" . $product_search . "%",
                "%" . $product_search . "%",
            );
        }

        # Category
        if ($product_category) {
            $query .= " AND p.category_id = ?";
            $param_types .= "i";
            array_push($params, $product_category);
        }

        # Start date
        if ($start_date) {
            $query .= " AND DATE(p.created_at) >= ?";
            $param_types .= "s";
            array_push($params, $start_date);
        }

        # End date
        if ($end_date) {
            $query .= " AND DATE(p.created_at) <= ?";
            $param_types .= "s";
            array_push($params, $end_date);
        }

        # Min price
        if ($min_price) {
            $query .= " AND p.price >= ?";
            $param_types .= "d";
            array_push($params, $min_price);
        }

        # Max price
        if ($max_price) {
            $query .= " AND p.price <= ?";
            $param_types .= "d";
            array_push($params, $max_price);
        }

        # Status
        if ($product_status) {
            $query .= " AND p.product_status = ?";
            $param_types .= "s";
            array_push($params, $product_status);
        }

        # Executing the query before pagination
        $statement = $conn->prepare($query);
        if (!empty($params)) {
            $statement->bind_param($param_types, ...$params);
        }
        $statement->execute();
        $result = $statement->get_result();
        $total_rows = $result->num_rows;
        $statement->close();

        # Pagination
        $query .= " ORDER BY p.created_at DESC LIMIT ? OFFSET ?";
        $param_types .= "ii";
        array_push($params, $limit, $offset);

        # Sort by
        if ($sort_by) {
            $query = str_replace(
                "ORDER BY p.created_at DESC",
                "ORDER BY p.$sort_by DESC",
                $query,
            );
        }

        $statement = $conn->prepare($query);
        $statement->bind_param($param_types, ...$params);
        $statement->execute();

        $result = $statement->get_result();
        $products = $result->fetch_all(MYSQLI_ASSOC);

        return [
            "products" => $products,
            "total_products" => $total_rows,
        ];
    }

    # Get total products
    public static function totalProducts(): int
    {
        $conn = Database::connect();

        $statement = $conn->prepare("
            SELECT COUNT(id) as total_products
            FROM products
        ");

        $statement->execute();

        $result = $statement->get_result();
        $row = $result->fetch_assoc();
        return $row["total_products"];
    }

    public static function updateProduct(int $id, array $data)
    {
        $name = $data["name"];
        $category_id = $data["category_id"];
        $sku = $data["sku"];
        $price = $data["price"];
        $reorder_level = $data["reorder_level"];
        $unit = $data["unit"];

        # Check if price is greater than 0
        if ($price <= 0) {
            throw new ValidationException("Price must be greater than 0");
        }
        # Check if reorder level is greater than 0
        if ($reorder_level <= 0) {
            throw new ValidationException(
                "Reorder level must be greater than 0",
            );
        }

        # Update product in DB
        $conn = Database::connect();

        # A) Check if product exists
        $product = self::getProductById($id);
        if (!$product) {
            throw new ValidationException("Product not found");
        }

        # B) Update product
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
        $statement->bind_param(
            "sisdsii",
            $name,
            $category_id,
            $sku,
            $price,
            $unit,
            $reorder_level,
            $id,
        );
        try {
            $statement->execute();
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() == 1062) {
                throw new ValidationException(
                    "Product with same SKU already exists",
                );
            }
        }
    }
    public static function deleteProduct(int $id)
    {
        # Check if product exists
        $product = self::getProductById($id);
        if (!$product) {
            throw new ValidationException("Product not found");
        }

        $conn = Database::connect();
        $statement = $conn->prepare("
            UPDATE products
            SET product_status = 'INACTIVE'
            WHERE id = ?
        ");
        $statement->bind_param("i", $id);
        $statement->execute();
    }

    public static function exportCSV(array $product_filters)
    {
        try {
            # Clean output buffer
            if (ob_get_length()) {
                ob_end_clean();
            }
            # Get products
            $products = self::getAllProducts($product_filters);
            if (!is_array($products)) {
                throw new ValidationException("Invalid product data format.");
            }

            # Headers
            header("Content-Type: text/csv; charset=utf-8");
            header(
                'Content-Disposition: attachment; filename="products_' .
                    date("Y-m-d_H-i") .
                    '.csv"',
            );

            # Open output stream
            $output = fopen("php://output", "w");
            if ($output === false) {
                throw new SystemException("Failed to open output stream.");
            }

            # Excel UTF-8 BOM
            fprintf($output, chr(0xef) . chr(0xbb) . chr(0xbf));

            # Product name | SKU | Category | Price | Total Stock | Status | Reorder | Updated

            # Header row
            fputcsv($output, [
                "Product Name",
                "SKU",
                "Category",
                "Price",
                "Total Stock",
                "Status",
                "Reorder",
                "Updated",
            ]);

            # Data rows
            if (empty($products)) {
                fputcsv($output, ["No data found"]);
                # Close stream
                fclose($output);
                throw new ValidationException("No products found.");
            }

            foreach ($products as $product) {
                fputcsv($output, [
                    $product["product_name"],
                    $product["sku"],
                    $product["category"],
                    $product["price"],
                    $product["total_stock"],
                    $product["product_status"],
                    $product["reorder_level"],
                    $product["updated_at"],
                ]);
            }

            # Close stream
            fclose($output);
            exit();
        } catch (Exception $e) {
            throw $e;
        }
    }

    public static function getProductFilters(): array
    {
        # Get page number
        $page = isset($_GET["page"]) ? (int) $_GET["page"] : 1;
        # Limit of products
        $limit = PRODUCTS_PER_PAGE;

        # Validate filter inputs
        # Search data
        $product_search = isset($_GET["product_search"])
            ? trim(htmlspecialchars($_GET["product_search"]))
            : null;

        # Filter data
        $product_category = isset($_GET["product_category"])
            ? (int) htmlspecialchars($_GET["product_category"])
            : null;
        $start_date = isset($_GET["start_date"])
            ? htmlspecialchars($_GET["start_date"])
            : null;
        $end_date = isset($_GET["end_date"])
            ? htmlspecialchars($_GET["end_date"])
            : null;
        $sort_by = isset($_GET["sort_by"])
            ? htmlspecialchars($_GET["sort_by"])
            : null; # name, price, created_at
        $min_price = isset($_GET["min_price"])
            ? (float) htmlspecialchars($_GET["min_price"])
            : null;
        $max_price = isset($_GET["max_price"])
            ? (float) htmlspecialchars($_GET["max_price"])
            : null;
        $product_status = isset($_GET["product_status"])
            ? htmlspecialchars($_GET["product_status"])
            : null; # ACTIVE, INACTIVE
        $filter_data = [
            "product_search" => $product_search,
            "product_category" => $product_category,
            "start_date" => $start_date,
            "end_date" => $end_date,
            "sort_by" => $sort_by,
            "min_price" => $min_price,
            "max_price" => $max_price,
            "product_status" => $product_status,
            "page" => $page,
            "limit" => $limit,
        ];

        return $filter_data;
    }
}
