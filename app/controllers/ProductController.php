<?php

require_once __DIR__ . "/../core/Database.php";

class ProductController
{
    public function showProducts()
    {

    }
    public function createProduct($data)
    {
        $product_name = $data["name"];
        $category_id = $data["category_id"];
        $sku = $data["sku"];
        $price = $data["price"];
        $unit = $data["unit"];

        $conn = Database::connect();
        $statement = $conn->prepare("INSERT INTO products(name, category_id, sku, price, unit) INTO VALUES ('$product_name', '$category_id', '$sku', '$price', '$unit')");

        $resutlt = $statement->execute();
        if (!$resutlt) {
            die("Product creation failed");
        } else {
            echo "Product created successfully.";
        }

    }

    public function getAllProducts()
    {
        $conn = Database::connect();
        $statement = $conn->prepare("SELECT * FROM products");
        $statement->execute();
        $result = $statement->get_result();
        if (!$result) {
            die("⭕ Error fetching products.");
        } else {
            $products = $result->fetch_all(MYSQLI_ASSOC);
            return $products;
        }
    }
    public function getProductById($id)
    {
        $conn = Database::connect();
        $statement = $conn->prepare("SELECT * FROM products WHERE id = ?");
        $statement->bind_param("i", $id);
        $statement->execute();
        $result = $statement->get_result();
        if (!$result) {
            die("⭕ Error fetching product.");
        } else {
            $product = $result->fetch_assoc();
            return $product;
        }

    }
    public function updateProduct($id, $data)
    {
        $product_name = $data["name"];
        $category_id = $data["category_id"];
        $sku = $data["sku"];
        $price = $data["price"];
        $unit = $data["unit"];

        $conn = Database::connect();
        $statement = $conn->prepare("UPDATE products 
        SET product = '$product_name',
        category_id = '$category_id',
        sku = '$sku', 
        price = '$price',
        unit = '$unit'
        WHERE id = ?
        )");
        $statement->bind_param("i", $id);

        $resutlt = $statement->execute();
        if (!$resutlt) {
            die("⭕ Error updating product.");
        } else {
            echo "Product updated successfully.";
        }
    }

    public function deleteProduct($id)
    {
        $conn = Database::connect();
        $statement = $conn->prepare("UPDATE products 
        SET product_status = 'INACTIVE'
        WHERE id = ?
        )");
        $statement->bind_param("i", $id);

        $resutlt = $statement->execute();
        if (!$resutlt) {
            die("⭕ Error removing product.");
        } else {
            echo "Product removed successfully.";
        }

    }


}