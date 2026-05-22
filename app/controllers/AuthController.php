<?php


class AuthController
{
    public function index()
    {
        require_once __DIR__ . '/../views/auth/login.php';
    }
    public function login()
    {
        try {

            # 1) Validate request method
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new SystemException('Invalid request method');
            }
            # 2) Validate input
            if (!isset($_POST['email']) || !isset($_POST['password'])) {
                throw new ValidationException('Email and password are required');
            }

            # 3) Sanitize input
            $email = htmlspecialchars($_POST['email']) ?? '';
            $password = htmlspecialchars($_POST['password']) ?? '';
            $email = trim($email);
            $password = trim($password);

            # 4) Validate email format
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new ValidationException('Invalid email format');
            }

            # 5) Authenticate user
            $conn = Database::connect();
            $statement = $conn->prepare("
                SELECT * 
                FROM users 
                WHERE email = ?
                AND user_status = 'ACTIVE'
            ");
            $statement->bind_param('s', $email);
            if (!$statement->execute()) {
                throw new SystemException("Database error: Error fetching user. $statement->error");
            }

            $result = $statement->get_result();
            if ($result->num_rows === 0) {
                throw new ValidationException('Incorrect email');
            }
            $user = $result->fetch_assoc();
            if (!password_verify($password, $user['password_hash'])) {
                throw new ValidationException('Incorrect password');
            }

            $_SESSION['user'] = [
                'id' => $user['id'],
                'fullname' => $user['full_name'],
                'email' => $user['email'],
                'role' => $user['role']
            ];

            Session::flashSet('success', 'Login successful');
            Logger::info("User logged in: {$user['email']} (ID: {$user['id']})");
            header("Location: /inventory-overview");
            exit;
        } catch (Exception $e) {
            throw $e;
        }
    }

    # Make users logout by destroying session
    public function logout()
    {
        unset($_SESSION['user']);
        session_destroy();
        Session::flashSet('success', 'Logged out successfully');
        header("Location: /login");
        exit;
    }
}