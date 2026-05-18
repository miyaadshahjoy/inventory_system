<?php

require_once __DIR__ . '/../core/Database.php';

class ProductService
{
    public static function getAllProducts()
    {
        // Product Name 
        // SKU 
        // Category 
        // Current Stock
        // Price
        // Unit
        // Status
        // Created At 
        try {


            $conn = Database::connect();
            $statement = $conn->prepare("
            SELECT p.id, p.name as product_name, p.sku, c.id as category_id, c.name as category, ss.quantity as current_stock, p.price, p.unit, p.product_status as status, p.created_at 
            FROM products p INNER JOIN categories c 
            ON p.category_id = c.id
            INNER JOIN stock_snapshots ss
            ON p.id = ss.product_id
            ");

            if (!$statement->execute()) {
                throw new SystemException("Database error: Error fetching products. $statement->error");
            }
            $result = $statement->get_result();
            if ($result->num_rows === 0) {
                throw new ValidationException("No products found.");
            }
            $products = $result->fetch_all(MYSQLI_ASSOC);

            return $products;
        } catch (Exception $e) {
            throw $e;
        }

    }
}