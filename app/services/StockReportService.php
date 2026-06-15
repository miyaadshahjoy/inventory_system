<?php

class StockReportService
{
    public static function getStockMovementSummary()
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
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    public static function getCurrentStockDetails()
    {
        $conn = Database::connect();
        $statement = $conn->prepare("
                
            SELECT p.name AS product,
                p.sku,
                c.name AS category,
                w.name AS warehouse,
                COALESCE(ss.quantity, 0) as current_stock,
                p.price AS unit_cost,
                (p.price * ss.quantity) AS stock_value,
                p.reorder_level,
                CASE
                    WHEN COALESCE(ss.quantity, 0) = 0 THEN 'OUT'
                    WHEN COALESCE(ss.quantity, 0) < p.reorder_level THEN 'LOW'
                    ELSE 'OK'
                END as status,
                (
                    SELECT MAX(sm.created_at)
                    FROM stock_movements sm
                    WHERE sm.product_id = p.id
                ) as last_movement_date

                FROM products p JOIN stock_snapshots ss
                ON ss.product_id = p.id
                JOIN categories c
                ON p.category_id = c.id
                JOIN warehouses w
                ON ss.warehouse_id = w.id        
                ORDER BY last_movement_date DESC
        ");

        $statement->execute();
        $result = $statement->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
