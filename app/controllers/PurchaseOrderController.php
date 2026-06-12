<?php

class PurchaseOrderController
{
    public function index()
    {
        $data = [
            "products" => ProductService::getAllActiveProducts(),
            "suppliers" => SupplierService::getAllActiveSuppliers(),
            "purchase_orders" => PurchaseOrderService::getAllPurchaseOrders(),
        ];

        require_once __DIR__ . "/../views/purchase_orders/index.php";
    }

    public function create()
    {
        // echo "<pre>";
        // print_r($_POST);
        // echo "</pre>";
        # Check if the request method is POST
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            throw new ApplicationException("Request method must be POST");
        }

        # Validate the input data
        # supplier_id | expected_delivery_date | notes |
        # items[0][product_id] | items[0][quantity] | items[0][unit_price]

        if (
            !isset($_POST["supplier_id"]) ||
            !isset($_POST["expected_delivery_date"])
        ) {
            throw new ValidationException(
                "All fields of purchase order except notes are required",
            );
        }
        $items = $_POST["items"];
        $items = array_filter(
            $_POST["items"],
            fn($item) => $item["quantity"] !== null && $item["quantity"] > 0,
        );
        if (!isset($items) || empty($items)) {
            throw new ValidationException(
                "At least one item must be added to create a purchase order",
            );
        }

        $supplier_id = (int) $_POST["supplier_id"];
        $expected_delivery_date = $_POST["expected_delivery_date"];
        $notes = $_POST["notes"] ?? null;

        foreach ($items as &$item) {
            $item["product_id"] = (int) $item["product_id"];
            $item["quantity"] = (int) $item["quantity"];
            $item["unit_price"] = (float) $item["unit_price"];
        }

        # Create the purchase order
        PurchaseOrderService::createPurchaseOrder(
            $supplier_id,
            $expected_delivery_date,
            $notes,
            $items,
        );

        Session::flashSet("success", "Purchase order created successfully");
        header("Location: /purchase-orders");
        exit();
    }

    public function purchaseOrderDetails()
    {
        # id | PO Number | Status | Supplier | Warehouse | Expected delivery | Created by | Created at | Updated at | Notes
        if (!isset($_GET["id"])) {
            throw new SystemException("Purchase order ID is missing");
        }
        $id = (int) $_GET["id"];
        $purchase_order = PurchaseOrderService::getPurchaseOrderById($id);

        # Products Ordered | Total Quantity | Received Quantity | Total Cost
        $purchase_order_overview = PurchaseOrderService::getPurchaseOrderOverview(
            $id,
        );

        # Product | Ordered | Received | Remaining | Unit Price | Line Total
        $purchase_order_items = PurchaseOrderService::getPurchaseOrderItems(
            $id,
        );

        $data = [
            "warehouses" => WarehouseService::getAllActiveWarehouses(),
            "purchase_order" => $purchase_order,
            "products_ordered" =>
                $purchase_order_overview["products_ordered"] ?? 0,
            "total_quantity" => $purchase_order_overview["total_quantity"] ?? 0,
            "received_quantity" =>
                $purchase_order_overview["received_quantity"] ?? 0,
            "total_cost" => $purchase_order_overview["total_cost"] ?? 0,
            "purchase_order_items" => $purchase_order_items,
        ];

        require_once __DIR__ .
            "/../views/purchase_orders/purchase_order_details.php";
    }

    public function receiveItems()
    {
        # product_id | order_quantity | received_quantity | warehouse_id |receive_now

        # Check if the request method is POST
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            throw new SystemException("Request method must be POST");
        }

        # Validate the input data
        if (!isset($_POST["purchase_order_id"]) || !isset($_POST["items"])) {
            throw new ValidationException("All fields are required");
        }

        if (empty($_POST["items"])) {
            throw new ValidationException("At least one item must be received");
        }
        $purchase_order_id = $_POST["purchase_order_id"];
        $items = $_POST["items"];

        # Sanitize the input data
        $purchase_order_id = (int) $purchase_order_id;

        $hasReceiveQuantity = false;
        foreach ($items as &$item) {
            $item["product_id"] = (int) $item["product_id"];
            $item["order_quantity"] = (int) $item["order_quantity"];
            $item["received_quantity"] = (int) $item["received_quantity"];
            $item["warehouse_id"] = (int) $item["warehouse_id"];
            $item["receive_now"] = (int) $item["receive_now"];

            if ($item["receive_now"] > 0 && $item["warehouse_id"] === 0) {
                throw new ValidationException(
                    "Receiveable item(s) must have a warehouse",
                );
            }
            if ($item["warehouse_id"] !== 0 && $item["receive_now"] === 0) {
                throw new ValidationException(
                    "You can not select a warehouse without receiving the item",
                );
            }

            if ($item["receive_now"] !== 0) {
                $hasReceiveQuantity = true;
            }
        }

        if (!$hasReceiveQuantity) {
            throw new ValidationException("At least one item must be received");
        }

        # Receive the items
        PurchaseOrderService::receivePurchaseOrderItems(
            $purchase_order_id,
            $items,
        );

        Session::flashSet("success", "Items received successfully");
        header("Location: $_SERVER[HTTP_REFERER]");
        exit();
    }

    public function approvePurchaseOrder()
    {
        if (!isset($_GET["id"])) {
            throw new SystemException("Purchase order ID is missing");
        }

        $id = (int) $_GET["id"];

        # Approve the purchase order
        PurchaseOrderService::approvePurchaseOrder($id);

        Session::flashSet("success", "Purchase order approved successfully");
        header("Location: $_SERVER[HTTP_REFERER]");
        exit();
    }

    public function cancelPurchaseOrder()
    {
        if (!isset($_GET["id"])) {
            throw new SystemException("Purchase order ID is missing");
        }

        $id = (int) $_GET["id"];

        # Cancel the purchase order
        PurchaseOrderService::cancelPurchaseOrder($id);

        Session::flashSet("success", "Purchase order cancelled successfully");
        header("Location: /purchase_orders");
        exit();
    }
}
