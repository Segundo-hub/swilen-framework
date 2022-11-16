<?php

namespace Swilen\Arthropod\Exception;

use Swilen\Arthropod\Contract\ExceptionFormatter;
use Swilen\Http\Exception\HttpException;

class JsonFormatter implements ExceptionFormatter
{
    /**
     * Default encoding options for serialize this exception in json.
     *
     * @var int
     */
    protected $encodingOptions = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION
        | JSON_INVALID_UTF8_SUBSTITUTE | JSON_PARTIAL_OUTPUT_ON_ERROR | JSON_PRETTY_PRINT;

    /**
     * The current exception passed.
     *
     * @var \Throwable
     */
    protected $exception;

    /**
     * Indicates if application is debug mode.
     *
     * @var bool
     */
    protected $debugMode = false;

    /**
     * Create new JsonFormatter for exception.
     *
     * @param \Throwable $exception
     *
     * @return void
     */
    public function __construct(\Throwable $exception, bool $debugMode)
    {
        $this->exception = $exception;
        $this->debugMode = $debugMode;
    }

    /**
     * {@inheritdoc}
     *
     * Format and serialize exception to json.
     *
     * @return string
     */
    public function format()
    {
        return json_encode($this->formatExceptionFragment($this->exception), $this->encodingOptions);
    }

    /**
     * @param \Throwable $exception
     *
     * @return array
     */
    public function formatExceptionFragment(\Throwable $exception)
    {
        return $this->debugMode
            ? [
                'type' => get_class($exception),
                'message' => $exception->getMessage(),
                'code' => $exception->getCode(),
                'file' => $exception->getFile().':'.$exception->getLine(),
                'trace' => $this->formatTraceFragment(),
            ]
            : [
                'type' => get_class($exception),
                'message' => $exception instanceof HttpException ? $exception->getMessage() : 'Internal Server Error',
            ];
    }

    /**
     * Format trace without args.
     *
     * @return array
     */
    protected function formatTraceFragment()
    {
        return array_map(function ($trace) {
            if (isset($trace['file']) && isset($trace['line'])) {
                $trace['file'] = $trace['file'].':'.$trace['line'];
            }

            unset($trace['args'], $trace['line']);

            return $trace;
        }, $this->exception->getTrace());
    }
}
