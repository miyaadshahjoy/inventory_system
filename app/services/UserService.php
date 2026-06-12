<?php

class UserService
{
    public static function createUser(
        string $full_name,
        string $email,
        string $password,
        string $role,
    ) {
        # 1) Validate input data
        if (
            !isset($full_name) ||
            !isset($email) ||
            !isset($password) ||
            !isset($role)
        ) {
            throw new SystemException(
                "Full name, email, password and role are required",
            );
        }

        # 2) Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        # 3) Insert user into database
        $conn = Database::connect();
        $statement = $conn->prepare("
                INSERT INTO users (full_name, email, password_hash, role)
                VALUES (?, ?, ?, ?)
            ");
        $statement->bind_param(
            "ssss",
            $full_name,
            $email,
            $hashed_password,
            $role,
        );
        $statement->execute();
    }

    public static function getAllUsers()
    {
        $conn = Database::connect();

        $statement = $conn->prepare("
            SELECT *
            FROM users
            ORDER BY created_at DESC
        ");
        $statement->execute();
        $result = $statement->get_result();
        $users = $result->fetch_all(MYSQLI_ASSOC);
        return $users;
    }

    public static function getUserById(int $id)
    {
        $conn = Database::connect();

        $statement = $conn->prepare("
            SELECT *
            FROM users
            WHERE id = ?
            FOR UPDATE
        ");
        $statement->bind_param("i", $id);
        $statement->execute();
        $result = $statement->get_result();
        if ($result->num_rows === 0) {
            throw new ValidationException("User does not exist");
        }
        $user = $result->fetch_assoc();
        return $user;
    }

    public static function deleteUser(int $id)
    {
        # Check if user exists
        $user = self::getUserById($id);
        if (!$user) {
            throw new ValidationException("User does not exist.");
        }

        # Delete user
        # Update user status: ACTIVE -> INACTIVE
        $conn = Database::connect();
        $statement = $conn->prepare("
            UPDATE users
            SET user_status = 'INACTIVE'
            WHERE id = ?
        ");
        $statement->bind_param("i", $id);
        $statement->execute();
    }
}
