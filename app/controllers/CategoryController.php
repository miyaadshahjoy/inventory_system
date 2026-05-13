<?php
require_once __DIR__ . '/../core/Database.php';

class CategoriesController
{
    public function index()
    {
        $categories = $this->getAllCategories();
        $data = [
            'categories' => $categories
        ];
        require_once __DIR__ . '/../views/categories.php';
    }

    public function createCategory()
    {
        try {
            # 1) Check if request method is POST
            if (!$_SERVER['REQUEST_METHOD'] === 'POST') {
                throw new Exception("Request method must be POST");
            }

            # 2) Validate input fields
            if (!isset($_POST['name'])) {
                throw new Exception('Category name is required');
            }
            $name = $_POST['name'];

            # 3) Create slug from category name
            $slug = $this->createSlug($name);

            # 4) Insert new database record into categories table
            $conn = Database::connect();
            $statement = $conn->prepare("
            INSERT INTO categories(name, slug) VALUES( ?, ?)
            ");

            $statement->bind_param('ss', $name, $slug);
            if (!$statement->execute()) {
                throw new Exception('Error creating new category.');
            } else {
                header('Location: /categories');
            }

        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }


    }
    public function getAllCategories()
    {
        try {
            $conn = Database::connect();
            $statement = $conn->prepare("
            SELECT name,categories_status,created_at FROM categories 
            ORDER BY created_at DESC
            ");
            if (!$statement->execute()) {
                throw new Exception('Error fetching categories');
            } else {

                $result = $statement->get_result();
                $categories = $result->fetch_all(MYSQLI_ASSOC);
                return $categories;
            }

        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    /*
    public function createCategory($data)
    {
        $category_name = $data["name"];
        $slug = $data["slug"];

        $conn = Database::connect();
        $statement = $conn->prepare("INSERT INTO categories(name, slug) INTO VALUES ('$category_name', '$slug)");

        $resutlt = $statement->execute();
        if (!$resutlt) {
            die("Category creation failed");
        } else {
            echo "Category created successfully.";
        }

    }

    public function getAllCategories()
    {
        $conn = Database::connect();
        $statement = $conn->prepare("SELECT * FROM categories");
        $statement->execute();
        $result = $statement->get_result();
        if (!$result) {
            die("⭕ Error fetching categories.");
        } else {
            $categories = $result->fetch_all(MYSQLI_ASSOC);
            return $categories;
        }
    }
    */
    public function getCategoryById($id)
    {
        $conn = Database::connect();
        $statement = $conn->prepare("SELECT * FROM categories WHERE id = ?");
        $statement->bind_param("i", $id);
        $statement->execute();
        $result = $statement->get_result();
        if (!$result) {
            die("⭕ Error fetching category.");
        } else {
            $category = $result->fetch_assoc();
            return $category;
        }

    }
    public function updateCategory($id, $data)
    {
        $category_name = $data["name"];
        $slug = $data["slug"];

        $conn = Database::connect();
        $statement = $conn->prepare("UPDATE categories SET name = '$category_name',
        slug = '$slug'
        WHERE id = ?
        ");
        $statement->bind_param("i", $id);
        $resutlt = $statement->execute();
        if (!$resutlt) {
            die("Category update failed");
        } else {
            echo "Category updated successfully.";
        }

    }

    public function deleteCategory($id)
    {
        $conn = Database::connect();
        $statement = $conn->prepare("UPDATE categories 
        SET categories_status = 'INACTIVE'
        WHERE id = ?
        )");
        $statement->bind_param("i", $id);

        $resutlt = $statement->execute();
        if (!$resutlt) {
            die("⭕ Error removing category.");
        } else {
            echo "Category removed successfully.";
        }
    }

    public function createSlug($string)
    {
        $words = explode(' ', $string);
        $slug = implode('-', $words);
        return strtolower($slug);
    }
}

