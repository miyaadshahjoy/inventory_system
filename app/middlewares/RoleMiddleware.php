<?php
require_once __DIR__ . '/../core/Session.php';
class RoleMiddleware
{
    public static function check($allowedRoles)
    {
        $userRole = $_SESSION['user']['role'];
        if (!in_array($userRole, $allowedRoles)) {
            Session::flashSet('error', 'You do not have permission to access this page');
            header("Location: /");
            exit;
        }

    }
}