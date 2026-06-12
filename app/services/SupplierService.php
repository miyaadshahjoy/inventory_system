<?php

class SupplierService
{
    public static function getAllActiveSuppliers()
    {
        $conn = Database::connect();

        $statement = $conn->prepare("
            SELECT * 
            FROM suppliers
            WHERE supplier_status = 'ACTIVE'
            ORDER BY created_at DESC
        ");

        $statement->execute();
        $result = $statement->get_result();
        $suppliers = $result->fetch_all(MYSQLI_ASSOC);
        return $suppliers;
    }
    public static function getAllSuppliers()
    {
        # Supplier Name | Email | Contact Number | Purchase Orders | Status | Created At

        $conn = Database::connect();

        $statement = $conn->prepare("
            SELECT s.id, 
                s.supplier_name,
                s.email,
                s.phone,
                s.supplier_status,
                (
                    SELECT COUNT(po.id) 
                    FROM purchase_orders po
                    WHERE po.supplier_id = s.id
                ) AS purchase_orders,
                s.created_at
                FROM suppliers s
                ORDER BY s.created_at DESC
        ");

        $statement->execute();
        $result = $statement->get_result();
        $suppliers = $result->fetch_all(MYSQLI_ASSOC);
        return $suppliers;
    }

    public static function getSupplierById(int $id)
    {
        $conn = Database::connect();

        $statement = $conn->prepare("
            SELECT * 
            FROM suppliers
            WHERE id = ? AND supplier_status = 'ACTIVE'
            FOR UPDATE
        ");

        $statement->bind_param("i", $id);
        $statement->execute();
        $result = $statement->get_result();
        $supplier = $result->fetch_assoc();
        return $supplier;
    }
}
