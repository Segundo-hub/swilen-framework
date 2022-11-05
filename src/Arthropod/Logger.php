<?php

namespace Swilen\Arthropod;

use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;

class Logger extends AbstractLogger
{
    /**
     * Local directory path for write logs.
     *
     * @var string
     */
    protected $directory;

    /**
     * Log time format.
     *
     * @var string
     */
    protected $timeFormat = 'Y-m-d H:i:s';

    /**
     * Create new Psr logger instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->directory = storage_path('logs');
    }

    /**
     * {@inheritdoc}
     */
    public function log($level, $message, array $context = [])
    {
        $record = $this->newRecord($level, $message, $context);

        if ($filename = $this->ensureLogFilename()) {
            \error_log($record, 3, $filename);

            return;
        }

        \error_log($record);
    }

    /**
     * Determine log filename append date.
     *
     * @return string|void
     */
    protected function ensureLogFilename()
    {
        $filename = $this->directory.DIRECTORY_SEPARATOR.('swilen-'.date('Y-m-d').'.log');

        if (file_exists($filename) && is_writable($filename)) {
            return $filename;
        }

        if (@touch($filename)) {
            return $filename;
        }
    }

    /**
     * Create new log record.
     *
     * @param string|LogLevel::*|null $level
     * @param string                  $message
     *
     * @return string
     */
    protected function newRecord($level, $message, array $context)
    {
        $datetime = (new \DateTime('now'))->format($this->timeFormat);
        $context = isset($context['exception']) ? $this->formatException($context['exception']) : '';
        $level = $this->determineContextLogging($level ?? LogLevel::WARNING);

        return sprintf('[%s] %s: %s.  %s'.PHP_EOL, $datetime, $level, (string) $message, $context);
    }

    /**
     * Determine context logging.
     *
     * @param string|LogLevel::*|null $level
     *
     * @return string
     */
    private function determineContextLogging($level)
    {
        return 'local.['.strtoupper($level).']';
    }

    /**
     * Format exception for write to log file.
     *
     * @param \Throwable $e
     *
     * @return string
     */
    private function formatException(\Throwable $e)
    {
        $formatted = '"[object] ('.get_class($e).'(code: '.$e->getCode().')": '.
            $e->getMessage().' at '.$e->getFile().':'.$e->getLine().')';

        return $formatted .= PHP_EOL.'[stacktrace]'.PHP_EOL.$e->getTraceAsString().PHP_EOL;
    }
}
