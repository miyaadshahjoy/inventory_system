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
        try {

            global $db_host, $db_user, $db_pass, $db_name;
            $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

            if ($conn->connect_error) {

                throw new SystemException("Database connection failed: . $conn->connect_error");
            }
            return $conn;
        } catch (Exception $e) {
            throw $e;
        }
    }
}