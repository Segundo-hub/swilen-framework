<?php

namespace Swilen\Console\Output;

class StreamOutput
{
    /**
     * @var resource
     */
    protected $stream;

    public function __construct()
    {
        $this->stream = $this->openOutputStream();
    }

    public function writeln($messages)
    {
        $this->write($messages, true);
    }

    public function write($messages, bool $newLine = false)
    {
        if (!is_iterable($messages)) {
            $messages = [$messages];
        }

        foreach ($messages as $message) {
            $this->doWrite($message ?? '', $newLine);
        }
    }

    protected function doWrite(string $message, bool $newline)
    {
        if ($newline) {
            $message .= \PHP_EOL;
        }

        @fwrite($this->stream, $message);

        fflush($this->stream);
    }

    protected function hasStdoutSupport()
    {
        return false === $this->isRunningOS400();
    }

    /**
     * Returns true if current environment supports writing console output to
     * STDERR.
     *
     * @return bool
     */
    protected function hasStderrSupport()
    {
        return false === $this->isRunningOS400();
    }

    /**
     * Checks if current executing environment is IBM iSeries (OS400), which
     * doesn't properly convert character-encodings between ASCII to EBCDIC.
     */
    private function isRunningOS400()
    {
        $checks = [
            \function_exists('php_uname') ? \php_uname('s') : '',
            \getenv('OSTYPE'),
            \PHP_OS,
        ];

        return false !== stripos(implode(';', $checks), 'OS400');
    }

    /**
     * @return resource
     */
    private function openOutputStream()
    {
        if (!$this->hasStdoutSupport()) {
            return fopen('php://output', 'w');
        }

        // Use STDOUT when possible to prevent from opening too many file descriptors
        return \defined('STDOUT') ? \STDOUT : (@fopen('php://stdout', 'w') ?: fopen('php://output', 'w'));
    }
}
