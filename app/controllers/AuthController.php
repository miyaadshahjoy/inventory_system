<?php

require_once __DIR__ . "/../core/Database.php";
require_once __DIR__ . '/../core/Session.php';


class AuthController
{
    public function showLogin()
    {
        require_once __DIR__ . '/../views/auth/login.php';
    }
    public function login()
    {
        try {

            # 1) Validate request method
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Invalid request method');
            }
            # 2) Validate input
            if (!isset($_POST['email']) || !isset($_POST['password'])) {
                throw new Exception('Email and password are required');
            }

            # 3) Sanitize input
            $email = htmlspecialchars($_POST['email']) ?? '';
            $password = htmlspecialchars($_POST['password']) ?? '';
            $email = trim($email);
            $password = trim($password);

            # 4) Validate email format
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Invalid email format');
            }

            # 5) Authenticate user
            $conn = Database::connect();
            $statement = $conn->prepare('SELECT * FROM users WHERE email = ?');
            $statement->bind_param('s', $email);
            if (!$statement->execute()) {
                throw new Exception("Database error: $statement->error");
            }

            $result = $statement->get_result();
            $user = $result->fetch_assoc();
            if (!$user) {
                throw new Exception('Invalid credentials');
            }
            if (!password_verify($password, $user['password_hash'])) {
                throw new Exception('Invalid credentials');
            }

            $_SESSION['user'] = [
                'id' => $user['id'],
                'fullname' => $user['full_name'],
                'email' => $user['email'],
                'role' => $user['role']
            ];

            Session::flashSet('success', 'Login successful');
            header("Location: /");
            exit;
        } catch (Exception $e) {
            Session::flashSet('error', $e->getMessage());
            header("Location: /login");
            exit;
        }
    }
}