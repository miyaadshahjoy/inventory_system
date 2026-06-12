<?php

class CategoryController
{
    public function index()
    {
        $categories = CategoryService::getAllCategories();
        $data = [
            "categories" => $categories,
        ];
        require_once __DIR__ . "/../views/categories/index.php";
    }

    public function createCategory()
    {
        # 1) Check if request method is POST
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            throw new ApplicationException("Request method must be POST");
        }

        # 2) Validate input fields
        if (!isset($_POST["name"])) {
            throw new ValidationException("Category name is required");
        }
        $name = trim($_POST["name"]);

        # 3) Create new category
        CategoryService::createCategory(["name" => $name]);

        Session::flashSet("success", "Category created successfully");
        header("Location: /categories");
        exit();
    }

    public function updateCategory()
    {
        # 1) Check if request method is POST
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            throw new ApplicationException("Request method must be POST");
        }

        # 2) Validate input fields
        if (!isset($_POST["id"])) {
            throw new ApplicationException("Category id is required");
        }
        if (!isset($_POST["name"])) {
            throw new ValidationException("Category name is required");
        }

        # 3) sanitize input fields
        $id = (int) $_POST["id"];
        $name = trim($_POST["name"]);

        # 4) Update category
        CategoryService::updateCategory($id, ["name" => $name]);

        Session::flashSet("success", "Category updated successfully.");
        header("Location: /categories");
        exit();
    }

    # Delete category using AJAX
    /*
    public function deleteCategory()
    {
        
        # How to retrieve data from the request body (json data)
        - php://input -> a stream resource that is used to read the raw data from the request body.
        - file_get_contents('php://input') -> reads the raw data from the request body and returns it as a string.
        - json_decode(file_get_contents('php://input')) -> decodes the JSON data from the request body and returns it as an associative array.
        

        header("Content-Type: application/json");

        $json_data = file_get_contents("php://input");
        $data = json_decode($json_data, true); # convert json to associative array
        if (!$data) {
            http_response_code(400);
            echo json_encode([
                "status" => "failed",
                "message" => "Invalid request",
            ]);
            exit();
        }

        if (!isset($data["id"])) {
            http_response_code(400);
            echo json_encode([
                "status" => "failed",
                "message" => "Category id is required",
            ]);
            exit();
        }

        $id = $data["id"];
        $id = (int) $id;

        # Check if category exists
        $category = $this->getCategoryById($id);
        if (!$category) {
            http_response_code(404);
            echo json_encode([
                "status" => "failed",
                "message" => "Category not found",
            ]);
            exit();
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
                "message" => "Database error: Error deleting category. $statement->error",
            ]);
            exit();
        } else {
            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "message" => "Category deleted successfully.",
                "data" => [
                    "category_status" => "INACTIVE",
                ],
            ]);
            exit();
        }
    }
    */

    public function deleteCategory()
    {
        # Check if category id exists
        if (!isset($_GET["id"])) {
            throw new ApplicationException("Category id is required");
        }
        $id = (int) $_GET["id"];

        # Delete category
        CategoryService::deleteCategory($id);

        # Redirect to categories
        Session::flashSet("success", "Category deleted successfully.");
        header("Location: /categories");
        exit();
    }
}
