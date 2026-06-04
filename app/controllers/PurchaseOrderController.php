<?php

class PurchaseOrderController
{

    public function index()
    {
        $data = [
            'warehouses' => (new WarehouseController())->getAllActiveWarehouses(),
            'products' => (new ProductController())->getAllActiveProducts(),
            'suppliers' => (new SupplierController())->getAllActiveSuppliers(),
            'purchase_orders' => PurchaseOrderService::getAllPurchaseOrders()
        ];

        require_once __DIR__ . '/../views/purchase_orders/index.php';
    }

    public function create()
    {
        try {

            # Check if the request method is POST
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new SystemException("Request method must be POST");
            }

            # Validate the input data
            # supplier_id | warehouse_id | expected_delivery_date | notes | 
            # items[0][product_id] | items[0][quantity] | items[0][unit_price]

            if (!isset($_POST['supplier_id']) || !isset($_POST['warehouse_id']) || !isset($_POST['expected_delivery_date'])) {
                throw new ValidationException("All fields of purchase order except notes are required");
            }

            if (!isset($_POST['items'])) {
                throw new ValidationException("At-least one item must be added to create a purchase order");
            }

            $supplier_id = htmlspecialchars($_POST['supplier_id']);
            $warehouse_id = htmlspecialchars($_POST['warehouse_id']);
            $expected_delivery_date = htmlspecialchars($_POST['expected_delivery_date']);
            $notes = htmlspecialchars($_POST['notes']) ?? null;
            $items = $_POST['items'];
            if (empty($items)) {
                throw new ValidationException("At least one item must be added to create a purchase order");
            }

            # Sanitize the input data
            $supplier_id = (int) $supplier_id;
            $warehouse_id = (int) $warehouse_id;

            foreach ($items as &$item) {
                $item['product_id'] = (int) htmlspecialchars($item['product_id']);
                $item['quantity'] = (int) htmlspecialchars($item['quantity']);
                $item['unit_price'] = (float) htmlspecialchars($item['unit_price']);
            }


            # Create the purchase order
            PurchaseOrderService::createPurchaseOrder($supplier_id, $warehouse_id, $expected_delivery_date, $notes, $items);

            Session::flashSet('success', 'Purchase order created successfully');
            header('Location: /purchase-orders');
            exit;

        } catch (Exception $e) {
            throw $e;
        }
    }

    public function purchaseOrderDetails()
    {
        # id | PO Number | Status | Supplier | Warehouse | Expected delivery | Created by | Created at | Updated at | Notes
        if (!isset($_GET['id'])) {
            throw new SystemException("Purchase order ID is missing");
        }
        $id = (int) $_GET['id'];
        $purchase_order = PurchaseOrderService::getPurchaseOrderById($id);

        # Products Ordered | Total Quantity | Received Quantity | Total Cost
        $purchase_order_overview = PurchaseOrderService::getPurchaseOrderOverview($id);

        # Product | Ordered | Received | Remaining | Unit Price | Line Total
        $purchase_order_items = PurchaseOrderService::getPurchaseOrderItems($id);

        $data = [
            'purchase_order' => $purchase_order,
            'products_ordered' => $purchase_order_overview['products_ordered'] ?? 0,
            'total_quantity' => $purchase_order_overview['total_quantity'] ?? 0,
            'received_quantity' => $purchase_order_overview['received_quantity'] ?? 0,
            'total_cost' => $purchase_order_overview['total_cost'] ?? 0,
            'purchase_order_items' => $purchase_order_items
        ];

        require_once __DIR__ . '/../views/purchase_orders/purchase_order_details.php';
    }

    public function receiveItems()
    {
        # product_id | order_quantity | received_quantity | receive_now

        try {
            # Check if the request method is POST 
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new SystemException("Request method must be POST");
            }

            # Validate the input data
            if (!isset($_POST['purchase_order_id']) || !isset($_POST['items'])) {
                throw new ValidationException("All fields are required");
            }

            if (empty($_POST['items'])) {
                throw new ValidationException("At least one item must be received");
            }
            $purchase_order_id = htmlspecialchars($_POST['purchase_order_id']);
            $items = $_POST['items'];


            # Sanitize the input data
            $purchase_order_id = (int) $purchase_order_id;

            $hasReceiveQuantity = false;
            foreach ($items as &$item) {
                $item['product_id'] = (int) $item['product_id'];
                $item['order_quantity'] = (int) $item['order_quantity'];
                $item['received_quantity'] = (int) $item['received_quantity'];
                $item['receive_now'] = (int) $item['receive_now'];

                if ($item['receive_now'] !== 0) {
                    $hasReceiveQuantity = true;
                }
            }

            if (!$hasReceiveQuantity) {
                throw new ValidationException("At least one item must be received");
            }

            # Receive the items
            PurchaseOrderService::receivePurchaseOrderItems($purchase_order_id, $items);

            Session::flashSet('success', 'Items received successfully');
            header("Location: $_SERVER[HTTP_REFERER]");
            exit;

        } catch (Exception $e) {
            throw $e;
        }

    }

    public function approvePurchaseOrder()
    {

        $conn = Database::connect();
        try {
            if (!isset($_GET['id'])) {
                throw new SystemException("Purchase order ID is missing");
            }

            $id = (int) $_GET['id'];
            # Check if purchase order exists 
            $stmt = $conn->prepare("
                SELECT id 
                FROM purchase_orders 
                WHERE id = ?
            ");
            $stmt->bind_param("i", $id);
            if (!$stmt->execute()) {
                throw new SystemException("Database error: Error fetching purchase orders. $stmt->error");
            }
            $result = $stmt->get_result();
            if ($result->num_rows === 0) {
                throw new ValidationException("Purchase order does not exist");
            }

            # Check if the purchase order is already approved
            $stmt = $conn->prepare("
                SELECT po_status 
                FROM purchase_orders 
                WHERE id = ? AND po_status = 'APPROVED'
            ");
            $stmt->bind_param("i", $id);
            if (!$stmt->execute()) {
                throw new SystemException("Database error: Error fetching purchase orders. $stmt->error");
            }
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                throw new ValidationException("Purchase order is already approved");
            }

            $stmt = $conn->prepare("
                UPDATE purchase_orders 
                SET po_status = 'APPROVED' 
                WHERE id = ?
            ");

            $stmt->bind_param("i", $id);
            if (!$stmt->execute()) {
                throw new SystemException("Database error: Error approving purchase order. $stmt->error");
            }

            Session::flashSet('success', 'Purchase order approved successfully');
            header("Location: $_SERVER[HTTP_REFERER]");
            exit;

        } catch (Exception $e) {
            throw $e;
        }
    }

    public function cancelPurchaseOrder()
    {
        $conn = Database::connect();

        try {
            if (!isset($_GET['id'])) {
                throw new SystemException("Purchase order ID is missing");
            }

            $id = (int) $_GET['id'];

            # Check if purchase order exists 
            $stmt = $conn->prepare("
                SELECT id 
                FROM purchase_orders 
                WHERE id = ?
            ");
            $stmt->bind_param("i", $id);
            if (!$stmt->execute()) {
                throw new SystemException("Database error: Error fetching purchase orders. $stmt->error");
            }
            $result = $stmt->get_result();
            if ($result->num_rows === 0) {
                throw new ValidationException("Purchase order does not exist");
            }

            # Check if purchase ourder is already cancelled or it is received or partially received
            $stmt = $conn->prepare("
                SELECT po_status 
                FROM purchase_orders
                WHERE id = ? AND (po_status = 'CANCELLED' OR po_status = 'RECEIVED' OR po_status = 'PARTIALLY_RECEIVED')
            ");
            $stmt->bind_param("i", $id);
            if (!$stmt->execute()) {
                throw new SystemException("Database error: Error fetching purchase orders. $stmt->error");
            }
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                throw new ValidationException("Purchase order is already cancelled or it is received or partially received");
            }

            $stmt = $conn->prepare("
                UPDATE purchase_orders 
                SET po_status = 'CANCELLED' 
                WHERE id = ?
            ");

            $stmt->bind_param("i", $id);
            if (!$stmt->execute()) {
                throw new SystemException("Database error: Error cancelling purchase order. $stmt->error");
            }

            Session::flashSet('success', 'Purchase order cancelled successfully');
            header("Location: /purchase_orders");
            exit;

        } catch (Exception $e) {
            throw $e;
        }
    }

}