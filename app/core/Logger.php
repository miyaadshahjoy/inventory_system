<?php

class Logger{
    # log()

    public static function log($type, $message){

        # Format message
        $timestamp = date("Y-m-d H:i:s");
        $type = strtoupper($type);
        $formated_message = "[$timestamp] [$type] $message\n";
        $filepath = __DIR__ . '/../../storage/logs/app.log';
        # Log message into a file
        echo file_put_contents($filepath, $formated_message, FILE_APPEND);
    }
    # info()

    public static function info($message){
        self::log('info', $message);
    }
    # warning()
    public static function warning($message){
        self::log('warning', $message);
    }
    # error()
    public static function error($message){
        self::log('error', $message);
    }
    # critical()
    public static function critical($message){
        self::log('critical', $message);
    }


}