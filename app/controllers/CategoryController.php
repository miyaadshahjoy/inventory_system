<?php

require_once __DIR__ . "/../core/Database.php";

class CategoryController
{

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
}