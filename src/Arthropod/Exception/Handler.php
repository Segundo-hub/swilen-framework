<?php

namespace Swilen\Arthropod\Exception;

use Swilen\Arthropod\Application;
use Swilen\Arthropod\Contract\ExceptionHandler;
use Swilen\Arthropod\Logger;
use Swilen\Http\Exception\HttpException;
use Swilen\Http\Response\JsonResponse;

class Handler implements ExceptionHandler
{
    /**
     * The appliaction instance.
     *
     * @var \Swilen\Arthropod\Application
     */
    protected $app;

    /**
     * The psr logger implementation.
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Exceptions dont report.
     *
     * @var array
     */
    protected $skipReport = [];

    /**
     * Internal Exceptions dont report.
     *
     * @var array
     */
    protected $internalSkipReport = [
        \Swilen\Http\Exception\HttpException::class,
    ];

    /**
     * @param \Swilen\Arthropod\Application $app
     *
     * @return void
     */
    public function __construct(Application $app)
    {
        $this->app = $app;

        $this->logger = new Logger();
    }

    /**
     * Render exception to client.
     *
     * {@inheritdoc}
     */
    public function render(\Throwable $exception)
    {
        return new JsonResponse(
            $this->transformExceptionToJson($exception),
            $this->determineStatusCode($exception),
            ['Content-Type' => 'application/json; charset=UTF-8'],
            true
        );
    }

    /**
     * Report exception to log file.
     *
     * {@inheritdoc}
     */
    public function report(\Throwable $e)
    {
        if ($this->isSkippableReport($e)) {
            return;
        }

        $this->logger->error($e->getMessage(), ['exception' => $e]);
    }

    /**
     * Transform incoming exception to json.
     *
     * @param \Throwable $exception
     *
     * @return \Swilen\Arthropod\Exception\JsonFormatter
     */
    public function transformExceptionToJson(\Throwable $exception)
    {
        return (new JsonFormatter($exception, $this->determineDebugMode()))->format();
    }

    /**
     * Determine status code for exception response.
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
     * Determine if exception is skippable.
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
     * Determine app is debug mode.
     *
     * @return bool
     */
    protected function determineDebugMode()
    {
        return $this->app->isDevelopmentMode() || $this->app->isDebugMode() || filter_var(ini_get('display_errors'), FILTER_VALIDATE_BOOLEAN);
    }
}
