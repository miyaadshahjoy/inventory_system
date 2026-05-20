<?php

class ProductService
{
    public static function getAllProducts()
    {
        # Product name | SKU | Category | Price | Total Stock | Status | Reorder | Updated | Actions
        try {

            $conn = Database::connect();
            $statement = $conn->prepare("

                SELECT p.id,
                    p.name as product_name,
                    p.sku,
                    p.category_id,
                    c.name as category,
                    p.price,(
                        SELECT SUM(ss.quantity)
                        FROM stock_snapshots ss 
                        WHERE ss.product_id = p.id
                        ) as total_stock,
                    p.unit,
                    p.product_status,
                    p.reorder_level,
                    p.updated_at
                FROM products p JOIN categories c 
                ON p.category_id = c.id
            ");

            if (!$statement->execute()) {
                throw new SystemException("Database error: Error fetching products. $statement->error");
            }
            $result = $statement->get_result();
            // if ($result->num_rows === 0) {
            //     throw new ValidationException("No products found.");
            // }
            $products = $result->fetch_all(MYSQLI_ASSOC);

            return $products;
        } catch (Exception $e) {
            throw $e;
        }

    }
}