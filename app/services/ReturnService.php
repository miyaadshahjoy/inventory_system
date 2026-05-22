<?php

class ReturnService
{

    public static function createReturn(int $product_id, int $warehouse_id, int $quantity, $reason = null)
    {
        $conn = Database::connect();
        try {


            # 1) Validate input data 
            if (!isset($product_id) || !isset($warehouse_id) || !isset($quantity)) {
                throw new SystemException("All fields are required except reason");
            }

            # 1.A) Check if product id is integer
            if (!is_int($product_id)) {
                throw new SystemException('Product ID must be an integer');
            }
            # 1.B) Check if warehouse id is integer
            if (!is_int($warehouse_id)) {
                throw new SystemException('Warehouse ID must be an integer');
            }
            # 1.C) Check if quantity is integer
            if (!is_int($quantity)) {
                throw new SystemException('Quantity must be an integer');
            }
            # 1.D) Check if quantity is greater than 0
            if ($quantity <= 0) {
                throw new ValidationException('Quantity must be greater than 0');
            }

            # 2) Get user id from session
            if (!isset($_SESSION['user'])) {
                throw new ValidationException('You are not logged in. You must be logged in to perform this action.');
            }

            $created_by = $_SESSION['user']['id'];

            # 3) Start DB transaction
            $conn->begin_transaction();

            # 4) Insert return in BD
            $statement = $conn->prepare("
                INSERT INTO returns( product_id, warehouse_id, quantity, reason, created_by) VALUES( ?, ?, ?, ?, ?)
            ");
            $statement->bind_param("iiisi", $product_id, $warehouse_id, $quantity, $reason, $created_by);
            if (!$statement->execute()) {
                throw new SystemException("Database error: Error inserting return. $statement->error");
            }


            /*
            # 5) Get current stock from stock_snapshot
            $statement = $conn->prepare("
                SELECT quantity FROM stock_snapshots 
                WHERE product_id = ? 
                AND warehouse_id = ?
                FOR UPDATE
            ");

            $statement->bind_param("ii", $product_id, $warehouse_id);

            if (!$statement->execute()) {
                throw new SystemException("Database error: Error fetching stock snapshot. $statement->error");
            }
            $result = $statement->get_result();
            if ($result->num_rows === 0) {
                throw new SystemException("Database error: Stock snapshot not found");
            }
            $snapshot = $result->fetch_assoc();
            $current_stock = $snapshot['quantity'];

            # 6) Add stock movement
            $updated_stock = $current_stock + $quantity;
            $statement = $conn->prepare("
                INSERT INTO stock_movements( product_id, warehouse_id, direction, movement_type, quantity, resulting_stock, notes, created_by)
                VALUES( ?, ?, 'IN', 'RETURN', ?, ?, ?, ?)
            ");
            $statement->bind_param("iiiisi", $product_id, $warehouse_id, $quantity, $updated_stock, $reason, $created_by);
            if (!$statement->execute()) {
                throw new SystemException("Database error: Error inserting stock movement. $statement->error");
            }


            # 7) Update stock snapshot

            $statement = $conn->prepare("
                UPDATE stock_snapshots
                SET quantity = ?
                WHERE product_id = ?
                AND warehouse_id = ?
            ");
            $statement->bind_param("iii", $updated_stock, $product_id, $warehouse_id);
            if (!$statement->execute()) {
                throw new SystemException("Database error: Error updating stock snapshot. $statement->error");
            }
            */

            # Add movement of type RETURN

            $movement = InventoryService::addMovement($product_id, 'RETURN', $warehouse_id, $quantity, $created_by, $reason);
            if (!$movement) {
                throw new ValidationException("Failed to create RETURN movement.");
            }

            # 8) End DB transaction
            $conn->commit();

            return [
                'product_id' => $product_id,
                'warehouse_id' => $warehouse_id,
                'quantity' => $quantity
            ];
        } catch (Exception $e) {
            $conn->rollback();
            throw $e;
        }
    }

    public static function getAllReturns()
    {

        $conn = Database::connect();
        try {
            $statement = $conn->prepare("
                SELECT 
                    r.id,
                    p.name as product_name,
                    p.sku as product_sku,
                    w.name as warehouse_name,
                    r.quantity,
                    r.reason,
                    u.full_name as created_by,
                    DATE( r.created_at) as created_at
                FROM returns r JOIN products p
                ON r.product_id = p.id
                JOIN warehouses w
                ON r.warehouse_id = w.id
                JOIN users u
                ON r.created_by = u.id
            ");
            if (!$statement->execute()) {
                throw new SystemException("Database error: Error fetching returns. $statement->error");
            }
            $result = $statement->get_result();
            $returns = $result->fetch_all(MYSQLI_ASSOC);
            return $returns;
        } catch (Exception $e) {
            throw $e;
        }
    }
}