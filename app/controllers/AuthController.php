<?php

class AuthController
{
    public function index()
    {
        require_once __DIR__ . "/../views/auth/login.php";
    }
    public function login()
    {
        # 1) Validate request method
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            throw new ApplicationException("Invalid request method");
        }
        # 2) Validate input
        if (!isset($_POST["email"]) || !isset($_POST["password"])) {
            throw new ValidationException("Email and Password are required");
        }

        # 3) Sanitize input
        $email = $_POST["email"] ?? "";
        $password = $_POST["password"] ?? "";
        $email = trim($email);
        $password = trim($password);

        # 4) Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new ValidationException("Invalid email format");
        }

        # 5) Authenticate user
        $user = AuthService::authenticateUser($email, $password);

        # 6) Set session data: Login user
        AuthService::loginUser($user);

        Session::flashSet("success", "Login successful");
        Logger::info("User logged in: {$user["email"]} (ID: {$user["id"]})");
        header("Location: /inventory-overview");
        exit();
    }

    # Make users logout by destroying session
    public function logout()
    {
        unset($_SESSION["user"]);
        session_destroy();
        Session::flashSet("success", "Logged out successfully");
        header("Location: /login");
        exit();
    }
}
