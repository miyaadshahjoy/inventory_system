<?php

require_once __DIR__ . '/../core/Database.php';

class CategoryService
{
    public static function getAllCategories()
    {

        try {
            $conn = Database::connect();
            $statement = $conn->prepare("
            SELECT id, name FROM categories 
            WHERE categories_status = 'ACTIVE'
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
}