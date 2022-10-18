<?php

namespace Swilen\Arthropod\Exception;

use Swilen\Arthropod\Application;
use Swilen\Arthropod\Contract\ExceptionHandler;
use Swilen\Http\Contract\ResponseContract;
use Swilen\Http\Exception\HttpException;

class Handler implements ExceptionHandler
{
    /**
     * The appliaction instance
     *
     * @var \Swilen\Arthropod\Application
     */
    protected $app;

    /**
     * Application core not report
     *
     * @var array
     */
    protected $skipReport = [];

    /**
     * Application core not report
     *
     * @var array
     */
    protected $internalSkipReport = [
        \Swilen\Http\Exception\HttpException::class
    ];

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
     * Render exception to client
     *
     * {@inheritdoc}
     */
    public function render(\Throwable $exception)
    {
        return $this->app->make(ResponseContract::class)
            ->make($this->transformExceptionToJson($exception), $this->determineStatusCode($exception), [
                'Content-Type' => 'application/json; charset=UTF-8'
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function report(\Throwable $exception)
    {
        if ($this->isSkippableReport($exception)) {
            return;
        }

        $this->log($exception);
    }

    public function transformExceptionToJson(\Throwable $exception)
    {
        return (new JsonFormatter($exception, $this->determineDebugMode()))->format();
    }

    /**
     * Determine status code for exception response
     *
     * @param \Throwable $exception
     *
     * @return int
     */
    public function determineStatusCode(\Throwable $exception)
    {
        if ($exception instanceof HttpException) {
            return (int) $exception->getCode();
        }

        return 500;
    }

    /**
     * Determine if exception is skippable
     *
     * @return bool
     */
    protected function isSkippableReport(\Throwable $exception)
    {
        $skippables = array_merge($this->skipReport, $this->internalSkipReport);

        return !empty(array_filter($skippables, function ($skip) use ($exception) {
            return $exception instanceof $skip;
        }));
    }

    /**
     * @param string|\Stringable $error
     */
    protected function log($error)
    {
        $record = sprintf('[%s]: %s' . PHP_EOL, date('Y-m-d H:i:s'), (string) $error);
        $filename = $this->determineLogFilename();

        if ($filename !== false) {
            error_log($record, 3, $filename);
        } else {
            error_log($record);
        }
    }

    /**
     * Determine log filename append date
     *
     * @return string|false
     */
    protected function determineLogFilename()
    {
        $filename = app_path('storage/logs/swilen-' . date('Y-m-d') . '.log');

        if (is_file($filename) && is_writable($filename)) {
            return $filename;
        }

        if (@touch($filename)) return $filename;

        return false || is_writable($filename);
    }

    /**
     * Determine app is debug mode
     *
     * @return bool
     */
    protected function determineDebugMode()
    {
        return $this->app->isDevelopmentMode() || $this->app->isDebugMode() || filter_var(ini_get('display_errors'), FILTER_VALIDATE_BOOLEAN);
    }
}
