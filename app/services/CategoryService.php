<?php
class CategoryService
{
    public static function createCategory(array $data)
    {
        $name = trim($data["name"]);
        # Create slug from category name
        $slug = self::createSlug($name);

        # Insert new database record into categories table
        $conn = Database::connect();
        $statement = $conn->prepare("
            INSERT INTO categories(name, slug) VALUES( ?, ?)
            ");

        $statement->bind_param("ss", $name, $slug);
        try {
            $statement->execute();
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() === 1062) {
                throw new ValidationException(
                    "Category with the same name already exists",
                );
            }
        }
    }
    public static function getAllCategories(): array
    {
        $conn = Database::connect();
        $statement = $conn->prepare("
            SELECT * 
            FROM categories 
            ORDER BY created_at DESC
        ");
        $statement->execute();

        $result = $statement->get_result();
        $categories = $result->fetch_all(MYSQLI_ASSOC);
        return $categories;
    }

    public static function getAllActiveCategories(): array
    {
        $conn = Database::connect();
        $statement = $conn->prepare("
            SELECT * 
            FROM categories 
            WHERE categories_status = 'ACTIVE'
            ORDER BY created_at DESC
        ");

        $statement->execute();
        $result = $statement->get_result();
        $categories = $result->fetch_all(MYSQLI_ASSOC);
        return $categories;
    }
    public static function getCategoryById(int $id): array
    {
        $conn = Database::connect();
        $statement = $conn->prepare("
            SELECT * 
            FROM categories 
            WHERE id = ?
            AND categories_status = 'ACTIVE'
            FOR UPDATE
        ");
        $statement->bind_param("i", $id);
        $statement->execute();
        $result = $statement->get_result();
        if ($result->num_rows === 0) {
            throw new ValidationException("Category does not exist");
        }
        $category = $result->fetch_assoc();
        return $category;
    }

    public static function updateCategory(int $id, array $data)
    {
        $name = trim($data["name"]);
        # Create slug from category name
        $slug = self::createSlug($name);

        # Update category in DB
        $conn = Database::connect();

        # A) Check if category exists
        $category = self::getCategoryById($id);
        if (!$category) {
            throw new ValidationException("Category does not exist.");
        }

        # B) Update category
        $statement = $conn->prepare("
            UPDATE categories 
            SET name = ?, slug = ?
            WHERE id = ?
        ");

        $statement->bind_param("ssi", $name, $slug, $id);

        try {
            $statement->execute();
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() === 1062) {
                throw new ValidationException(
                    "Category with the same name already exists",
                );
            }
        }
    }

    public static function deleteCategory(int $id)
    {
        $id = (int) $id;

        # Check if category exists
        $category = self::getCategoryById($id);
        if (!$category) {
            throw new ValidationException("Category does not exist.");
        }

        # Delete category
        # Update category status: ACTIVE -> INACTIVE
        $conn = Database::connect();
        $statement = $conn->prepare("
            UPDATE categories 
            SET categories_status = 'INACTIVE'
            WHERE id = ?
        ");
        $statement->bind_param("i", $id);
        $statement->execute();
    }

    public static function createSlug(string $string)
    {
        $words = explode(" ", $string);
        $slug = implode("-", $words);
        return strtolower($slug);
    }
}
