<?php

namespace Swilen\Arthropod\Exception;

use Swilen\Arthropod\Contract\ExceptionFormatter;
use Swilen\Http\Exception\HttpException;

class JsonFormatter implements ExceptionFormatter
{
    /**
     * @var int
     */
    protected $encodingOptions = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION
        | JSON_INVALID_UTF8_SUBSTITUTE | JSON_PARTIAL_OUTPUT_ON_ERROR | JSON_PRETTY_PRINT;

    /**
     * @var \Throwable
     */
    protected $exception;

    /**
     * @var bool
     */
    protected $debugMode = false;

    /**
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
    protected function formatExceptionFragment(\Throwable $exception)
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
     * @return array
     */
    protected function formatTraceFragment()
    {
        return array_map(function ($trace) {
            unset($trace['args']);

            return $trace;
        }, $this->exception->getTrace());
    }
}
