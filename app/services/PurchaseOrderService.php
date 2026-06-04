<?php

class PurchaseOrderService
{
    public static function createPurchaseOrder(int $supplier_id, int $warehouse_id, string $expected_delivery_date, $notes = null, array $items)
    {
        $conn = Database::connect();

        try {

            # 1) Validate input data

            # 1.A) Check if supplier_id is INTEGER
            if (!is_int($supplier_id)) {
                throw new SystemException("Supplier ID must be an integer");
            }

            # 1.B) Check if warehouse_id is INTEGER
            if (!is_int($warehouse_id)) {
                throw new SystemException("Warehouse ID must be an integer");
            }

            # 1.C) Check if expected_delivery_date is a valid date
            $date = DateTime::createFromFormat('Y-m-d', $expected_delivery_date);
            if (!$date || $date->format('Y-m-d') !== $expected_delivery_date) {
                throw new ValidationException("Expected delivery date must be in YYYY-MM-DD format");
            }
            # 1.D) Check if expected_delivery_date is not in the past
            $current_date = new DateTime();
            if ($date < $current_date) {
                throw new ValidationException("Expected delivery date cannot be in the past");
            }

            # 2) Validate supplier 
            # Check if supplier exists and is active
            $supplier = (new SupplierController())->getSupplierById($supplier_id);
            if (!$supplier) {
                throw new ValidationException("Supplier not found");
            }

            # 3) Validate warehouse
            # Check if warehouse exists and is active
            $warehouse = (new WarehouseController())->getWarehouseById($warehouse_id);
            if (!$warehouse) {
                throw new ValidationException("Warehouse not found");
            }

            # 4) Validate expected delivery date
            if (!isset($expected_delivery_date)) {
                throw new SystemException("Expected delivery date is required");
            }

            # 5) Validate items
            # items[0][product_id] | items[0][quantity] | items[0][unit_price]
            foreach ($items as $item) {
                if (!isset($item['product_id']) || !isset($item['quantity']) || !isset($item['unit_price'])) {
                    throw new ValidationException("All fields of purchase order item are required");
                }

                # 5.A) Check if product_id is INTEGER
                if (!is_int($item['product_id'])) {
                    throw new SystemException("Product ID must be an integer");
                }

                # 5.B) Check if quantity is INTEGER
                if (!is_int($item['quantity'])) {
                    throw new SystemException("Quantity must be an integer");
                }
                # 5.C) Check if unit_price is FLOAT
                if (!is_float($item['unit_price']) && !is_int($item['unit_price'])) {
                    throw new SystemException("Unit price must be a number");
                }
                # 5.D) Check if unit_price is greater than 0
                if ($item['unit_price'] <= 0) {
                    throw new ValidationException("Unit price must be greater than 0");
                }

                # Validate quantities
                # 5.E) Check if quantity is greater than 0
                if ($item['quantity'] <= 0) {
                    throw new ValidationException("Quantity must be greater than 0");
                }

                # 5.F) Check if product exists and is active
                $product = (new ProductController())->getProductById($item['product_id']);
                if (!$product) {
                    throw new ValidationException("Product not found");
                }
            }


            # 6) Generate purchase order number
            $purchase_order_number = self::createPurchaseOrderNumber();

            # 7) Get current user
            if (!isset($_SESSION['user'])) {
                throw new ValidationException("You must be logged in to create a purchase order");
            }
            $ordered_by = $_SESSION['user']['id'];

            # 8) Begin Database transaction
            $conn->begin_transaction();


            # 9) Insert purchase order into database
            # po_number | supplier_id | warehouse_id | ordered_by | expected_delivery_date | notes
            $statement = $conn->prepare("
                INSERT INTO purchase_orders(po_number, supplier_id, warehouse_id, ordered_by, expected_delivery_date, notes)
                VALUES(?, ?, ?, ?, ?, ?);

            ");
            $statement->bind_param("siiiss", $purchase_order_number, $supplier_id, $warehouse_id, $ordered_by, $expected_delivery_date, $notes);

            if (!$statement->execute()) {
                throw new SystemException("Database error: Error creating purchase order");
            }
            $purchase_order_id = $conn->insert_id;


            # 10) Insert purchase order items into database
            # purchase_order_id | product_id | order_quantity | received_quantity | unit_price

            foreach ($items as $item) {

                $statement = $conn->prepare("
                    INSERT INTO purchase_order_items(purchase_order_id, product_id, order_quantity, unit_price)
                    VALUES(?, ?, ?, ?)
                ");

                $statement->bind_param("iiid", $purchase_order_id, $item['product_id'], $item['quantity'], $item['unit_price']);

                if (!$statement->execute()) {
                    throw new SystemException("Database error: Error creating purchase order item");
                }

            }
            # 11) Commit transaction
            $conn->commit();
        } catch (Exception $e) {
            $conn->rollback();
            throw $e;
        }

    }

    public static function createPurchaseOrderNumber()
    {
        $date = new DateTime();
        return 'PO-' . $date->format('Ymd-u');

    }

    public static function getAllPurchaseOrders()
    {
        # PO Number | Supplier | Warehouse | Total Items | Total Quantity | Total Cost | Status | Expected Delivery | Created By | Created At

        # id | PO Number | Status | Supplier | Warehouse | Expected delivery | Created by | Created at | Updated at | Notes

        $conn = Database::connect();
        try {
            $statement = $conn->prepare("
                SELECT po.id, 
                    po.po_number,
                    s.supplier_name AS supplier,
                    w.name AS warehouse,
                    (
                        SELECT COUNT( poi.id)
                        FROM purchase_order_items poi
                        WHERE poi.purchase_order_id = po.id
                    ) AS total_items,
                    (
                        SELECT COALESCE(SUM(poi.order_quantity))
                        FROM purchase_order_items poi
                        WHERE poi.purchase_order_id = po.id
                    ) AS total_quantity,
                    (
                        SELECT COALESCE(SUM(poi.order_quantity * poi.unit_price))
                        FROM purchase_order_items poi
                        WHERE poi.purchase_order_id = po.id
                    ) AS total_cost,
                    po.po_status AS status,
                    po.expected_delivery_date as expected_delivery,
                    u.full_name AS created_by,
                    DATE(po.created_at) AS created_at,
                    po.notes AS notes,
                    DATE(po.updated_at) AS updated_at

                    FROM purchase_orders po JOIN suppliers s 
                    ON po.supplier_id = s.id
                    JOIN warehouses w 
                    ON po.warehouse_id = w.id
                    JOIN users u
                    ON po.ordered_by = u.id
                    ORDER BY po.created_at DESC
            ");

            if (!$statement->execute()) {
                throw new SystemException("Database error: Error fetching purchase orders. $statement->error");
            }
            $result = $statement->get_result();
            $purchase_orders = $result->fetch_all(MYSQLI_ASSOC);
            return $purchase_orders;


        } catch (Exception $e) {
            throw $e;
        }


    }

    public static function getPurchaseOrderById(int $id)
    {
        # PO Number | Supplier | Warehouse | Total Items | Total Quantity | Total Cost | Status | Expected Delivery | Created By | Created At

        # id | PO Number | Status | Supplier | Warehouse | Expected delivery | Created by | Created at | Updated at | Notes

        $conn = Database::connect();
        try {
            $statement = $conn->prepare("
                SELECT po.*,
                    po.id, 
                    po.po_number,
                    s.supplier_name AS supplier,
                    w.name AS warehouse,
                    po.po_status AS status,
                    po.expected_delivery_date as expected_delivery,
                    u.full_name AS created_by,
                    DATE(po.created_at) AS created_at,
                    po.notes AS notes,
                    DATE(po.updated_at) AS updated_at

                    FROM purchase_orders po JOIN suppliers s 
                    ON po.supplier_id = s.id
                    JOIN warehouses w 
                    ON po.warehouse_id = w.id
                    JOIN users u
                    ON po.ordered_by = u.id

                    WHERE po.id = ?
                    ORDER BY po.created_at DESC
            ");

            $statement->bind_param("i", $id);
            if (!$statement->execute()) {
                throw new SystemException("Database error: Error fetching purchase orders. $statement->error");
            }
            $result = $statement->get_result();

            return $result->fetch_assoc();


        } catch (Exception $e) {
            throw $e;
        }


    }

    public static function getPurchaseOrderOverview(int $id)
    {
        # Products Ordered | Total Quantity | Received Quantity | Total Cost

        $conn = Database::connect();
        try {
            $statement = $conn->prepare("
                SELECT COUNT(poi.id) AS products_ordered,
                    SUM(poi.order_quantity) AS total_quantity,
                    SUM(poi.received_quantity) AS received_quantity,
                    SUM(poi.order_quantity * poi.unit_price) AS total_cost
                    
                    FROM purchase_orders po JOIN purchase_order_items poi 
                    ON po.id = poi.purchase_order_id
                    WHERE po.id = ?
            ");

            $statement->bind_param("i", $id);
            if (!$statement->execute()) {
                throw new SystemException("Database error: Error fetching purchase orders. $statement->error");
            }
            $result = $statement->get_result();

            return $result->fetch_assoc();


        } catch (Exception $e) {
            throw $e;
        }
    }

    public static function getPurchaseOrderItems(int $purchase_order_id)
    {
        # Product | Ordered | Received | Remaining | Unit Price | Line Total

        $conn = Database::connect();
        try {
            $statement = $conn->prepare("

                SELECT poi.id, 
                    poi.purchase_order_id,
                    poi.product_id, 
                    p.name AS product,
                    poi.order_quantity AS ordered,
                    poi.received_quantity AS received,
                    poi.unit_price,
                    (poi.order_quantity * poi.unit_price) AS line_total
                    
                    FROM products p JOIN purchase_order_items poi
                    ON p.id = poi.product_id
                    WHERE poi.purchase_order_id = ?
            ");

            $statement->bind_param("i", $purchase_order_id);
            if (!$statement->execute()) {
                throw new SystemException("Database error: Error fetching purchase order items. $statement->error");
            }
            $result = $statement->get_result();

            return $result->fetch_all(MYSQLI_ASSOC);


        } catch (Exception $e) {
            throw $e;
        }
    }

    public static function receivePurchaseOrderItems($purchase_order_id, $items)
    {


        $conn = Database::connect();

        try {


            # 1) Validate input data 
            if (!is_int($purchase_order_id)) {
                throw new SystemException("Purchase Order ID must be an integer");
            }

            if (!is_array($items)) {
                throw new SystemException("Items must be an array");
            }

            # 2) Validate Purchase Order
            # 2.A) Fetch purchase order
            $purchase_order = self::getPurchaseOrderById($purchase_order_id);
            # 2.B) Check if purchase order exists
            if (!$purchase_order) {
                throw new ValidationException("Purchase Order does not exist");
            }


            # 3) Validate Purchase order status 
            # Allowed status: APPROVED, PARTIALLY_RECEIVED
            # Non-allowed status: PENDING, CANCELLED, RECEIVED
            if ($purchase_order['status'] !== 'APPROVED' && $purchase_order['status'] !== 'PARTIALLY_RECEIVED') {
                throw new ValidationException("Purchase Order status must be APPROVED or PARTIALLY_RECEIVED");
            }


            # 4) Begin transaction 
            $conn->begin_transaction();

            # Created by 
            if (!isset($_SESSION['user']['id'])) {
                throw new ValidationException("You must be logged in to perform this action");
            }
            $created_by = $_SESSION['user']['id'];

            # 5) Process each items
            foreach ($items as &$item) {
                # product_id | order_quantity | received_quantity | receive_now
                $product_id = $item['product_id'];
                $order_quantity = $item['order_quantity'];
                $received_quantity = $item['received_quantity'];
                $receive_now = $item['receive_now'];

                # 5.A) Check if product_id is INTEGER
                if (!is_int($product_id)) {
                    throw new SystemException("Product ID must be an integer");
                }

                # 5.B) Check if order_quantity is INTEGER
                if (!is_int($order_quantity) || $order_quantity <= 0) {
                    throw new ValidationException("Order Quantity must be an integer and greater than 0");
                }

                # 5.C) Check if received_quantity is INTEGER

                if (!is_int($received_quantity) || $received_quantity < 0) {
                    throw new ValidationException("Received Quantity must be an integer and not a negative number");
                }

                # 5.D) Check if receive_now is INTEGER
                if (!is_int($receive_now) || $receive_now < 0) {
                    throw new ValidationException("Receive Now must be an integer and not a negative number");
                }

                ############################################################################
                ############################################################################

                # 5.E) validate quantity: received_quantity <= order_quantity
                if ($received_quantity > $order_quantity) {
                    throw new ValidationException("Received quantity cannot be greater than order quantity");
                }
                # 5.F) validate quantity: received_quantity + receive_now <= order_quantity
                if ($received_quantity + $receive_now > $order_quantity) {
                    throw new ValidationException("Received quantity cannot be greater than order quantity");
                }

                # 6) Create stock_in movement & update stock in inventory
                # If receive_now > 0, create stock_in movement 
                if ($receive_now > 0) {
                    InventoryService::addMovementWithoutTransaction($product_id, 'STOCK_IN', $purchase_order['warehouse_id'], $receive_now, $created_by, "Received via Purchase Order {$purchase_order['po_number']}");
                }
                # 7) update received_quantity: received_quantity + receive_now
                # update purchase_order_items: received_quantity = received_quantity + receive_now

                $statement = $conn->prepare("
                    UPDATE purchase_order_items
                    SET received_quantity = received_quantity + ?
                    WHERE purchase_order_id = ? AND product_id = ?
                ");
                $statement->bind_param("iii", $receive_now, $purchase_order_id, $product_id);

                if (!$statement->execute()) {
                    throw new SystemException("Database error: Error updating purchase order items. $statement->error");
                }

                $item['new_received_quantity'] = $received_quantity + $receive_now;

            }

            # 8) Recalculate Purchase Order status 
            # If all items received, update status to RECEIVED
            # If any items received, update status to PARTIALLY_RECEIVED

            $flag = 0;
            foreach ($items as $item) {
                if ($item['new_received_quantity'] < $item['order_quantity']) {
                    $flag++;
                }
            }

            $status = $flag === 0 ? 'RECEIVED' : 'PARTIALLY_RECEIVED';


            # 9) update purchase status
            $statement = $conn->prepare("
                UPDATE purchase_orders
                SET po_status = ?
                WHERE id = ?
            ");

            $statement->bind_param("si", $status, $purchase_order_id);

            if (!$statement->execute()) {
                throw new SystemException("Database error: Error updating purchase orders. $statement->error");
            }

            # 10) Commit transaction
            $conn->commit();

        } catch (Exception $e) {

            # 11) Rollback
            $conn->rollback();
            throw $e;
        }

    }

}