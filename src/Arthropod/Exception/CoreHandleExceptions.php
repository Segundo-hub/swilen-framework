<?php

namespace Swilen\Arthropod\Exception;

use Swilen\Arthropod\Application;

class CoreHandleExceptions
{
     /**
     * The appliaction instance
     *
     * @var \Swilen\Arthropod\Application
     */
    protected $app;

    /**
     * @param \Swilen\Arthropod\Application $app
     *
     * @return void
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Enable exception handler and log manager
     *
     * @return void
     */
    private function enableHandler()
    {
        if (!$this->app->isDevelopmentMode() || !$this->app->isDebugMode()) {
            @ini_set('display_errors', 0);
        }

        $this->normalizeErrorsToException();

        error_reporting(E_ALL);

        set_exception_handler(function (\Throwable $exception) {
            $this->handleException($exception);
        });
    }

    /**
     * Transform error to exception for manage only exception manager
     *
     * @return void
     */
    protected function normalizeErrorsToException()
    {
        \set_error_handler(function ($level, $message, $file = '', $line = 0) {
            throw new \ErrorException($message, 0, $level, $file, $line);
        });

        \register_shutdown_function(function () {
            $error = error_get_last();
            if ($error !== null) {
                $exception = new \ErrorException(
                    $error['message'],
                    0,
                    $error['type'],
                    $error['file'],
                    $error['line']
                );
                $this->handleException($exception);
            }
        });
    }

    /**
     * Handle exceptions
     *
     * @param \Throwable $exception
     *
     * @return \Swilen\Http\Response
     */
    public function handleException(\Throwable $exception)
    {
        $this->exception = $exception;
    }
    /**
     * Determine if the error level is a deprecation.
     *
     * @param  int  $level
     * @return bool
     */
    protected function isDeprecation($level)
    {
        return in_array($level, [E_DEPRECATED, E_USER_DEPRECATED]);
    }

    /**
     * Determine if the error type is fatal.
     *
     * @param  int  $type
     * @return bool
     */
    protected function isFatal($type)
    {
        return in_array($type, [E_COMPILE_ERROR, E_CORE_ERROR, E_ERROR, E_PARSE]);
    }
}
