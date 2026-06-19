<?php

class StockReportService
{
    public static function getCurrentStockDetails(array $filter_data): array
    {
        # Pagination data
        $page = $filter_data["page"] ?? 1;
        $limit = $filter_data["limit"] ?? RECORDS_PER_PAGE;
        $page = max($page, 1);
        $offset = ($page - 1) * $limit;

        /* 
        # Filter Data:
            - product_search
            - product_category
            - warehouse
            - stock_status
        */

        $product_search = $filter_data["product_search"] ?? null;
        $product_category = $filter_data["product_category"] ?? null;
        $warehouse = $filter_data["warehouse"] ?? null;
        // $stock_status = $filter_data["stock_status"] ?? null;
        $sort_by = $filter_data["sort_by"] ?? null;
        $sort_order = $filter_data["sort_order"] ?? null;

        $conn = Database::connect();
        # Query
        $query = "
            SELECT
                p.id,
                p.name AS product,
                p.sku,
                p.category_id,
                c.name AS category,
                ss.warehouse_id,
                w.name AS warehouse,

                -- Opening stock
                COALESCE(SUM(
                    CASE WHEN sm.movement_type = 'STARTING_STOCK'
                    THEN sm.quantity ELSE 0 END
                ),0) AS opening_stock,

                -- Received
                COALESCE(SUM(
                    CASE WHEN sm.movement_type IN ('STOCK_IN','PURCHASE')
                    THEN sm.quantity ELSE 0 END
                ),0) AS received,

                -- Sold
                COALESCE(SUM(
                    CASE WHEN sm.movement_type='STOCK_OUT'
                    THEN sm.quantity ELSE 0 END
                ),0) AS sold,

                -- Transfer In
                COALESCE(SUM(
                    CASE WHEN sm.movement_type='TRANSFER_IN'
                    THEN sm.quantity ELSE 0 END
                ),0) AS transfered_in,

                -- Transfer Out
                COALESCE(SUM(
                    CASE WHEN sm.movement_type='TRANSFER_OUT'
                    THEN sm.quantity ELSE 0 END
                ),0) AS transfered_out,

                -- Adjusted In
                COALESCE(SUM(
                    CASE WHEN sm.movement_type='ADJUSTMENT_IN'
                    THEN sm.quantity ELSE 0 END
                ),0) AS adjusted_in,

                -- Adjusted Out
                COALESCE(SUM(
                    CASE WHEN sm.movement_type='ADJUSTMENT_OUT'
                    THEN sm.quantity ELSE 0 END
                ),0) AS adjusted_out,

                -- Returned
                COALESCE(SUM(
                    CASE WHEN sm.movement_type='RETURN'
                    THEN sm.quantity ELSE 0 END
                ),0) AS returned,

                -- Damaged
                COALESCE(SUM(
                    CASE WHEN sm.movement_type='DAMAGE'
                    THEN sm.quantity ELSE 0 END
                ),0) AS damaged,

                -- Expired
                COALESCE(SUM(
                    CASE WHEN sm.movement_type='EXPIRE'
                    THEN sm.quantity ELSE 0 END
                ),0) AS expired,

                -- Current Stock
                COALESCE(ss.quantity,0) AS current_stock,

                -- Unit Cost 
                p.price AS unit_cost,

                -- Stock value 
                (p.price * COALESCE(ss.quantity,0)) AS stock_value


            FROM products p

            JOIN stock_snapshots ss
            ON ss.product_id = p.id

            JOIN categories c
            ON c.id = p.category_id

            JOIN warehouses w
            ON w.id = ss.warehouse_id

            LEFT JOIN stock_movements sm
            ON sm.product_id = ss.product_id
            AND sm.warehouse_id = ss.warehouse_id

            WHERE 1 = 1

        ";
        # Parameter types
        $param_types = "";

        # Parameters
        $params = [];

        # Search
        if ($product_search) {
            $query .= " AND (p.name LIKE ? OR p.sku LIKE ?)";
            $param_types .= "ss";
            array_push($params, "%$product_search%", "%$product_search%");
        }

        # Product category
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

        /*
        # Stock status
        if ($stock_status) {
            $query .= " AND status = ?";
            $param_types .= "s";
            array_push($params, $stock_status);
        }
        */
        # Add Group By clause
        $query .= " 

            GROUP BY
                p.id,
                p.name,
                p.sku,
                p.category_id,
                c.name,
                ss.warehouse_id,
                w.name
        ";

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
        $query .= "    ORDER BY product ASC LIMIT ? OFFSET ?";

        $param_types .= "ii";
        array_push($params, $limit, $offset);

        # Sort by
        if ($sort_by) {
            $query = str_replace(
                "ORDER BY product",
                "ORDER BY $sort_by",
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

    public static function getTotalStockDetails(): int
    {
        $conn = Database::connect();
        $statement = $conn->prepare("
            SELECT * FROM
                (
                    SELECT p.name AS product,
                        p.sku,
                        p.category_id,
                        c.name AS category,
                        ss.warehouse_id,
                        w.name AS warehouse,
                        COALESCE(ss.quantity, 0) as current_stock,
                        p.price AS unit_cost,
                        (p.price * ss.quantity) AS stock_value,
                        p.reorder_level,
                        CASE
                            WHEN COALESCE(ss.quantity, 0) = 0 THEN 'OUT'
                            WHEN COALESCE(ss.quantity, 0) < p.reorder_level THEN 'LOW'
                            ELSE 'OK'
                        END AS status,
                        (
                            SELECT MAX(sm.created_at)
                            FROM stock_movements sm
                            WHERE sm.product_id = p.id
                        ) AS last_movement_date

                        FROM products p JOIN stock_snapshots ss
                        ON ss.product_id = p.id
                        JOIN categories c
                        ON p.category_id = c.id
                        JOIN warehouses w
                        ON ss.warehouse_id = w.id    

                ) sd
                WHERE 1 = 1
        ");
        $statement->execute();
        $result = $statement->get_result();
        return $result->num_rows;
    }

    public static function getStockDetailsFilter(): array
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

        /*
        # Filter by stock status
        $stock_status = isset($_GET["stock_status"])
            ? htmlspecialchars($_GET["stock_status"])
            : null;
        */
        # Sort
        $sort_by = isset($_GET["sort_by"])
            ? htmlspecialchars($_GET["sort_by"])
            : null;
        $sort_order = isset($_GET["sort_order"])
            ? htmlspecialchars($_GET["sort_order"])
            : null;

        $filter_data = [
            "product_search" => $product_search,
            "product_category" => $product_category,
            "warehouse" => $warehouse,
            // "stock_status" => $stock_status,
            "sort_by" => $sort_by,
            "sort_order" => $sort_order,
            "page" => $page,
            "limit" => $limit,
        ];

        return $filter_data;
    }
    public static function getStockMovementSummary(array $filter_data): array
    {
        # Pagination data
        $page = $filter_data["page"] ?? 1;
        $limit = $filter_data["limit"] ?? 10;
        $page = max($page, 1);
        $offset = ($page - 1) * $limit;

        /*
        # Filters 
        - product_search
        - start_date
        - end_date
        - warehouse
        - movement_type
        - page
        - limit
        */

        $product_search = $filter_data["product_search"] ?? null;
        $start_date = $filter_data["start_date"] ?? null;
        $end_date = $filter_data["end_date"] ?? null;
        $warehouse = $filter_data["warehouse"] ?? null;
        $movement_type = $filter_data["movement_type"] ?? null;

        $conn = Database::connect();

        # Query
        $query = "
            SELECT sm.created_at AS date,
                p.name AS product,
                p.sku AS sku,
                w.name AS warehouse,
                sm.movement_type,
                sm.quantity,
                u.full_name AS created_by,
                sm.notes AS reference

                FROM stock_movements as sm
                JOIN products p
                ON sm.product_id = p.id
                JOIN warehouses w
                ON sm.warehouse_id = w.id
                JOIN users u 
                ON sm.created_by = u.id
                
                WHERE 1 = 1

        ";

        $param_types = "";
        $params = [];

        if ($product_search) {
            $query .= " AND p.name LIKE ? OR p.sku LIKE ?";
            $param_types .= "ss";
            array_push($params, "%$product_search%", "%$product_search%");
        }

        if ($start_date) {
            $query .= " AND DATE(sm.created_at) >= ?";
            $param_types .= "s";
            array_push($params, $start_date);
        }
        if ($end_date) {
            $query .= " AND DATE(sm.created_at) <= ?";
            $param_types .= "s";
            array_push($params, $end_date);
        }

        if ($warehouse) {
            $query .= " AND sm.warehouse_id = ?";
            $param_types .= "i";
            array_push($params, $warehouse);
        }

        if ($movement_type) {
            $query .= " AND sm.movement_type = ?";
            $param_types .= "s";
            array_push($params, $movement_type);
        }

        # Executing the query before applying pagination to get the total number of rows
        $statement = $conn->prepare($query);
        if (!empty($params)) {
            $statement->bind_param($param_types, ...$params);
        }
        $statement->execute();
        $result = $statement->get_result();
        $total_rows = $result->num_rows;

        $query .= " ORDER BY sm.created_at DESC LIMIT ? OFFSET ?";

        $param_types .= "ii";
        array_push($params, $limit, $offset);

        $statement = $conn->prepare($query);
        $statement->bind_param($param_types, ...$params);

        $statement->execute();
        $result = $statement->get_result();
        return [
            "results" => $result->fetch_all(MYSQLI_ASSOC),
            "length" => $total_rows,
        ];
    }

    public static function getTotalMovementsSummary(): int
    {
        $conn = Database::connect();
        $statement = $conn->prepare("    
            SELECT sm.created_at AS date,
                p.name AS product,
                p.sku AS sku,
                w.name AS warehouse,
                sm.movement_type,
                sm.quantity,
                u.full_name AS created_by,
                sm.notes AS reference

                FROM stock_movements as sm
                JOIN products p
                ON sm.product_id = p.id
                JOIN warehouses w
                ON sm.warehouse_id = w.id
                JOIN users u 
                ON sm.created_by = u.id
                ORDER BY sm.created_at DESC
        ");

        $statement->execute();
        $result = $statement->get_result();
        return $result->num_rows;
    }

    public static function getMovementsSummaryFilter(): array
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
        # Filter by date range
        $start_date = isset($_GET["start_date"])
            ? htmlspecialchars($_GET["start_date"])
            : null;

        $end_date = isset($_GET["end_date"])
            ? htmlspecialchars($_GET["end_date"])
            : null;

        # Filter by warehouse
        $warehouse = isset($_GET["warehouse_id"])
            ? (int) htmlspecialchars($_GET["warehouse_id"])
            : null;

        # Filter by movement type
        $movement_type = isset($_GET["movement_type"])
            ? htmlspecialchars($_GET["movement_type"])
            : null;

        $filter_data = [
            "product_search" => $product_search,
            "start_date" => $start_date,
            "end_date" => $end_date,
            "warehouse" => $warehouse,
            "movement_type" => $movement_type,
            "page" => $page,
            "limit" => $limit,
        ];

        return $filter_data;
    }
}
