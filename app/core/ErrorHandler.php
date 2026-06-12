<?php

/*
    # 1) Bootstrap

    |> Bootstraping: Creating a self sustaining process that initializes, builds or runs itself without external help.
    |> INFO: Bootstrapping the error handler to catch all errors and exceptions in a centralized way

    
    # 2) ErrorHandler::register()

    |> PHP Errors
    |> PHP Fatal Errors
    |> MySQL Errors
    |> Application Exceptions

    <?php

    -> abstract class ApplicationException extends Exception {}
    -> abstract class SystemException extends Exception {}
    -> class ValidationException extends ApplicationException {}
    -> class AuthorizationException extends ApplicationException {}
    -> class NotFoundException extends ApplicationException {}
    -> class DatabaseException extends SystemException {}


    # 3) Throwable


    # 4) ErrorHandler


    # 5) Classify

    |> ValidationException
    |> AuthorizationException
    |> NotFoundException
    |> DatabaseException
    |> SystemException


    # 6) Logger


    # 7) 400 / 403 / 404 / 500 pages
*/

class ErrorHandler
{
    private static bool $handlingException = false;
    public static function register(): void
    {
        # Register the exception handler
        set_exception_handler([self::class, "exceptionHandler"]);
        # Register the error handler
        set_error_handler([self::class, "errorHandler"]);
        # Register the shutdown handler
        register_shutdown_function([self::class, "shutdownHandler"]);
    }

    # Handle uncaught exceptions
    public static function exceptionHandler(Throwable $exception): void
    {
        if (self::$handlingException) {
            self::render500();
        }
        self::$handlingException = true;
        self::logException($exception);
        self::renderException($exception);
    }

    # Handle uncaught Errors
    public static function errorHandler(
        int $errno,
        string $errstr,
        string $errfile,
        int $errline,
    ): bool {
        if (!(error_reporting() & $errno)) {
            return true;
        }

        $ignored = [E_DEPRECATED, E_USER_DEPRECATED];
        if (in_array($errno, $ignored, true)) {
            Logger::warning($errstr);
            return true;
        }

        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    }

    # Handle fatal shutdown errors
    public static function shutdownHandler(): void
    {
        $error = error_get_last(); # Get the last error that occurred (if any)
        if ($error !== null) {
            if (!isset($error["type"])) {
                return;
            }

            $fatal_errors = [
                E_ERROR,
                E_PARSE,
                E_CORE_ERROR,
                E_COMPILE_ERROR,
                E_USER_ERROR,
                E_RECOVERABLE_ERROR,
            ];

            if (!in_array($error["type"], $fatal_errors)) {
                return;
            }

            $exception = new ErrorException(
                $error["message"],
                0,
                $error["type"],
                $error["file"],
                $error["line"],
            );

            self::logException($exception);

            self::render500();
        }
    }

    # Render Exception
    private static function renderException(Throwable $exception): void
    {
        switch (true) {
            case $exception instanceof ValidationException:
                self::handleValidation($exception);
                break;

            case $exception instanceof AuthorizationException:
                self::render403();
                break;

            case $exception instanceof NotFoundException:
                self::render404();
                break;

            default:
                self::render500();
                break;
        }
    }

    # Log Exception function
    private static function logException(Throwable $exception): void
    {
        $message = self::buildLogMessage($exception);
        Logger::critical($message);
    }

    # Build Log message
    private static function buildLogMessage(Throwable $exception): string
    {
        $message = sprintf(
            "
        [%s]\nMESSAGE: %s\nFILE: %s\nLINE: %d\nTRACE: %s\n",
            get_class($exception),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            $exception->getTraceAsString(),
        );
        if ($exception->getPrevious()) {
            $message .= sprintf(
                "\n\nPREVIOUS EXCEPTION:\n[%s] %s",
                get_class($exception->getPrevious()),
                $exception->getPrevious()->getMessage(),
            );
        }
        return $message;
    }

    # Handle validation exceptions
    private static function handleValidation(
        ValidationException $exception,
    ): void {
        Session::flashSet("error", $exception->getMessage());

        $redirect = $_SERVER["HTTP_REFERER"] ?? "/dashboard";

        header("Location: $redirect");
        exit();
    }

    private static function clearOutputBuffers(): void
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
    }

    private static function render403(): void
    {
        self::clearOutputBuffers();
        http_response_code(403);
        require __DIR__ . "/../views/errors/403.php";
        exit();
    }
    private static function render404(): void
    {
        self::clearOutputBuffers();
        http_response_code(404);
        require __DIR__ . "/../views/errors/404.php";
        exit();
    }
    private static function render500(): void
    {
        self::clearOutputBuffers();
        http_response_code(500);
        require __DIR__ . "/../views/errors/500.php";
        exit();
    }
}
