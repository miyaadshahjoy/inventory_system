<?php

require_once __DIR__ . "/../core/Database.php";
require_once __DIR__ . "/../services/InventoryService.php";
require_once __DIR__ . "/../services/CategoryService.php";
require_once __DIR__ . "/../services/ProductService.php";

class ProductController
{

    public function index()
    {
        $products = ProductService::getAllProducts();
        $categories = CategoryService::getAllCategories();
        $data = [
            'products' => $products,
            'categories' => $categories
        ];
        require __DIR__ . '/../views/products.php';
    }
    public function createProduct()
    {
        try {

            # 1) Check if request method is POST
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Invalid request method');
            }

            # 2) Validate input data
            if (!isset($_POST['name']) || !isset($_POST['category_id']) || !isset($_POST['sku']) || !isset($_POST['price']) || !isset($_POST['unit'])) {
                throw new Exception('All fields are required');
            }

            # 3) Sanitize input data
            $name = htmlspecialchars($_POST['name']);
            $category_id = (int) $_POST['category_id'];
            $sku = htmlspecialchars($_POST['sku']);
            $price = (float) $_POST['price'];
            $unit = htmlspecialchars($_POST['unit']);

            $name = trim($name);
            $sku = trim($sku);
            $price = (int) $price;
            $category_id = (int) $category_id;

            # 4) Create and store product in database

            $conn = Database::connect();
            $statement = $conn->prepare("INSERT INTO products(name, category_id, sku, price, unit) VALUES (?, ?, ?, ?, ?)");

            $statement->bind_param("sisis", $name, $category_id, $sku, $price, $unit);
            if (!$statement->execute()) {
                throw new Exception("Product creation failed");
            } else {
                header("Location: /products");
                exit();
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }

    }


    public function getAllProducts()
    {

        $conn = Database::connect();
        $statement = $conn->prepare("SELECT * FROM products");

        if (!$statement->execute()) {
            die("⭕ Error fetching products.");
        } else {
            $result = $statement->get_result();
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
        SET name = '$product_name',
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