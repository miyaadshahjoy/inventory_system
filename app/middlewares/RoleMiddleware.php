<?php

class RoleMiddleware
{
    public static function check($allowedRoles)
    {
        $userRole = $_SESSION['user']['role'];
        if (!in_array($userRole, $allowedRoles)) {
            http_response_code(403);
            die('403 - Forbidden: You do not have permission to access this page.');
        }

    }
}