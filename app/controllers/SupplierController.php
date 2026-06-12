<?php

class SupplierController
{
    public function index()
    {
        $suppliers = SupplierService::getAllSuppliers();
        $data = [
            "suppliers" => $suppliers,
        ];
        require_once __DIR__ . "/../views/suppliers/index.php";
    }
}
