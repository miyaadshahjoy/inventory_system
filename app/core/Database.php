<?php
$db_cred = require __DIR__ . '/../../config/config.php';

$db_host = $db_cred['db_host'];
$db_user = $db_cred['db_user'];
$db_pass = $db_cred['db_pass'];
$db_name = $db_cred['db_name'];

class Database
{
    public static function connect()
    {
        global $db_host, $db_user, $db_pass, $db_name;
        $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

        if ($conn->connect_error) {
            die("DB connection Failed: " . $conn->connect_error);
        }
        return $conn;
    }
}