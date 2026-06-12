<?php

class MovementService
{
    public static function getAllMovements(int $page, int $limit)
    {
        # Date
        # Product (name and sku)
        # Warehouse name
        # Movement Type
        # Direction
        # Quantity
        # Resulting stock
        # Created by
        # Notes
        try {
            # Pagination

            $page = max($page, 1);
            $offset = ($page - 1) * $limit;

            # date | prouct_name | product_sku | warehouse_name | movement_type | direction | quantity | resulting_stock | created_by | notes
            # Get stock movements from stock_movements table
            $conn = Database::connect();
            $statement = $conn->prepare("
                
                SELECT DATE(sm.created_at) as date,
                    p.name as product_name,
                    p.sku as product_sku,
                    w.name as warehouse_name,
                    sm.movement_type,
                    sm.direction,
                    sm.quantity,
                    sm.resulting_stock,
                    u.full_name as created_by,
                    sm.notes
                FROM stock_movements sm JOIN products p 
                ON sm.product_id = p.id
                JOIN warehouses w 
                ON sm.warehouse_id = w.id 
                JOIN users u 
                ON sm.created_by = u.id 
                ORDER BY sm.created_at DESC LIMIT ? OFFSET ?
            ");
            $statement->bind_param("ii", $limit, $offset);
            $statement->execute();
            $result = $statement->get_result();
            // if ($result->num_rows === 0) {
            //     throw new ValidationException("No stock movements found.");
            // }
            $movements = $result->fetch_all(MYSQLI_ASSOC);
            return $movements;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public static function totalMovements()
    {
        $conn = Database::connect();

        $statement = $conn->prepare("
            SELECT COUNT(id) as total_movements
            FROM stock_movements
        ");

        $statement->execute();

        $result = $statement->get_result();
        $row = $result->fetch_assoc();
        return $row["total_movements"];
    }

    public static function exportCSV(int $page, int $limit)
    {
        try {
            # Clean output buffer
            if (ob_get_length()) {
                ob_end_clean();
            }
            # Get movements data
            $movements = self::getAllMovements($page, $limit);
            if (!is_array($movements)) {
                throw new ValidationException("Invalid movements data format.");
            }

            # Headers
            header("Content-Type: text/csv; charset=utf-8");
            header(
                'Content-Disposition: attachment; filename="stock_movements_' .
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

            # date | prouct_name | product_sku | warehouse_name | movement_type | direction | quantity | resulting_stock | created_by | notes
            # Header row
            fputcsv($output, [
                "Date",
                "Product Name",
                "Product SKU",
                "Warehouse Name",
                "Movement Type",
                "Direction",
                "Quantity",
                "Resulting Stock",
                "Created By",
                "Notes",
            ]);

            # Data rows
            if (empty($movements)) {
                fputcsv($output, ["No data found"]);
                fclose($output);
                throw new ValidationException("No stock movements found.");
            }

            # date | prouct_name | product_sku | warehouse_name | movement_type | direction | quantity | resulting_stock | created_by | notes
            foreach ($movements as $movement) {
                fputcsv($output, [
                    $movement["date"],
                    $movement["product_name"],
                    $movement["product_sku"],
                    $movement["warehouse_name"],
                    $movement["movement_type"],
                    $movement["direction"],
                    $movement["quantity"],
                    $movement["resulting_stock"],
                    $movement["created_by"],
                    $movement["notes"],
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
