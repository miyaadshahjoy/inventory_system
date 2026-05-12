<?php
require_once __DIR__ . '/../core/Database.php';
class InventoryService
{

    public function addMovement($product_id, $direction, $movement_type, $quantity, $created_by, $notes = null)
    {
        # 1) Validate input parameters

        # 1.A) Check if all required parameters are provided
        if (empty($product_id) || empty($direction) || empty($movement_type) || empty($quantity) || empty($created_by)) {
            throw new InvalidArgumentException('All parameters except notes are required');
        }
        # 1.B) Check if product id is integer
        if (!is_int($product_id)) {
            throw new InvalidArgumentException('Product ID must be an integer');
        }

        # 1.C) Check if direction is either "IN" or "OUT"
        if (!in_array($direction, ['IN', 'OUT'])) {
            throw new InvalidArgumentException('Direction must be either "IN" or "OUT"');
        }

        # 1.D) Check if movement type is valid
        $valid_movement_types = ['STOCK_IN', 'STOCK_OUT', 'TRANSFER_IN', 'TRANSFER_OUT', 'ADJUSTMENT', 'RETURN', 'DAMAGE', 'EXPIRE'];

        if (!in_array($movement_type, $valid_movement_types)) {
            throw new InvalidArgumentException('Invalid movement type');
        }

        # 1.E) Check if quantity is a positive integer
        if (!is_int($quantity) || $quantity <= 0) {
            throw new InvalidArgumentException('Quantity must be a positive integer');
        }

        # 2) Derive movement direction

        $in_movements = ['STOCK_IN', 'TRANSFER_IN', 'RETURN'];
        $out_movements = ['STOCK_OUT', 'TRANSFER_OUT', 'DAMAGE', 'EXPIRE'];

        $movement_direction = in_array($movement_type, $in_movements) ? 'IN' : 'OUT';

        # 3) Start DB transaction
        $conn = Database::connect();

        try {
            $conn->begin_transaction();

            # 4) Get current stock from stock_snapshots table

            # 4.A) Create snapshot if it doesn't exist
            $statement = $conn->prepare("SELECT * FROM stock_snapshots WHERE product_id = ?");
            $statement->bind_param("i", $product_id);
            $statement->execute();
            $result = $statement->get_result();
            if (!$result) {
                $conn->rollback();
                throw new Exception('Failed to retrieve stock snapshot');
            } else {
                $snapshot = $result->fetch_assoc();

                if (!$snapshot) {
                    # Create snapshot 
                    $statement = $conn->prepare("
                    INSERT INTO stock_snapshots (product_id, quantity) VALUES(?, ?)");
                    $statement->bind_param("ii", $product_id, $quantity);
                    $statement->execute();
                    $result = $statement->get_result();
                    if (!$result) {
                        $conn->rollback();
                        throw new Exception('Failed to create stock snapshot');
                    } else {
                        $current_stock = $quantity;
                    }
                } else {
                    $current_stock = $snapshot['quantity'];
                }
            }



            # 5) Validate stock for out movements

            if ($movement_direction === 'OUT' && $current_stock < $quantity) {
                throw new Exception('Not enough stock for this movement');

            }
            # 6) Create movement record in stock_movements table
            $statement = $conn->prepare("INSERT INTO stock_movements(product_id, direction, movement_type, quantity, notes) VALUES(?, ?, ?, ?, ?)");
            $statement->bind_param("issis", $product_id, $movement_direction, $movement_type, $quantity, $notes);
            $statement->execute();
            $result = $statement->get_result();
            if (!$result) {
                $conn->rollback();
                throw new Exception('Failed to create stock movement record');
            }


            # 7) Update stock_snapshots table

            $new_stock = $movement_direction === 'IN' ? $current_stock + $quantity : $current_stock - $quantity;

            $statement = $conn->prepare("UPDATE stock_snapshots 
            SET quantity = ?
            WHERE product_id = ?");

            $statement->bind_param("ii", $new_stock, $product_id);
            $statement->execute();
            $result = $statement->get_result();
            if (!$result) {
                $conn->rollback();
                throw new Exception('Failed to update stock snapshot');
            }

            # 8) Commit transaction

            $conn->commit();
        } catch (Exception $e) {

            # 9) Rollback on any error
            $conn->rollback();
            throw $e;
        }

    }
}