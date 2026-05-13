<?php
require_once __DIR__ . '/../core/Database.php';
class InventoryService
{

    public function addMovement($product_id, $movement_type, $quantity, $created_by, $notes = null)
    {
        # 1) Validate input parameters

        # 1.A) Check if all required parameters are provided
        if (!isset($product_id) || !isset($movement_type) || !isset($quantity) || !isset($created_by)) {
            throw new InvalidArgumentException('All parameters except notes are required');
        }
        # 1.B) Check if product id is integer
        if (!is_int($product_id)) {
            throw new InvalidArgumentException('Product ID must be an integer');
        }

        # 1.C) Check if direction is either "IN" or "OUT"
        /*
        if (!in_array($direction, ['IN', 'OUT'])) {
            throw new InvalidArgumentException('Direction must be either "IN" or "OUT"');
        }
        */

        # 1.D) Check if movement type is valid
        $valid_movement_types = ['STOCK_IN', 'STOCK_OUT', 'TRANSFER_IN', 'TRANSFER_OUT', 'ADJUSTMENT_IN', 'ADJUSTMENT_OUT', 'RETURN', 'DAMAGE', 'EXPIRE'];

        if (!in_array($movement_type, $valid_movement_types)) {
            throw new InvalidArgumentException('Invalid movement type');
        }

        # 1.E) Check if quantity is a positive integer
        if (!is_int($quantity) || $quantity <= 0) {
            throw new InvalidArgumentException('Quantity must be a positive integer');
        }

        # 2) Derive movement direction

        $in_movements = ['STOCK_IN', 'TRANSFER_IN', 'RETURN', 'ADJUSTMENT_IN'];
        $out_movements = ['STOCK_OUT', 'TRANSFER_OUT', 'DAMAGE', 'EXPIRE', 'ADJUSTMENT_OUT'];

        $movement_direction = in_array($movement_type, $in_movements) ? 'IN' : 'OUT';

        # 3) Start DB transaction
        $conn = Database::connect();

        try {
            $conn->begin_transaction();

            # 4) Check if product exists
            $statement = $conn->prepare("SELECT * FROM products WHERE id = ?");
            $statement->bind_param("i", $product_id);
            $statement->execute();
            $result = $statement->get_result();
            if ($result->num_rows === 0) {
                throw new Exception("Product does not exist");
            }

            # 5) Get current stock from stock_snapshots table

            # 5.A) Create snapshot if it doesn't exist
            $statement = $conn->prepare("SELECT quantity FROM stock_snapshots WHERE product_id = ? FOR UPDATE");
            $statement->bind_param("i", $product_id);
            $statement->execute();
            $result = $statement->get_result();
            if (!$result) {
                throw new Exception('Failed to retrieve stock snapshot');
            } else {
                $snapshot = $result->fetch_assoc();

                if (!$snapshot) {
                    # Create snapshot 
                    $statement = $conn->prepare("
                    INSERT INTO stock_snapshots (product_id, quantity) VALUES(?, 0)");
                    $statement->bind_param("i", $product_id);
                    ;
                    if (!$statement->execute()) {
                        throw new Exception('Failed to create stock snapshot');
                    } else {
                        $current_stock = 0;
                    }
                } else {
                    $current_stock = $snapshot['quantity'];
                }
            }



            # 6) Validate stock for out movements

            if ($movement_direction === 'OUT' && $current_stock < $quantity) {
                throw new Exception('Not enough stock for this movement');

            }
            # 7) Create movement record in stock_movements table
            $statement = $conn->prepare("INSERT INTO stock_movements(product_id, direction, movement_type, quantity, notes, created_by) VALUES(?, ?, ?, ?, ?, ?)");
            $statement->bind_param("issisi", $product_id, $movement_direction, $movement_type, $quantity, $notes, $created_by);

            if (!$statement->execute()) {
                throw new Exception('Failed to create stock movement record');
            }


            # 8) Update stock_snapshots table

            $new_stock = $movement_direction === 'IN' ? $current_stock + $quantity : $current_stock - $quantity;

            $statement = $conn->prepare("UPDATE stock_snapshots 
            SET quantity = ?
            WHERE product_id = ?");

            $statement->bind_param("ii", $new_stock, $product_id);

            if (!$statement->execute()) {
                throw new Exception('Failed to update stock snapshot');
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
            $statement->execute();
            $result = $statement->get_result();
            if (!$result) {
                throw new Exception('Error fetching stock movements data from the DB.');
            } else {
                $movements = $result->fetch_all(MYSQLI_ASSOC);
                return $movements;
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }


}