<?php
require_once __DIR__ . '/../core/Session.php';


class AuthMiddleware
{
    public static function check()
    {
        if (!isset($_SESSION['user'])) {
            Session::flashSet('error', 'Please log in to access this page');
            header("Location: /login");
            exit;
        }
    }
}