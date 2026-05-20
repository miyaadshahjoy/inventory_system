<?php
// ini_set('display_errors', 1);
// error_reporting(E_ALL);
require_once __DIR__ . '/app/core/Database.php';

$fullname = getenv('ADMIN_FULLNAME') ?: 'Admin User';
$email = getenv('ADMIN_EMAIL') ?: 'admin@example.com';
$role = 'ADMIN';
$password = getenv('ADMIN_PASSWORD') ?: 'admin12345';
$password_hash = password_hash($password, PASSWORD_DEFAULT);

$conn = Database::connect();
$statement = $conn->prepare("INSERT INTO users(full_name, email, password_hash, role) VALUES( ?, ?, ?, ?)");

$statement->bind_param('ssss', $fullname, $email, $password_hash, $role);

if (!$statement->execute()) {
    die("Database error: Error creating admin. $statement->error");
}

echo "Admin created successfully.\n";

