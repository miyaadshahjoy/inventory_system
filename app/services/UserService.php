<?php

class UserService
{
    public static function createUser(string $full_name, string $email, string $password, string $role)
    {
        try {

            # 1) Validate input data
            if (!isset($full_name) || !isset($email) || !isset($password) || !isset($role)) {
                throw new SystemException("Full name, email, password and role are required");
            }

            # 2) Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            # 3) Insert user into database
            $conn = Database::connect();
            $statement = $conn->prepare("
                INSERT INTO users (full_name, email, password_hash, role)
                VALUES (?, ?, ?, ?)
            ");
            $statement->bind_param("ssss", $full_name, $email, $hashed_password, $role);
            if (!$statement->execute()) {
                throw new SystemException("Database error: Error creating user. $statement->error");
            }


        } catch (Exception $e) {
            throw $e;
        }

    }
}