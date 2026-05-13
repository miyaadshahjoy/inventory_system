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

        $conn = Database::connect();
        $statement = $conn->prepare("
        SELECT p.name as product_name, p.sku, c.name as category, ss.quantity as current_stock, p.price, p.unit, p.product_status as status, p.created_at 
        FROM products p INNER JOIN categories c 
        ON p.category_id = c.id
        INNER JOIN stock_snapshots ss
        ON p.id = ss.product_id
        ");

        if (!$statement->execute()) {
            throw new Exception("Error fetching products from DB.");
        } else {
            $result = $statement->get_result();
            $products = $result->fetch_all(MYSQLI_ASSOC);

            return $products;
        }
    }
}