<?php

require_once __DIR__ . "/../core/Database.php";

class AuthController
{
    public function showLogin()
    {
        require_once __DIR__ . '/../views/auth/login.php';
    }
    public function login()
    {
        $conn = Database::connect();
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            die('Email and password are required');
        }

        $statement = $conn->prepare('SELECT * FROM users WHERE email = ?');
        $statement->bind_param('s', $email);
        $statement->execute();

        $result = $statement->get_result();
        $user = $result->fetch_assoc();
        if (empty($user)) {
            die('Invalid credentials');
        }
        if (!password_verify($password, $user['password_hash'])) {
            die('Invalid credentials');
        }

        $_SESSION['user'] = [
            'id' => $user['id'],
            'fullname' => $user['full_name'],
            'email' => $user['email'],
            'role' => $user['role']
        ];

        header("Location: /");
        exit;
    }
}