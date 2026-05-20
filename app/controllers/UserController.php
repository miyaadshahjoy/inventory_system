<?php

class UserController
{

    public function index()
    {
        $data = [
            'users' => $this->getAllUsers()
        ];
        require_once __DIR__ . '/../views/users/index.php';
    }

    public function create()
    {

        # 1) Validate if request method is POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new SystemException("Invalid request method");
        }
        # 2) Validate form data
        # full_name
        # email
        # password
        # role
        if (!isset($_POST['full_name']) || !isset($_POST['email']) || !isset($_POST['password']) || !isset($_POST['role'])) {
            throw new ValidationException("Full name, email, password and role are required");
        }

        # 3) Sanitize form data
        $full_name = htmlspecialchars($_POST['full_name']);
        $email = htmlspecialchars($_POST['email']);
        $password = htmlspecialchars($_POST['password']);
        $role = htmlspecialchars($_POST['role']);

        $full_name = trim($full_name);
        $email = trim($email);
        $password = trim($password);
        $role = trim($role);

        # 4) Create user
        UserService::createUser($full_name, $email, $password, $role);

        # 5) Redirect to users page
        Session::flashSet('success', 'User created successfully');
        header("Location: /users");
        exit;
    }

    public function getAllUsers()
    {

        $conn = Database::connect();
        try {
            $statement = $conn->prepare("
                SELECT *
                FROM users
                ORDER BY created_at DESC
            ");
            if (!$statement->execute()) {
                throw new SystemException("Database error: Error fetching users. $statement->error");
            }
            $result = $statement->get_result();
            $users = $result->fetch_all(MYSQLI_ASSOC);
            return $users;
        } catch (SystemException $e) {
            throw $e;
        }
    }

    public function getUserById($id)
    {

        $conn = Database::connect();
        try {
            $statement = $conn->prepare("
                SELECT *
                FROM users
                WHERE id = ?
                FOR UPDATE
            ");
            $statement->bind_param("i", $id);
            if (!$statement->execute()) {
                throw new SystemException("Database error: Error fetching user. $statement->error");
            }
            $result = $statement->get_result();
            if ($result->num_rows === 0) {
                throw new ValidationException("User does not exist");
            }
            $user = $result->fetch_assoc();
            return $user;
        } catch (SystemException $e) {
            throw $e;
        }
    }



    public function deleteUser()
    {
        header('Content-Type: application/json');

        $json_data = file_get_contents("php://input");
        $data = json_decode($json_data, true);

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
                "message" => "User id is required"
            ]);
            exit;
        }

        $id = (int) $data["id"];

        # Check if user exists
        $user = $this->getUserById($id);
        if (!$user) {
            http_response_code(404);
            echo json_encode([
                "status" => "failed",
                "message" => "User not found"
            ]);
            exit;
        }

        # Delete user
        $conn = Database::connect();
        $statement = $conn->prepare("
            UPDATE users
            SET user_status = 'INACTIVE'
            WHERE id = ?
        ");
        $statement->bind_param("i", $id);
        if (!$statement->execute()) {
            http_response_code(500);
            echo json_encode([
                "status" => "error",
                "message" => "Database error: Error deleting user. $statement->error"
            ]);
            exit;
        }

        http_response_code(200);
        echo json_encode([
            "status" => "success",
            "message" => "User deleted successfully",
            "data" => [
                "user_status" => 'INACTIVE'
            ]
        ]);
        exit;
    }
}