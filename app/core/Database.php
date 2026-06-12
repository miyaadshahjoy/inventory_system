<?php
$db_cred = require __DIR__ . '/../../config/config.php';

$db_host = $db_cred['db_host'];
$db_user = $db_cred['db_user'];
$db_pass = $db_cred['db_pass'];
$db_name = $db_cred['db_name'];

class Database
{
    private static ?mysqli $connection = null;
    public static function connect() : mysqli
    {

        if(self::$connection !== null){
            return self::$connection;
        }
        try {

            global $db_host, $db_user, $db_pass, $db_name;
            self::$connection = new mysqli($db_host, $db_user, $db_pass, $db_name);

            self::$connection->set_charset('utf8mb4');

           
            return self::$connection;
        } catch (Exception $e) {
            throw new DatabaseException('Database connection failded: ' . $e->getMessage(), 0, $e);
        }
    }
}

