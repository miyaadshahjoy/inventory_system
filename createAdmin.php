<?php

require_once __DIR__ . '/app/core/Database.php';

$fullname = getenv('ADMIN_FULLNAME') ?: 'Admin User';
$email = getenv('ADMIN_EMAIL') ?: 'admin@example.com';
$role = 'ADMIN';
$password = getenv('ADMIN_PASSWORD') ?: 'admin12345';
$password = password_hash($password, PASSWORD_DEFAULT);

$conn = Database::connect();
$statement = $conn->prepare("INSERT INTO users(full_name, email, password_hash, role) VALUES('$fullname', '$email', '$password', '$role')");
$result = $statement->execute();

if (!$result) {
    die('Admin creation failed');
} else {
    echo 'Admin created successfully';
}