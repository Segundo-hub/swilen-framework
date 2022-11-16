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
     * Logging time format.
     *
     * @var string
     */
    protected $timeFormat = 'Y-m-d H:i:s';

    /**
     * Logging timezone.
     *
     * @var \DateTimeZone
     */
    protected $timezone = \DateTimeZone::UTC;

    /**
     * Create new Psr logger instance.
     *
     * @param string        $directory
     * @param \DateTimeZone $timezone
     *
     * @return void
     */
    public function __construct(string $directory, \DateTimeZone $timezone = null)
    {
        $this->directory = $directory;
        $this->timezone  = $timezone ?: \DateTimeZone::UTC;
    }

    /**
     * {@inheritdoc}
     */
    public function log($level, $message, array $context = [])
    {
        $record = $this->newRecord($level, $message, $context);

        if ($path = $this->ensureLogFilePath()) {
            return \error_log($record, 3, $path);
        }

        \error_log($record);
    }

    /**
     * Determine if the log file path exists and add the date if true.
     *
     * @return string|void
     */
    protected function ensureLogFilePath()
    {
        $path = $this->directory.DIRECTORY_SEPARATOR.('swilen-'.$this->formatTime().'.log');

        if (file_exists($path) && is_writable($path)) {
            return $path;
        }

        if (@touch($path)) {
            return $path;
        }
    }

    /**
     * Create new log record.
     *
     * @param string|\Psr\Log\LogLevel::* $level
     * @param string                      $message
     *
     * @return string
     */
    protected function newRecord($level, $message, array $context)
    {
        $context = isset($context['exception']) ? $this->formatException($context['exception']) : '';

        $level = $this->determineContextLogging($level ?? LogLevel::WARNING);

        return sprintf('[%s] %s: %s.  %s'.PHP_EOL, $this->formatTime(null), $level, (string) $message, $context);
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
        return 'swilen.['.strtoupper($level).']';
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

    /**
     * Create date with format and timezone.
     *
     * @param string|null $custom Pass custom date format, use default if is null
     *
     * @return string
     */
    protected function formatTime($custom = 'Y-m-d')
    {
        return (new \DateTime('now', $this->timezone))->format($custom ?? $this->timeFormat);
    }
}
