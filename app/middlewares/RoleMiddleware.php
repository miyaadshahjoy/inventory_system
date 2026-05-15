<?php
require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/../views/errors/403.php';
class RoleMiddleware
{
    public static function check($allowedRoles)
    {
        $userRole = $_SESSION['user']['role'];
        if (!in_array($userRole, $allowedRoles)) {
            http_response_code(403);
            require __DIR__ . '/../views/errors/403.php';
            exit;
        }

    }
}