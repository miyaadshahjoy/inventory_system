<?php
/*
# What a proper flash system must do:
- Survive redirect
- Disappear after one request
- Support multiple types
- Work globally
- Be reusable

# Workflow:
1) Controller action
2) Set flash message
3) Redirect to another page
4) Page loads
5) Display flash message on the redirected page
6) Flash deleted after display

# Flash Types:
- Success: Green background, checkmark icon
- Error: Red background, cross icon
- Warning: Yellow background, exclamation icon
- Info: Blue background, info icon
*/

class Session
{
    public static function flashSet(string $type, string $message)
    {

        if (!isset($_SESSION['flash'])) {
            $_SESSION['flash'] = [];
        }
        $_SESSION['flash'] = [
            $type => $message
        ];
    }

    public static function flashGet(string $type)
    {
        if (!isset($_SESSION['flash'][$type])) {
            return null;
        }
        $message = $_SESSION['flash'][$type];

        self::flashClear($type);
        return $message;
    }
    public static function flashClear(string $type)
    {
        if (isset($_SESSION['flash'][$type])) {
            unset($_SESSION['flash'][$type]);
        }
        if (empty($_SESSION['flash'])) {
            unset($_SESSION['flash']);
        }
    }

    public static function hasFlash(string $type)
    {
        return isset($_SESSION['flash'][$type]);
    }

}
