<?php


class ReturnController
{
    public function index()
    {
        $productController = new ProductController();
        $products = $productController->getAllActiveProducts();

        $warehouseController = new WarehouseController();
        $warehouses = $warehouseController->getAllActiveWarehouses();

        $returns = ReturnService::getAllReturns();

        $data = [
            'products' => $products,
            'warehouses' => $warehouses,
            'returns' => $returns

        ];
        require_once __DIR__ . '/../views/returns/index.php';
    }

    public function create()
    {
        try {


            # 1) Check if request method is POST
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new SystemException("Request method must be POST");
            }

            # 2) Validate form data 
            # product_id
            # warehouse_id
            # quantity
            # reason

            if (!isset($_POST['product_id']) || !isset($_POST['warehouse_id']) || !isset($_POST['quantity'])) {
                throw new ValidationException("All fields are required except reason");
            }

            # 3) Sanitize form data

            $product_id = htmlspecialchars($_POST['product_id']);
            $warehouse_id = htmlspecialchars($_POST['warehouse_id']);
            $quantity = htmlspecialchars($_POST['quantity']);
            $reason = htmlspecialchars($_POST['reason']) ?? null;

            $product_id = (int) $product_id;
            $warehouse_id = (int) $warehouse_id;
            $quantity = (int) $quantity;
            $reason = trim($reason);

            # 4) Create return
            $return = ReturnService::createReturn($product_id, $warehouse_id, $quantity, $reason);
            if (!$return) {
                throw new ValidationException("Failed to process return");
            }

            Session::flashSet('success', 'Return created successfully');
            header('Location: /returns');
            exit;

        } catch (Exception $e) {
            throw $e;
        }

    }
}