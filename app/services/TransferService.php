<?php
require_once __DIR__ . '/../core/Database.php';
class TransferService
{
    # Data: product_id, from_warehouse, to_warehouse, quantity, user_id, notes
    public static function transferStock(array $data): array
    {
        $conn = Database::connect();

        try {
            # 1) Validate input data
            # 1.A) Check if all required fields are present
            if (
                !isset($data['product_id']) ||
                !isset($data['from_warehouse']) ||
                !isset($data['to_warehouse']) ||
                !isset($data['quantity']) ||
                !isset($data['user_id'])
            ) {
                throw new SystemException('All fields are required for transfer');
            }

            if (
                !is_int($data['product_id']) ||
                !is_int($data['from_warehouse']) ||
                !is_int($data['to_warehouse']) ||
                !is_int($data['user_id']) ||
                !is_int($data['quantity'])
            ) {
                throw new SystemException('Invalid product_id, from_warehouse, to_warehouse or user_id');
            }

            $product_id = $data['product_id'];
            $from_warehouse = $data['from_warehouse'];
            $to_warehouse = $data['to_warehouse'];
            $quantity = $data['quantity'];
            $user_id = $data['user_id'];
            $notes = $data['notes'] ?? null;

            # 1.B) Check if user_id, product_id and warehouse IDs are valid (exist in the database)

            # Validate user_id
            $statement = $conn->prepare("
            SELECT id FROM users 
            WHERE id = ?
            ");
            $statement->bind_param("i", $user_id);
            if (!$statement->execute()) {
                throw new SystemException("Database error: Error validating user_id.  $statement->error");
            }
            $result = $statement->get_result();
            if ($result->num_rows === 0) {
                throw new SystemException("Invalid user_id: No such user found");
            }

            # Validate product_id
            $statement = $conn->prepare("
            SELECT id FROM products 
            WHERE id = ?
            ");
            $statement->bind_param("i", $product_id);
            if (!$statement->execute()) {
                throw new SystemException("Database error: Error validating product_id: $statement->error");
            }
            $result = $statement->get_result();
            if ($result->num_rows === 0) {
                throw new SystemException("Invalid product_id: No such product found");
            }

            # Validate warehouse IDs (check if both from_warehouse and to_warehouse exist)
            $statement = $conn->prepare("
            SELECT id FROM warehouses 
            WHERE id IN (?, ?)");
            $statement->bind_param("ii", $from_warehouse, $to_warehouse);
            if (!$statement->execute()) {
                throw new SystemException("Database error: Error validating warehouse IDs. $statement->error");
            }
            $result = $statement->get_result();
            if ($result->num_rows < 2) {
                throw new SystemException("Invalid warehouse IDs: One or both warehouses not found");
            }

            # Validate that the from_warehouse and to_warehouse are different
            if ($from_warehouse === $to_warehouse) {
                throw new ValidationException('From and To warehouses cannot be the same for a transfer');
            }

            # 1.C) Check if quantity is valid
            if ($quantity <= 0) {
                throw new ValidationException('Quantity must be greater than zero for a transfer');
            }

            # Generate a unique ID to link the OUT and IN movements together
            $transfer_group_id = bin2hex(random_bytes(16)); // Generate a random 32-character hexadecimal string

            # 2) Start DB transaction
            $conn->begin_transaction();


            # 3) Get stock snapshot from from_warehouse 

            # 3.A) Check if stock_snapshot exist for the product_id in from_warehouse
            $statement = $conn->prepare("
            SELECT quantity FROM stock_snapshots
            WHERE product_id = ? 
            AND warehouse_id = ?
            FOR UPDATE
            ");

            $statement->bind_param("ii", $product_id, $from_warehouse);

            if (!$statement->execute()) {
                throw new SystemException("Database error: Error checking stock in from_warehouse: $statement->error");
            }

            $result = $statement->get_result();
            if ($result->num_rows === 0) {
                throw new ValidationException("No stock found for product in from_warehouse");
            } else {

                $snapshot = $result->fetch_assoc();
                $current_from_warehouse_stock = $snapshot['quantity'];
                if ($snapshot['quantity'] < $quantity) {
                    throw new ValidationException("Insufficient stock in from_warehouse for transfer. Available: " . $snapshot['quantity']);
                }
            }


            # 4) Create TRANSFER_OUT movement 

            # product_id
            # warehouse_id-> from warehouse
            # reference_warehouse_id-> to warehouse
            # transfer_group_id-> generate it 
            # direction = 'OUT'
            # movement_type = 'TRANSFER_OUT'
            # quantity
            # notes-> optional

            $statement = $conn->prepare("
            INSERT INTO stock_movements( product_id, warehouse_id, reference_warehouse_id, transfer_group_id, direction, movement_type, quantity, notes, created_by) VALUES (
            ?, ?, ?, ?, 'OUT', 'TRANSFER_OUT', ?, ?, ?)
            ");
            $statement->bind_param("iiisisi", $product_id, $from_warehouse, $to_warehouse, $transfer_group_id, $quantity, $notes, $user_id);

            if (!$statement->execute()) {
                throw new SystemException("Database error: Error creating TRANSFER_OUT movement.  $statement->error");
            }

            # 5) Update stock_snapshot in from_warehouse
            $updated_from_warehouse_stock = $current_from_warehouse_stock - $quantity;
            $statement = $conn->prepare("
            UPDATE stock_snapshots
            SET quantity = ?
            WHERE product_id = ?
            AND warehouse_id = ?
            ");
            $statement->bind_param("iii", $updated_from_warehouse_stock, $product_id, $from_warehouse);

            if (!$statement->execute()) {
                throw new SystemException("Database error: Error updating stock_snapshot in from_warehouse. $statement->error");
            }



            # 6) Get stock snapshot from to_warehouse

            # 6.A) Check if stock_snapshot exist for the product_id in to_warehouse
            $statement = $conn->prepare("
            SELECT quantity FROM stock_snapshots
            WHERE product_id = ?
            AND warehouse_id = ?
            FOR UPDATE
            ");

            $statement->bind_param("ii", $product_id, $to_warehouse);

            if (!$statement->execute()) {
                throw new SystemException("Database error: Error checking stock in to_warehouse.  $statement->error");
            }

            $result = $statement->get_result();
            if ($result->num_rows === 0) {
                # 6.B) Create new stock_snapshot in to_warehouse with quantity = 0
                $statement = $conn->prepare("
                INSERT INTO stock_snapshots(product_id, warehouse_id, quantity) VALUES (?, ?, 0)
                ");
                $statement->bind_param("ii", $product_id, $to_warehouse);

                if (!$statement->execute()) {
                    throw new SystemException("Database error: Error creating stock_snapshot in to_warehouse. $statement->error");
                }
                $current_to_warehouse_stock = 0;
            } else {

                $snapshot = $result->fetch_assoc();
                $current_to_warehouse_stock = $snapshot['quantity'];
            }


            # 7) Create TRANSFER_IN movement

            # product_id
            # warehouse_id-> to warehouse
            # reference_warehouse_id-> from warehouse
            # transfer_group_id-> generate it 
            # direction = 'IN'
            # movement_type = 'TRANSFER_IN'
            # quantity
            # notes-> optional

            $statement = $conn->prepare("
            INSERT INTO stock_movements( product_id, warehouse_id, reference_warehouse_id, transfer_group_id, direction, movement_type, quantity, notes, created_by) VALUES (
            ?, ?, ?, ?, 'IN', 'TRANSFER_IN', ?, ?, ?)
            ");
            $statement->bind_param("iiisisi", $product_id, $to_warehouse, $from_warehouse, $transfer_group_id, $quantity, $notes, $user_id);

            if (!$statement->execute()) {
                throw new SystemException("Database error: Error creating TRANSFER_IN movement.  $statement->error");
            }


            # 8) Update stock in the to_warehouse
            $updated_to_warehouse_stock = $current_to_warehouse_stock + $quantity;
            $statement = $conn->prepare("
                UPDATE stock_snapshots
                SET quantity = ?
                WHERE product_id = ? 
                AND warehouse_id = ?
                ");
            $statement->bind_param("iii", $updated_to_warehouse_stock, $product_id, $to_warehouse);

            if (!$statement->execute()) {
                throw new SystemException("Database error: Error updating stock in to_warehouse.  $statement->error");
            }

            # 9) Commit transaction
            $conn->commit();

            return [
                'product_id' => $product_id,
                'from_warehouse_id' => $from_warehouse,
                'to_warehouse_id' => $to_warehouse,
                'current_from_warehouse_stock' => $updated_from_warehouse_stock,
                'current_to_warehouse_stock' => $updated_to_warehouse_stock
            ];

        } catch (Exception $e) {
            $conn->rollback();
            throw $e;
        }
    }
}