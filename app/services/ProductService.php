<?php

class ProductService
{
    public static function getAllProducts(int $page, int $limit, array $filter_data)
    {
        # Product name | SKU | Category | Price | Total Stock | Status | Reorder | Updated | Actions
        try {

            # Pagination data
            $page = max($page, 1);
            $offset = ($page - 1) * $limit;

            /*
            #filter data
            - product_search
            - product_category
            - start_date
            - end_date
            - sort_by
            - min_price
            - max_price
            - product_status
            */
            $product_search = $filter_data['product_search'] ?? null;
            $product_category = $filter_data['product_category'] ?? null;
            $start_date = $filter_data['start_date'] ?? null;
            $end_date = $filter_data['end_date'] ?? null;
            $sort_by = $filter_data['sort_by'] ?? null; # name, price, created_at
            $min_price = $filter_data['min_price'] ?? null;
            $max_price = $filter_data['max_price'] ?? null;
            $product_status = $filter_data['product_status'] ?? null; # ACTIVE, INACTIVE

            $conn = Database::connect();

            # Query
            $query = "
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
                    p.updated_at,
                    ROW_NUMBER() OVER (ORDER BY p.updated_at DESC) as rn
                FROM products p JOIN categories c 
                ON p.category_id = c.id 
                WHERE 1=1
            ";
            # Parameter types
            $param_types = "";
            # Parameters
            $params = [];
            # Search
            if ($product_search) {
                $query .= " AND (p.name LIKE ? OR p.sku LIKE ?)";
                $param_types .= "ss";
                array_push($params, '%' . $product_search . '%', '%' . $product_search . '%');
            }

            # Category
            if ($product_category) {
                $query .= " AND p.category_id = ?";
                $param_types .= "i";
                array_push($params, $product_category);
            }

            # Start date
            if ($start_date) {
                $query .= " AND DATE(p.created_at) >= ?";
                $param_types .= "s";
                array_push($params, $start_date);
            }

            # End date
            if ($end_date) {
                $query .= " AND DATE(p.created_at) <= ?";
                $param_types .= "s";
                array_push($params, $end_date);

            }

            # Min price
            if ($min_price) {
                $query .= " AND p.price >= ?";
                $param_types .= "d";
                array_push($params, $min_price);
            }

            # Max price
            if ($max_price) {
                $query .= " AND p.price <= ?";
                $param_types .= "d";
                array_push($params, $max_price);
            }

            # Status
            if ($product_status) {
                $query .= " AND p.product_status = ?";
                $param_types .= "s";
                array_push($params, $product_status);
            }



            # Pagination
            $query .= " ORDER BY p.created_at DESC LIMIT ? OFFSET ?";
            $param_types .= "ii";
            array_push($params, $limit, $offset);

            # Sort by
            if ($sort_by) {
                $query = str_replace("ORDER BY p.created_at DESC", "ORDER BY p.$sort_by DESC", $query);
            }

            // echo "<pre>";
            // print_r($params);
            echo ($query);
            $statement = $conn->prepare($query);

            $statement->bind_param($param_types, ...$params);

            if (!$statement->execute()) {
                throw new SystemException("Database error: Error fetching products. $statement->error");
            }
            $result = $statement->get_result();
            $products = $result->fetch_all(MYSQLI_ASSOC);

            return $products;
        } catch (Exception $e) {
            throw $e;
        }

    }
}