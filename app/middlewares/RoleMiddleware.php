<?php
class RoleMiddleware
{
    public static function check(array $allowedRoles)
    {
        $userRole = $_SESSION['user']['role'];
        if (!in_array($userRole, $allowedRoles)) {
            require __DIR__ . '/../views/errors/403.php';
            exit;
        }

    }
}