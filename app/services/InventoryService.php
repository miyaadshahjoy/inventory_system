<?php

class InventoryService
{
    public static function addMovement(
        int $product_id,
        string $movement_type,
        int $warehouse_id,
        int $quantity,
        int $created_by,
        $notes = null,
    ): array {
        $conn = Database::connect();

        try {
            # 1) Validate input parameters

            # 1.A) Check if all required parameters are provided
            if (
                !isset($product_id) ||
                !isset($movement_type) ||
                !isset($warehouse_id) ||
                !isset($quantity) ||
                !isset($created_by)
            ) {
                throw new ApplicationException(
                    "All parameters except notes are required",
                );
            }
            # 1.B) Check if product id is integer
            if (!is_int($product_id)) {
                throw new ApplicationException("Product ID must be an integer");
            }

            # 1.C) Check if warehouse id is integer
            if (!is_int($warehouse_id)) {
                throw new ApplicationException(
                    "Warehouse ID must be an integer",
                );
            }

            # 1.C) Check if created by is integer
            if (!is_int($created_by)) {
                throw new ApplicationException(
                    "Created by ID must be an integer",
                );
            }

            # 1.D) Check if movement type is valid
            $valid_movement_types = [
                "STARTING_STOCK",
                "STOCK_IN",
                "STOCK_OUT",
                "ADJUSTMENT_IN",
                "ADJUSTMENT_OUT",
                "RETURN",
                "DAMAGE",
                "EXPIRE",
                "PURCHASE",
            ];

            if (!in_array($movement_type, $valid_movement_types)) {
                throw new ValidationException("Invalid movement type");
            }

            # 1.E) Check if quantity is a positive integer
            if (!is_int($quantity) || $quantity <= 0) {
                throw new ValidationException(
                    "Quantity must be a positive integer",
                );
            }

            # 2) Derive movement direction

            $in_movements = [
                "STARTING_STOCK",
                "STOCK_IN",
                "RETURN",
                "ADJUSTMENT_IN",
                "PURCHASE",
            ];
            // $out_movements = ['STOCK_OUT', 'DAMAGE', 'EXPIRE', 'ADJUSTMENT_OUT'];

            $movement_direction = in_array($movement_type, $in_movements)
                ? "IN"
                : "OUT";

            # If movement type is STARTING_STOCK
            if ($movement_type === "STARTING_STOCK") {
                # A) Product + warehouse must not already have starting stock
                $statement = $conn->prepare("
                    SELECT COUNT(*) AS count 
                        FROM stock_movements 
                        WHERE movement_type = 'STARTING_STOCK'
                        AND product_id = ?
                        AND warehouse_id = ?
                ");
                $statement->bind_param("ii", $product_id, $warehouse_id);
                $statement->execute();
                $result = $statement->get_result();
                if ($result->fetch_assoc()["count"] > 0) {
                    throw new ValidationException(
                        "Product has already been added to warehouse",
                    );
                }

                # B) Product + warehouse must not already have stock movements

                $statement = $conn->prepare("
                    SELECT COUNT(*) AS count 
                        FROM stock_movements 
                        WHERE product_id = ?
                        AND warehouse_id = ?
                ");
                $statement->bind_param("ii", $product_id, $warehouse_id);
                $statement->execute();
                $result = $statement->get_result();
                if ($result->fetch_assoc()["count"] > 0) {
                    throw new ValidationException(
                        "Product has already been added to warehouse",
                    );
                }

                # C) Product + warehouse must not already have stock snapshots
                $statement = $conn->prepare("
                SELECT COUNT(*) AS count 
                    FROM stock_snapshots 
                    WHERE product_id = ?
                    AND warehouse_id = ?
                ");
                $statement->bind_param("ii", $product_id, $warehouse_id);
                $statement->execute();
                $result = $statement->get_result();
                if ($result->fetch_assoc()["count"] > 0) {
                    throw new ValidationException(
                        "Product has already been added to warehouse",
                    );
                }
            }

            # 3) Start DB transaction
            $conn->begin_transaction();

            # 4) Check if product exists
            $statement = $conn->prepare("
                SELECT * FROM products 
                WHERE id = ?
            ");
            $statement->bind_param("i", $product_id);
            $statement->execute();

            $result = $statement->get_result();
            if ($result->num_rows === 0) {
                throw new ValidationException("Product does not exist");
            }
            # 5) Check if warehouse exists
            $statement = $conn->prepare(
                "SELECT * FROM warehouses WHERE id = ?",
            );
            $statement->bind_param("i", $warehouse_id);
            $statement->execute();

            $result = $statement->get_result();
            if ($result->num_rows === 0) {
                throw new ValidationException("Warehouse does not exist");
            }

            # 6) Get current stock from stock_snapshots table

            # 6.A) Create snapshot if it doesn't exist
            $statement = $conn->prepare("
                SELECT quantity FROM stock_snapshots 
                WHERE product_id = ? 
                AND warehouse_id = ?
                FOR UPDATE
            ");
            $statement->bind_param("ii", $product_id, $warehouse_id);
            $statement->execute();

            $result = $statement->get_result();
            if ($result->num_rows === 0) {
                # Snapshot doesn't exist, create it

                # Create snapshot
                $statement = $conn->prepare("
                    INSERT INTO stock_snapshots(product_id, warehouse_id, quantity) VALUES(?, ?, 0)");
                $statement->bind_param("ii", $product_id, $warehouse_id);
                if (!$statement->execute()) {
                    throw new SystemException(
                        "Database error: Failed to create stock snapshot.  $statement->error",
                    );
                } else {
                    $current_stock = 0;
                }
            } else {
                $snapshot = $result->fetch_assoc();
                $current_stock = $snapshot["quantity"];
            }

            # 7) Validate stock for out movements

            if ($movement_direction === "OUT" && $current_stock < $quantity) {
                throw new ValidationException(
                    "Not enough stock for this movement",
                );
            }

            # 8) Calculate new stock
            $new_stock =
                $movement_direction === "IN"
                    ? $current_stock + $quantity
                    : $current_stock - $quantity;

            # 9) Create movement record in stock_movements table
            $statement = $conn->prepare("
                INSERT INTO stock_movements(product_id, warehouse_id, direction, movement_type, quantity, resulting_stock, notes, created_by) VALUES(?, ?, ?, ?, ?, ?, ?, ?)");
            $statement->bind_param(
                "iissiisi",
                $product_id,
                $warehouse_id,
                $movement_direction,
                $movement_type,
                $quantity,
                $new_stock,
                $notes,
                $created_by,
            );

            $statement->execute();

            # 10) Update stock_snapshots table

            $statement = $conn->prepare("
                UPDATE stock_snapshots 
                SET quantity = ?
                WHERE product_id = ?
                AND warehouse_id = ?
            ");

            $statement->bind_param(
                "iii",
                $new_stock,
                $product_id,
                $warehouse_id,
            );

            $statement->execute();

            # 11) Commit transaction

            $conn->commit();
            return [
                "product_id" => $product_id,
                "new_stock" => $new_stock,
                "movement_type" => $movement_type,
            ];
        } catch (Exception $e) {
            # 12) Rollback on any error
            $conn->rollback();
            throw $e;
        }
    }

    public static function addMovementWithoutTransaction(
        int $product_id,
        string $movement_type,
        int $warehouse_id,
        int $quantity,
        int $created_by,
        $notes = null,
    ): array {
        $conn = Database::connect();

        try {
            # 1) Validate input parameters

            # 1.A) Check if all required parameters are provided
            if (
                !isset($product_id) ||
                !isset($movement_type) ||
                !isset($warehouse_id) ||
                !isset($quantity) ||
                !isset($created_by)
            ) {
                throw new SystemException(
                    "All parameters except notes are required",
                );
            }
            # 1.B) Check if product id is integer
            if (!is_int($product_id)) {
                throw new SystemException("Product ID must be an integer");
            }

            # 1.C) Check if warehouse id is integer
            if (!is_int($warehouse_id)) {
                throw new SystemException("Warehouse ID must be an integer");
            }

            # 1.C) Check if created by is integer
            if (!is_int($created_by)) {
                throw new SystemException("Created by ID must be an integer");
            }

            # 1.D) Check if movement type is valid
            $valid_movement_types = [
                "STOCK_IN",
                "STOCK_OUT",
                "ADJUSTMENT_IN",
                "ADJUSTMENT_OUT",
                "RETURN",
                "DAMAGE",
                "EXPIRE",
                "PURCHASE",
            ];

            if (!in_array($movement_type, $valid_movement_types)) {
                throw new SystemException("Invalid movement type");
            }

            # 1.E) Check if quantity is a positive integer
            if (!is_int($quantity) || $quantity <= 0) {
                throw new ValidationException(
                    "Quantity must be a positive integer",
                );
            }

            # 2) Derive movement direction

            $in_movements = ["STOCK_IN", "RETURN", "ADJUSTMENT_IN", "PURCHASE"];
            // $out_movements = ['STOCK_OUT', 'DAMAGE', 'EXPIRE', 'ADJUSTMENT_OUT'];

            $movement_direction = in_array($movement_type, $in_movements)
                ? "IN"
                : "OUT";

            # 3) Start DB transaction
            // $conn->begin_transaction();

            # 4) Check if product exists
            $statement = $conn->prepare("
            SELECT * FROM products 
            WHERE id = ?
            ");
            $statement->bind_param("i", $product_id);
            $statement->execute();

            $result = $statement->get_result();
            if ($result->num_rows === 0) {
                throw new ValidationException("Product does not exist");
            }
            # 5) Check if warehouse exists
            $statement = $conn->prepare(
                "SELECT * FROM warehouses WHERE id = ?",
            );
            $statement->bind_param("i", $warehouse_id);
            $statement->execute();

            $result = $statement->get_result();
            if ($result->num_rows === 0) {
                throw new ValidationException("Warehouse does not exist");
            }

            # 6) Get current stock from stock_snapshots table

            # 6.A) Create snapshot if it doesn't exist
            $statement = $conn->prepare("
                SELECT quantity FROM stock_snapshots 
                WHERE product_id = ? 
                AND warehouse_id = ?
                FOR UPDATE
            ");
            $statement->bind_param("ii", $product_id, $warehouse_id);
            $statement->execute();

            $result = $statement->get_result();
            if ($result->num_rows === 0) {
                # Snapshot doesn't exist, create it

                # Create snapshot
                $statement = $conn->prepare("
                    INSERT INTO stock_snapshots(product_id, warehouse_id, quantity) VALUES(?, ?, 0)");
                $statement->bind_param("ii", $product_id, $warehouse_id);
                if (!$statement->execute()) {
                    throw new SystemException(
                        "Database error: Failed to create stock snapshot.  $statement->error",
                    );
                } else {
                    $current_stock = 0;
                }
            } else {
                $snapshot = $result->fetch_assoc();
                $current_stock = $snapshot["quantity"];
            }

            # 7) Validate stock for out movements

            if ($movement_direction === "OUT" && $current_stock < $quantity) {
                throw new ValidationException(
                    "Not enough stock for this movement",
                );
            }

            # 8) Calculate new stock
            $new_stock =
                $movement_direction === "IN"
                    ? $current_stock + $quantity
                    : $current_stock - $quantity;

            # 9) Create movement record in stock_movements table
            $statement = $conn->prepare("
                INSERT INTO stock_movements(product_id, warehouse_id, direction, movement_type, quantity, resulting_stock, notes, created_by) VALUES(?, ?, ?, ?, ?, ?, ?, ?)");
            $statement->bind_param(
                "iissiisi",
                $product_id,
                $warehouse_id,
                $movement_direction,
                $movement_type,
                $quantity,
                $new_stock,
                $notes,
                $created_by,
            );

            $statement->execute();

            # 10) Update stock_snapshots table

            $statement = $conn->prepare("
                UPDATE stock_snapshots 
                SET quantity = ?
                WHERE product_id = ?
                AND warehouse_id = ?
            ");

            $statement->bind_param(
                "iii",
                $new_stock,
                $product_id,
                $warehouse_id,
            );

            $statement->execute();

            # 11) Commit transaction

            // $conn->commit();
            return [
                "product_id" => $product_id,
                "new_stock" => $new_stock,
                "movement_type" => $movement_type,
            ];
        } catch (Exception $e) {
            # 12) Rollback on any error
            // $conn->rollback();
            throw $e;
        }
    }

    # Get Total SKUs: number of active products
    public static function getTotalSKUs()
    {
        try {
            $conn = Database::connect();
            $statement = $conn->prepare("
                SELECT COUNT(sku) as total_skus FROM products
                WHERE product_status = 'ACTIVE'
            ");

            $statement->execute();

            $result = $statement->get_result();
            if ($result->num_rows === 0) {
                throw new ValidationException("No SKUs found.");
            }
            $row = $result->fetch_assoc();
            return $row["total_skus"];
        } catch (Exception $e) {
            throw $e;
        }
    }

    # Get total Stock of active products
    public static function getTotalStock()
    {
        try {
            $conn = Database::connect();
            $statement = $conn->prepare("
                SELECT SUM(ss.quantity) as total_stock
                FROM stock_snapshots ss INNER JOIN products p
                ON ss.product_id = p.id 
                WHERE p.product_status = 'ACTIVE';
            ");
            $statement->execute();

            $result = $statement->get_result();
            if ($result->num_rows === 0) {
                throw new ValidationException("No stock found.");
            }
            $row = $result->fetch_assoc();
            return $row["total_stock"];
        } catch (Exception $e) {
            throw $e;
        }
    }

    # Get Total Stock Value
    public static function getTotalStockValue()
    {
        try {
            $conn = Database::connect();
            $statement = $conn->prepare("
                SELECT SUM(p.price * ss.quantity) as total_stock_value
                FROM products p INNER JOIN stock_snapshots ss
                ON p.id = ss.product_id
                WHERE p.product_status = 'ACTIVE'
            ");

            $statement->execute();

            $result = $statement->get_result();
            if ($result->num_rows === 0) {
                throw new ValidationException("No stock value found.");
            }
            $row = $result->fetch_assoc();
            return $row["total_stock_value"];
        } catch (Exception $e) {
            throw $e;
        }
    }

    # Get total low stock products
    public static function getTotalLowStocks()
    {
        try {
            $conn = Database::connect();
            $statement = $conn->prepare("
                SELECT COUNT(p.id) total_low_stocks FROM 
                products p LEFT JOIN stock_snapshots ss 
                ON p.id = ss.product_id
                WHERE COALESCE(ss.quantity, 0) < p.reorder_level
                AND COALESCE(ss.quantity, 0) > 0
                AND p.product_status = 'ACTIVE'
            ");

            $statement->execute();

            $result = $statement->get_result();
            if ($result->num_rows === 0) {
                throw new ValidationException("No low stocks found.");
            }
            $row = $result->fetch_assoc();
            return $row["total_low_stocks"];
        } catch (Exception $e) {
            throw $e;
        }
    }

    # Get total out of stock products
    public static function getTotalOutStocks()
    {
        try {
            $conn = Database::connect();
            $statement = $conn->prepare("
                SELECT COUNT(p.id) as total_out_stocks 
                FROM products p LEFT JOIN stock_snapshots ss 
                ON p.id = ss.product_id
                WHERE COALESCE(ss.quantity, 0) = 0
                AND p.product_status = 'ACTIVE'
            ");

            $statement->execute();

            $result = $statement->get_result();
            if ($result->num_rows === 0) {
                throw new ValidationException(
                    "No out of stocks product found.",
                );
            }
            $row = $result->fetch_assoc();
            return $row["total_out_stocks"];
        } catch (Exception $e) {
            throw $e;
        }
    }

    public static function getTotalMovementToday()
    {
        try {
            $conn = Database::connect();
            $statement = $conn->prepare("
                SELECT COUNT(*) as total_movement_today FROM
                stock_movements
                WHERE DATE(created_at) = CURDATE()
            ");

            $statement->execute();

            $result = $statement->get_result();
            if ($result->num_rows === 0) {
                throw new ValidationException("No movement created today.");
            }
            $row = $result->fetch_assoc();
            return $row["total_movement_today"];
        } catch (Exception $e) {
            throw $e;
        }
    }

    public static function getInventoryOverviewData(array $filter_data)
    {
        /*
        # Data:
        -- Product name
        -- Product SKU
        -- Category
        -- Warehouse
        -- Stock
        -- Status
        -- Reorder level
        -- Last movement date
        */

        # Pagination
        $page = isset($filter_data["page"]) ? (int) $filter_data["page"] : 1;
        $limit = isset($filter_data["limit"])
            ? (int) $filter_data["limit"]
            : RECORDS_PER_PAGE;
        $page = max($page, 1);
        $offset = ($page - 1) * $limit;

        /* 
        # Filter Data:
            - product_search
            - product_category
            - warehouse
            - stock_status
            - sort_by
            - sort_order
        */
        $product_search = $filter_data["product_search"] ?? null;
        $product_category = $filter_data["product_category"] ?? null;
        $warehouse = $filter_data["warehouse"] ?? null;
        $stock_status = $filter_data["stock_status"] ?? null;
        $sort_by = $filter_data["sort_by"] ?? null;
        $sort_order = $filter_data["sort_order"] ?? null;

        # product_name | sku | product_category | warehouse | stock | status | reorder_level | last_movement_date
        $conn = Database::connect();

        $query = "
            SELECT p.id,
                p.name as product_name,
                p.sku, 
                p.category_id,
                c.name as product_category,
                ss.warehouse_id,
                w.name as warehouse, 
                COALESCE(ss.quantity, 0) as stock,
                CASE 
                    WHEN COALESCE(ss.quantity, 0) = 0 THEN 'OUT'
                    WHEN COALESCE(ss.quantity, 0) < p.reorder_level THEN 'LOW'
                    ELSE 'OK'
                END as stock_status, 
                p.reorder_level,
                (
                    SELECT MAX(sm.created_at)
                    FROM stock_movements sm 
                    WHERE sm.product_id = p.id
                ) as last_movement_date
                
                FROM products p LEFT JOIN stock_snapshots ss 
                ON p.id = ss.product_id
                LEFT JOIN categories c 
                ON p.category_id = c.id 
                LEFT JOIN warehouses w 
                ON ss.warehouse_id = w.id 
                WHERE p.product_status = 'ACTIVE'
        ";

        $param_types = "";
        $params = [];

        # Search
        if ($product_search) {
            $query .= " AND (p.name LIKE ? OR p.sku LIKE ?)";
            $param_types .= "ss";
            array_push($params, "%$product_search%", "%$product_search%");
        }

        # Product Category
        if ($product_category) {
            $query .= " AND p.category_id = ?";
            $param_types .= "i";
            array_push($params, $product_category);
        }

        # Warehouse
        if ($warehouse) {
            $query .= " AND ss.warehouse_id = ?";
            $param_types .= "i";
            array_push($params, $warehouse);
        }

        # Stock Status
        if ($stock_status) {
            $query .= " AND (
                CASE 
                    WHEN COALESCE(ss.quantity, 0) = 0 THEN 'OUT'
                    WHEN COALESCE(ss.quantity, 0) < p.reorder_level THEN 'LOW'
                    ELSE 'OK'
                END
            ) = ?";
            $param_types .= "s";
            array_push($params, $stock_status);
        }

        # Executing the query before applying pagination to get the total number of rows
        $statement = $conn->prepare($query);
        if (!empty($params)) {
            $statement->bind_param($param_types, ...$params);
        }

        $statement->execute();
        $result = $statement->get_result();
        $total_rows = $result->num_rows;
        $statement->close();

        # Pagination
        $query .= "    ORDER BY last_movement_date DESC LIMIT ? OFFSET ?";
        $param_types .= "ii";
        array_push($params, $limit, $offset);

        # Sort by
        if ($sort_by) {
            $query = str_replace(
                "ORDER BY last_movement_date DESC",
                "ORDER BY $sort_by ASC",
                $query,
            );
            # Sort order
            if ($sort_order) {
                $query = str_replace("ASC", $sort_order, $query);
            }
        }

        $statement = $conn->prepare($query);
        $statement->bind_param($param_types, ...$params);
        $statement->execute();
        $result = $statement->get_result();

        return [
            "results" => $result->fetch_all(MYSQLI_ASSOC),
            "length" => $total_rows,
        ];
    }

    public static function getInventoryOverviewFilterData(): array
    {
        # Get page number
        $page = isset($_GET["page"]) ? (int) $_GET["page"] : 1;

        # Limit of records
        $limit = RECORDS_PER_PAGE;

        # Validate filter inputs
        # Search records by product name
        $product_search = isset($_GET["product_search"])
            ? trim(htmlspecialchars($_GET["product_search"]))
            : null;

        # Filter data
        # Filter by product category
        $product_category = isset($_GET["category_id"])
            ? (int) htmlspecialchars($_GET["category_id"])
            : null;

        # Filter by warehouse
        $warehouse = isset($_GET["warehouse_id"])
            ? (int) htmlspecialchars($_GET["warehouse_id"])
            : null;

        # Filter by stock status
        $stock_status = isset($_GET["stock_status"])
            ? htmlspecialchars($_GET["stock_status"])
            : null;

        # Sort
        $sort_by = isset($_GET["sort_by"])
            ? htmlspecialchars($_GET["sort_by"])
            : null;
        $sort_order = isset($_GET["sort_order"])
            ? htmlspecialchars($_GET["sort_order"])
            : null;

        return [
            "product_search" => $product_search,
            "product_category" => $product_category,
            "warehouse" => $warehouse,
            "stock_status" => $stock_status,
            "sort_by" => $sort_by,
            "sort_order" => $sort_order,
            "page" => $page,
            "limit" => $limit,
        ];
    }

    public static function getTotalInventoryOverview(): int
    {
        $conn = Database::connect();
        try {
            $statement = $conn->prepare("
                SELECT COUNT(*) as total_inventory_overview
                FROM (
                    SELECT 
                        p.name as product_name,
                        p.sku, 
                        c.name as product_category,
                        w.name as warehouse, 
                        COALESCE(ss.quantity, 0) as stock,
                    CASE 
                        WHEN COALESCE(ss.quantity, 0) = 0 THEN 'OUT'
                        WHEN COALESCE(ss.quantity, 0) < p.reorder_level THEN 'LOW'
                        ELSE 'OK'
                    END as status, p.reorder_level,(
                        SELECT MAX(sm.created_at)
                        FROM stock_movements sm 
                        WHERE sm.product_id = p.id
                        ) as last_movement_date,
                    ROW_NUMBER() OVER (ORDER BY last_movement_date DESC) as rn
                    FROM products p LEFT JOIN stock_snapshots ss 
                    ON p.id = ss.product_id
                    LEFT JOIN categories c 
                    ON p.category_id = c.id 
                    LEFT JOIN warehouses w 
                    ON ss.warehouse_id = w.id 
                    WHERE p.product_status = 'ACTIVE'
                    ORDER BY last_movement_date DESC 
                ) t
            ");

            $statement->execute();

            $result = $statement->get_result();
            $row = $result->fetch_assoc();
            return $row["total_inventory_overview"];
        } catch (Exception $e) {
            throw $e;
        }
    }

    public static function exportCSV(int $page, int $limit)
    {
        try {
            # Clean output buffer
            if (ob_get_length()) {
                ob_end_clean();
            }
            # Get overview data
            $inventory_overviews = self::getInventoryOverviewData([
                "page" => $page,
                "limit" => $limit,
            ]);
            if (!is_array($inventory_overviews)) {
                throw new ValidationException(
                    "Invalid inventory overview data format.",
                );
            }

            # Headers
            header("Content-Type: text/csv; charset=utf-8");
            header(
                'Content-Disposition: attachment; filename="inventory_overview_' .
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

            # product_name | sku | product_category | warehouse | stock | status | reorder_level | last_movement_date
            # Header row
            fputcsv($output, [
                "Product Name",
                "SKU",
                "Category",
                "Warehouse",
                "Stock",
                "Status",
                "Reorder",
                "Last Movement Date",
            ]);

            # Data rows
            if (empty($inventory_overviews)) {
                fputcsv($output, ["No data found"]);
                fclose($output);
                throw new ValidationException(
                    "No inventory overview data found.",
                );
            }

            # product_name | sku | product_category | warehouse | stock | status | reorder_level | last_movement_date
            foreach ($inventory_overviews as $inventory_overview) {
                fputcsv($output, [
                    $inventory_overview["product_name"],
                    $inventory_overview["sku"],
                    $inventory_overview["product_category"],
                    $inventory_overview["warehouse"],
                    $inventory_overview["stock"],
                    $inventory_overview["status"],
                    $inventory_overview["reorder_level"],
                    $inventory_overview["last_movement_date"],
                ]);
            }

            # Close stream
            fclose($output);
            exit();
        } catch (Exception $e) {
            throw $e;
        }
    }
}
