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
        require_once __DIR__ . '/../views/categories/index.php';
    }

    public function createCategory()
    {
        try {
            # 1) Check if request method is POST
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new SystemException("Request method must be POST");
            }

            # 2) Validate input fields
            if (!isset($_POST['name'])) {
                throw new ValidationException('Category name is required');
            }
            $name = trim($_POST['name']);

            # 3) Create slug from category name
            $slug = $this->createSlug($name);

            # 4) Insert new database record into categories table
            $conn = Database::connect();
            $statement = $conn->prepare("
            INSERT INTO categories(name, slug) VALUES( ?, ?)
            ");

            $statement->bind_param('ss', $name, $slug);
            try {
                $statement->execute();
            } catch (mysqli_sql_exception $e) {
                if ($e->getCode() === 1062) {
                    throw new ValidationException("Category with the same name already exists");
                }
                throw new SystemException("Database error: Error creating category. $statement->error");
            }

            Session::flashSet('success', 'Category created successfully');
            header('Location: /categories');
            exit;


        } catch (Exception $e) {
            throw $e;
        }


    }
    public function getAllCategories()
    {
        try {
            $conn = Database::connect();
            $statement = $conn->prepare("
            SELECT * 
            FROM categories 
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
            throw $e;
        }
    }



    public static function getAllActiveCategories()
    {
        try {
            $conn = Database::connect();
            $statement = $conn->prepare("
            SELECT * 
            FROM categories 
            WHERE categories_status = 'ACTIVE'
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
            throw $e;
        }
    }
    public function getCategoryById(int $id)
    {

        try {

            $conn = Database::connect();
            $statement = $conn->prepare("
                SELECT * 
                FROM categories 
                WHERE id = ?
                AND categories_status = 'ACTIVE'
                FOR UPDATE
            ");
            $statement->bind_param("i", $id);
            if (!$statement->execute()) {
                throw new SystemException("Database error: Error fetching category. $statement->error");
            }
            $result = $statement->get_result();
            if ($result->num_rows === 0) {
                throw new ValidationException("Category does not exist");
            }
            $category = $result->fetch_assoc();
            return $category;
        } catch (Exception $e) {
            throw $e;
        }

    }
    public function updateCategory()
    {
        try {
            # 1) Check if request method is POST
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new SystemException("Request method must be POST");
            }

            # 2) Validate input fields
            if (!isset($_POST['id'])) {
                throw new SystemException('Category id is required');
            }
            if (!isset($_POST['name'])) {
                throw new ValidationException('Category name is required');
            }


            # 3) sanitize input fields
            $id = htmlspecialchars($_POST['id']);
            $name = htmlspecialchars($_POST['name']);
            $id = (int) $id;
            $name = trim($name);

            # 4) Create slug from category name
            $slug = $this->createSlug($name);

            # 5) Update category in DB
            $conn = Database::connect();

            # 5.A) Check if category exists
            $category = $this->getCategoryById($id);
            if (!$category) {
                throw new ValidationException("Category does not exist.");
            }

            # 5.B) Update category
            $statement = $conn->prepare("
                UPDATE categories 
                SET name = ?, slug = ?
                WHERE id = ?
            ");

            $statement->bind_param('ssi', $name, $slug, $id);

            try {
                $statement->execute();
            } catch (mysqli_sql_exception $e) {
                if ($e->getCode() === 1062) {
                    throw new ValidationException("Category with the same name already exists");
                }
                throw new SystemException("Database error: Error updating category. $statement->error");
            }
            Session::flashSet('success', 'Category updated successfully.');
            header('Location: /categories');
            exit;


        } catch (Exception $e) {
            throw $e;
        }

    }

    public function deleteCategory()
    {
        /*
        # How to retrieve data from the request body (json data)
        - php://input -> a stream resource that is used to read the raw data from the request body.
        - file_get_contents('php://input') -> reads the raw data from the request body and returns it as a string.
        - json_decode(file_get_contents('php://input')) -> decodes the JSON data from the request body and returns it as an associative array.
        */

        header('Content-Type: application/json');

        $json_data = file_get_contents("php://input");
        $data = json_decode($json_data, true); # convert json to associative array

        if (!$data) {
            http_response_code(400);
            echo json_encode([
                "status" => "failed",
                "message" => "Invalid request",
            ]);
            exit;
        }

        if (!isset($data["id"])) {
            http_response_code(400);
            echo json_encode([
                "status" => "failed",
                "message" => "Category id is required"
            ]);
            exit;
        }

        $id = $data["id"];
        $id = (int) $id;

        # Check if category exists
        $category = $this->getCategoryById($id);
        if (!$category) {
            http_response_code(404);
            echo json_encode([
                "status" => "failed",
                "message" => "Category not found"
            ]);
            exit;
        }
        $conn = Database::connect();
        $statement = $conn->prepare("
            UPDATE categories 
            SET categories_status = 'INACTIVE'
            WHERE id = ?
            ");
        $statement->bind_param("i", $id);

        if (!$statement->execute()) {
            http_response_code(500);
            echo json_encode([
                "status" => "error",
                "message" => "Database error: Error deleting category. $statement->error"
            ]);
            exit;
        } else {
            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "message" => "Category deleted successfully.",
                "data" => [
                    "category_status" => "INACTIVE"
                ]
            ]);
            exit;
        }

    }

    public function createSlug(string $string)
    {
        $words = explode(' ', $string);
        $slug = implode('-', $words);
        return strtolower($slug);
    }
}

