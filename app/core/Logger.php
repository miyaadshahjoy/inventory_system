<?php

class Logger
{
    # log()

    public static function log(string $type, string $message)
    {

        # Format message
        $timestamp = date("Y-m-d H:i:s");
        $type = strtoupper($type);
        $formated_message = "[$timestamp] [$type] $message\n";
        $filepath = __DIR__ . '/../../storage/logs/app.log';
        # Log message into a file
        echo file_put_contents($filepath, $formated_message, FILE_APPEND);
    }
    # info()

    public static function info(string $message)
    {
        self::log('info', $message);
    }
    # warning()
    public static function warning(string $message)
    {
        self::log('warning', $message);
    }
    # error()
    public static function error(string $message)
    {
        self::log('error', $message);
    }
    # critical()
    public static function critical(string $message)
    {
        self::log('critical', $message);
    }


}