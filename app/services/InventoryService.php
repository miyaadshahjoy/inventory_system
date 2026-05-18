<?php
require_once __DIR__ . '/../core/Database.php';
class InventoryService
{

    public function addMovement(int $product_id, string $movement_type, int $warehouse_id, int $quantity, int $created_by, $notes = null): array
    {
        $conn = Database::connect();


        try {
            # 1) Validate input parameters

            # 1.A) Check if all required parameters are provided
            if (!isset($product_id) || !isset($movement_type) || !isset($warehouse_id) || !isset($quantity) || !isset($created_by)) {
                throw new SystemException('All parameters except notes are required');
            }
            # 1.B) Check if product id is integer
            if (!is_int($product_id)) {
                throw new SystemException('Product ID must be an integer');
            }

            # 1.C) Check if warehouse id is integer
            if (!is_int($warehouse_id)) {
                throw new SystemException('Warehouse ID must be an integer');
            }

            # 1.C) Check if created by is integer
            if (!is_int($created_by)) {
                throw new SystemException('Created by ID must be an integer');
            }

            # 1.D) Check if movement type is valid
            $valid_movement_types = ['STOCK_IN', 'STOCK_OUT', 'ADJUSTMENT_IN', 'ADJUSTMENT_OUT', 'RETURN', 'DAMAGE', 'EXPIRE'];

            if (!in_array($movement_type, $valid_movement_types)) {
                throw new SystemException('Invalid movement type');
            }

            # 1.E) Check if quantity is a positive integer
            if (!is_int($quantity) || $quantity <= 0) {
                throw new ValidationException('Quantity must be a positive integer');
            }

            # 2) Derive movement direction

            $in_movements = ['STOCK_IN', 'RETURN', 'ADJUSTMENT_IN'];
            // $out_movements = ['STOCK_OUT', 'DAMAGE', 'EXPIRE', 'ADJUSTMENT_OUT'];

            $movement_direction = in_array($movement_type, $in_movements) ? 'IN' : 'OUT';


            # 3) Start DB transaction
            $conn->begin_transaction();

            # 4) Check if product exists
            $statement = $conn->prepare("
            SELECT * FROM products 
            WHERE id = ?
            ");
            $statement->bind_param("i", $product_id);
            if (!$statement->execute()) {
                throw new SystemException("Database error: Failed to retrieve product.  $statement->error");
            }
            $result = $statement->get_result();
            if ($result->num_rows === 0) {
                throw new ValidationException("Product does not exist");
            }
            # 5) Check if warehouse exists
            $statement = $conn->prepare("SELECT * FROM warehouses WHERE id = ?");
            $statement->bind_param("i", $warehouse_id);
            if (!$statement->execute()) {
                throw new SystemException("Database error: Failed to retrieve warehouse.  $statement->error");
            }
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
            if (!$statement->execute()) {
                throw new SystemException("Database error: Failed to retrieve stock snapshot.  $statement->error");
            }
            $result = $statement->get_result();
            if ($result->num_rows === 0) {
                # Snapshot doesn't exist, create it

                # Create snapshot 
                $statement = $conn->prepare("
                    INSERT INTO stock_snapshots(product_id, warehouse_id, quantity) VALUES(?, ?, 0)");
                $statement->bind_param("ii", $product_id, $warehouse_id);
                if (!$statement->execute()) {
                    throw new SystemException("Database error: Failed to create stock snapshot.  $statement->error");
                } else {
                    $current_stock = 0;
                }

            } else {
                $snapshot = $result->fetch_assoc();
                $current_stock = $snapshot['quantity'];
            }



            # 7) Validate stock for out movements

            if ($movement_direction === 'OUT' && $current_stock < $quantity) {
                throw new ValidationException('Not enough stock for this movement');

            }
            # 8) Create movement record in stock_movements table
            $statement = $conn->prepare("
            INSERT INTO stock_movements(product_id, warehouse_id, direction, movement_type, quantity, notes, created_by) VALUES(?, ?, ?, ?, ?, ?, ?)");
            $statement->bind_param("iissisi", $product_id, $warehouse_id, $movement_direction, $movement_type, $quantity, $notes, $created_by);

            if (!$statement->execute()) {
                throw new SystemException("Database error: Failed to create stock movement.  $statement->error");
            }


            # 9) Update stock_snapshots table

            $new_stock = $movement_direction === 'IN' ? $current_stock + $quantity : $current_stock - $quantity;

            $statement = $conn->prepare("
            UPDATE stock_snapshots 
            SET quantity = ?
            WHERE product_id = ?
            AND warehouse_id = ?
            ");

            $statement->bind_param("iii", $new_stock, $product_id, $warehouse_id);

            if (!$statement->execute()) {
                throw new SystemException("Database error: Failed to update stock snapshot.  $statement->error");
            }

            # 9) Commit transaction

            $conn->commit();
            return [
                'product_id' => $product_id,
                'new_stock' => $new_stock,
                'movement_type' => $movement_type
            ];
        } catch (Exception $e) {

            # 10) Rollback on any error
            $conn->rollback();
            throw $e;
        }
    }

    public static function getAllMovements()
    {
        // ID  
        // Product
        // Type 
        // Direction 
        // Created by 
        // Created at
        // Notes 
        try {

            $conn = Database::connect();
            $statement = $conn->prepare("
            SELECT sm.id, p.name as product, sm.movement_type as type, sm.direction, u.full_name created_by, sm.created_at, sm.notes 
            FROM stock_movements sm INNER JOIN products p 
            ON sm.product_id = p.id 
            INNER JOIN users u
            ON sm.created_by = u.id
            ");
            if (!$statement->execute()) {
                throw new SystemException("Database error: Failed to retrieve stock movements.  $statement->error");
            }
            $result = $statement->get_result();
            if ($result->num_rows === 0) {
                throw new ValidationException("No stock movements found.");
            }
            $movements = $result->fetch_all(MYSQLI_ASSOC);
            return $movements;

        } catch (Exception $e) {
            throw $e;
        }
    }


    # Get Total SKUs: number of active products
    public static function getTotalSKUs()
    {

        try {
            $conn = Database::connect();
            $statement = $conn->prepare("
                SELECT COUNT(sku) as total_skus FROM PRODUCTS
                WHERE product_status = 'ACTIVE'
            ");

            if (!$statement->execute()) {

                throw new SystemException("Database error: Failed to retrieve total SKUs.  $statement->error");
            }

            $result = $statement->get_result();
            if ($result->num_rows === 0) {
                throw new ValidationException("No SKUs found.");
            }
            $row = $result->fetch_assoc();
            return $row['total_skus'];

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

            if (!$statement->execute()) {
                throw new SystemException("Database error: Failed to retrieve total stock.  $statement->error");
            }

            $result = $statement->get_result();
            if ($result->num_rows === 0) {
                throw new ValidationException("No stock found.");
            }
            $row = $result->fetch_assoc();
            return $row['total_stock'];
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

            if (!$statement->execute()) {
                throw new SystemException("Database error: Failed to retrieve total stock value.  $statement->error");
            }

            $result = $statement->get_result();
            if ($result->num_rows === 0) {
                throw new ValidationException("No stock value found.");
            }
            $row = $result->fetch_assoc();
            return $row['total_stock_value'];

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
                AND p.product_status = 'ACTIVE'
            ");

            if (!$statement->execute()) {
                throw new SystemException("Database error: Failed to retrieve total low stocks.  $statement->error");
            }

            $result = $statement->get_result();
            if ($result->num_rows === 0) {
                throw new ValidationException("No low stocks found.");
            }
            $row = $result->fetch_assoc();
            return $row['total_low_stocks'];

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
                SELECT COUNT(p.id) total_out_stocks FROM 
                products p LEFT JOIN stock_snapshots ss 
                ON p.id = ss.product_id
                WHERE COALESCE(ss.quantity, 0) = 0
                AND p.product_status = 'ACTIVE'
            ");

            if (!$statement->execute()) {
                throw new SystemException("Database error: Failed to retrieve total out of stocks product.  $statement->error");
            }

            $result = $statement->get_result();
            if ($result->num_rows === 0) {
                throw new ValidationException("No out of stocks product found.");
            }
            $row = $result->fetch_assoc();
            return $row['total_out_stocks'];

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

            if (!$statement->execute()) {
                throw new SystemException("Database error: Failed to retrieve total movement today.  $statement->error");
            }

            $result = $statement->get_result();
            if ($result->num_rows === 0) {
                throw new ValidationException("No movement created today.");
            }
            $row = $result->fetch_assoc();
            return $row['total_movement_today'];

        } catch (Exception $e) {
            throw $e;
        }
    }

    public static function getInventoryOverviewData()
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

        $conn = Database::connect();
        $statement = $conn->prepare("
        
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
                ) as last_movement_date

            FROM products p LEFT JOIN stock_snapshots ss 
            ON p.id = ss.product_id
            LEFT JOIN categories c 
            ON p.category_id = c.id 
            LEFT JOIN warehouses w 
            ON ss.warehouse_id = w.id 
        ");

        if (!$statement->execute()) {
            throw new SystemException("Database error: Failed to retrieve inventory overview data.  $statement->error");
        }

        $result = $statement->get_result();
        if ($result->num_rows === 0) {
            throw new ValidationException("No inventory overview data found.");
        }
        $data = $result->fetch_all(MYSQLI_ASSOC);
        return $data;

    }

}