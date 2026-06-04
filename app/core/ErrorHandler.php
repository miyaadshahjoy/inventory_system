<?php

/*
# Error handler manages:
- Handle uncaught exceptions
- Handle fatal errors
- Handle php notices and warnings
- Log errors to a file
- User-friendly error pages
- Production safety: prevent sensitive info leakage

# Architecture:
- Error happens -> ErrorHandler captures it -> Logger writes it -> User sees user-friendly error pages

# Handlers needed:
- Exception handler: set_exception_handler([class, methodname]) -> sets a user-defined function to handle uncaught exceptions
- Error handler: set_error_handler([class, methodname]) -> sets a user-defined function to handle PHP errors (not exceptions)
- Shutdown handler (for fatal errors): register_shutdown_function([class, methodname]) -> registers a function to be executed on script shutdown, useful for catching fatal errors

? bootstraping: Creating a self sustaining process that initializes, builds or runs itself without external help

? [self::class, 'methodName'] 
? self::class -> classname 
*/

class ErrorHandler
{
    public static function register()
    {
        set_exception_handler([self::class, 'exceptionHandler']);
        set_error_handler([self::class, 'errorHandler']);
        register_shutdown_function([self::class, 'shutdownHandler']);

    }

    # Handle uncaught exceptions
    public static function exceptionHandler(Throwable $exception)
    {

        if ($exception instanceof ValidationException) {
            http_response_code(400);
            Session::flashSet('error', $exception->getMessage());
            $redirect = $_SERVER['HTTP_REFERER'] ?? '/';
            header("Location: $redirect");
            exit;
        }
        if ($exception instanceof SystemException) {

            $error_message = $exception->getMessage() . ' in: ' . $exception->getFile() . ' on line: ' . $exception->getLine();
            Logger::critical($error_message);
            self::renderErrorPage();
            exit;
        }

        # Fallback:
        Logger::critical($exception->getMessage() . ' in: ' . $exception->getFile() . ' on line: ' . $exception->getLine());

        self::renderErrorPage();
    }


    # Conver PHP errors (not exceptions) to ErrorException, so they can be handled by the exception handler
    public static function errorHandler(int $errno, string $errstr, string $errfile, int $errline)
    {


        $ignored_errors = [
            E_DEPRECATED,
            E_USER_DEPRECATED,
            E_NOTICE,
            E_USER_NOTICE
        ];

        if (in_array($errno, $ignored_errors)) {
            Logger::warning($errstr);
            return true;
        }
        $error_message = "[$errno] $errstr in $errfile on line $errline";
        # $errno -> error level (E_ERROR, E_WARNING, etc.) / error severity
        throw new ErrorException($error_message, 0, $errno, $errfile, $errline);
    }

    # Handle fatal shutdown errors
    public static function shutdownHandler()
    {
        $error = error_get_last(); # Get the last error that occurred (if any)
        if ($error !== null) {

            if (!isset($error['type'])) {
                return;
            }

            $fatal_errors = [
                E_ERROR,
                E_PARSE,
                E_CORE_ERROR,
                E_COMPILE_ERROR
            ];

            if (!in_array($error['type'], $fatal_errors)) {

                return;
            }
            $error_message = $error['message'] . ' in: ' . $error['file'] . ' on line: ' . $error['line'];
            Logger::critical("[Shutodown] : $error_message");

            self::renderErrorPage();
        }
    }

    # Render generic 500 error page for users 
    public static function renderErrorPage()
    {
        if (!headers_sent()) {

            http_response_code(500);
        }
        require __DIR__ . '/../views/errors/500.php';
        exit;
    }
}


