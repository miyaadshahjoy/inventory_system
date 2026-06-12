<?php

class ReturnController
{
    public function index()
    {
        $products = ProductService::getAllActiveProducts();
        $warehouses = WarehouseService::getAllActiveWarehouses();
        $returns = ReturnService::getAllReturns();

        $data = [
            "products" => $products,
            "warehouses" => $warehouses,
            "returns" => $returns,
        ];
        require_once __DIR__ . "/../views/returns/index.php";
    }

    public function create()
    {
        # 1) Check if request method is POST
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            throw new SystemException("Request method must be POST");
        }

        # 2) Validate form data
        # product_id
        # warehouse_id
        # quantity
        # reason

        if (
            !isset($_POST["product_id"]) ||
            !isset($_POST["warehouse_id"]) ||
            !isset($_POST["quantity"])
        ) {
            throw new ValidationException(
                "All fields are required except reason",
            );
        }

        # 3) Sanitize form data

        $product_id = $_POST["product_id"];
        $warehouse_id = $_POST["warehouse_id"];
        $quantity = $_POST["quantity"];
        $reason = $_POST["reason"] ?? null;

        $product_id = (int) $product_id;
        $warehouse_id = (int) $warehouse_id;
        $quantity = (int) $quantity;
        $reason = trim($reason);

        # 4) Create return
        $return = ReturnService::createReturn(
            $product_id,
            $warehouse_id,
            $quantity,
            $reason,
        );
        if (!$return) {
            throw new ValidationException("Failed to process return");
        }

        Session::flashSet("success", "Return created successfully");
        header("Location: /returns");
        exit();
    }
}
