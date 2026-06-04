<?php

class SupplierController
{
    public function index()
    {
        $suppliers = $this->getAllSuppliers();
        $data = [
            'suppliers' => $suppliers
        ];
        require_once __DIR__ . '/../views/suppliers/index.php';
    }
    public function getAllActiveSuppliers()
    {


        $conn = Database::connect();
        try {
            $statement = $conn->prepare("
                SELECT * 
                FROM suppliers
                WHERE supplier_status = 'ACTIVE'
                ORDER BY created_at DESC
            ");

            if (!$statement->execute()) {
                throw new SystemException("Database error: Error fetching suppliers. $statement->error");
            }

            $result = $statement->get_result();

            $suppliers = $result->fetch_all(MYSQLI_ASSOC);
            return $suppliers;

        } catch (Exception $e) {
            throw $e;
        }
    }
    public function getAllSuppliers()
    {
        # Supplier Name | Email | Contact Number | Purchase Orders | Status | Created At


        $conn = Database::connect();
        try {
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

            if (!$statement->execute()) {
                throw new SystemException("Database error: Error fetching suppliers. $statement->error");
            }

            $result = $statement->get_result();

            $suppliers = $result->fetch_all(MYSQLI_ASSOC);
            return $suppliers;

        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getSupplierById(int $id)
    {
        $conn = Database::connect();
        try {
            $statement = $conn->prepare("
                SELECT * 
                FROM suppliers
                WHERE id = ? AND supplier_status = 'ACTIVE'
                FOR UPDATE
            ");

            $statement->bind_param("i", $id);

            if (!$statement->execute()) {
                throw new SystemException("Database error: Error fetching supplier. $statement->error");
            }

            $result = $statement->get_result();

            $supplier = $result->fetch_assoc();
            return $supplier;

        } catch (Exception $e) {
            throw $e;
        }
    }
}