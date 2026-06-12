<?php

class AuthService
{
    # Authenticate user
    public static function authenticateUser(
        string $email,
        string $password,
    ): array {
        $conn = Database::connect();
        $statement = $conn->prepare("
            SELECT * 
            FROM users 
            WHERE email = ?
            AND user_status = 'ACTIVE'
        ");
        $statement->bind_param("s", $email);
        $statement->execute();
        $result = $statement->get_result();
        if ($result->num_rows === 0) {
            throw new ValidationException("Incorrect email or password");
        }
        $user = $result->fetch_assoc();
        if (!password_verify($password, $user["password_hash"])) {
            throw new ValidationException("Incorrect email or password");
        }
        return $user;
    }

    public static function loginUser(array $user): void
    {
        $_SESSION["user"] = [
            "id" => $user["id"],
            "fullname" => $user["full_name"],
            "email" => $user["email"],
            "role" => $user["role"],
        ];
    }
}
