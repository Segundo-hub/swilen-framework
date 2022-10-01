<?php

namespace Swilen\Arthropod\Exception;

use Swilen\Arthropod\Application;
use Swilen\Http\Exception\HttpException;
use Swilen\Http\Response;

final class CoreExceptionHandler
{
    /**
     * The appliaction instance
     *
     * @var \Swilen\Arthropod\Application
     */
    protected $app;

    /**
     * The exepction instance
     *
     * @var \Throwable
     */
    protected $exception;

    /**
     * The exepction error code
     *
     * @var int
     */
    protected $statusCode;

    /**
     * @param \Swilen\Arthropod\Application $app
     *
     * @return void
     */
    public function __construct(Application $app)
    {
        $this->app = $app;

        $this->enableExceptionHandling();
    }

    private function enableExceptionHandling()
    {
        if (!$this->app->isDevelopmentMode()) {
            ini_set('display_errors', 0);
        }

        $this->normalizeErrorsToException();

        error_reporting(-1);

        set_exception_handler(function (\Throwable $exception) {
            if (!$exception instanceof HttpException) {
                $this->newLogRecord($exception);
            }
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
        set_error_handler(function ($level, $message, $file = '', $line = 0) {
            throw new \ErrorException($message, 0, $level, $file, $line);
        });

        register_shutdown_function(function () {
            $error = error_get_last();
            if ($error !== null) {
                $e = new \ErrorException(
                    $error['message'],
                    0,
                    $error['type'],
                    $error['file'],
                    $error['line']
                );
                $this->handleException($e);
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
        $this->statusCode = $this->determineStatusCode();

        if ($this->app->isDevelopmentMode() || $this->app->isDebugMode() || filter_var(ini_get('display_errors'), FILTER_VALIDATE_BOOLEAN)) {
            $fragment = $this->formatExceptionFragment($exception);
            $fragment['trace'] = array_map(function ($trace) {
                unset($trace['args']);
                return $trace;
            }, $exception->getTrace());

            return $this->app->make(Response::class)
                ->content($this->json_encode($fragment))
                ->status($this->statusCode)
                ->terminate();
        }

        $response = $this->json_encode([
            "type"    => get_class($exception),
            "message" => $this->statusCode === 500 ? 'Internal Server Error' : $exception->getMessage(),
        ]);

        return $this->app->make(Response::class)
            ->content($response)
            ->status($this->statusCode)
            ->terminate();
    }

    /**
     * Determine status code for exception response
     *
     * @return int
     */
    protected function determineStatusCode()
    {
        if ($this->exception instanceof HttpException) {
            return (int) $this->exception->getCode();
        }

        return 500;
    }

    protected function formatExceptionFragment(\Throwable $exception)
    {
        return [
            'type' => get_class($exception),
            'code' => $exception->getCode(),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine()
        ];
    }

    /**
     * @param string|\Throwable $error
     */
    protected function newLogRecord($error)
    {
        $record = sprintf('[%s]: %s' . PHP_EOL . PHP_EOL, date('Y-m-d H:i:s'), (string) $error);

        if (is_file(app_path('storage/logs/Swilen.log'))) {
            error_log($record, 3, app_path('storage/logs/Swilen.log'));
        } else {
            error_log($record);
        }
    }

    protected function json_encode($content, $flags = 0)
    {
        return \json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | $flags);
    }
}
