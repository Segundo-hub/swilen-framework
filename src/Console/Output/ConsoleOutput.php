<?php

namespace Swilen\Console\Output;

use Swilen\Contracts\Support\Printable;

final class ConsoleOutput implements Printable
{
    /**
     * @var string
     */
    protected $consoleOutput;

    /**
     * @var string
     */
    protected $foreground;

    /**
     * @var string
     */
    protected $background;

    /**
     * @var \Swilen\Console\Output\StreamOutput
     */
    protected $streamOutput;

    public function __construct()
    {
        $this->streamOutput = new StreamOutput();
    }

    public function prepare(string $output = null, string $fore = 'cyan', string $background = null)
    {
        $this->consoleOutput = $output;
        $this->foreground = $fore;
        $this->background = $background;

        return $this;
    }

    public function print()
    {
        $this->streamOutput->write($this->formatOutput());
    }

    public function formatOutput()
    {
        $colorized = '';

        if (!is_null($this->foreground) && key_exists($this->foreground, Color::FOREGROUND_COLORS)) {
            $colorized .= "\033[" . Color::FOREGROUND_COLORS[$this->foreground] . "m";
        }

        if (!is_null($this->background) && key_exists($this->background, Color::BACKGROUND_COLORS)) {
            $colorized .= "\033[" . Color::BACKGROUND_COLORS[$this->background] . "m";
        }

        $colorized .= $this->consoleOutput() . "\033[0m" . PHP_EOL;

        return $colorized;
    }

    protected function consoleOutput()
    {
        return $this->consoleOutput ?? 'ARGUMENTS NOT FOUND';
    }

    /**
     * Get elapsed time of command execution
     *
     * @return string
     */
    protected function getElapsedTime()
    {
        define('SWILEN_CMD_END', microtime(true));

        return str_pad(sprintf("Execution time: %.5f seconds.", SWILEN_CMD_END - SWILEN_CMD_START), 80, ' ', STR_PAD_RIGHT);
    }

    /**
     * Get memory usage of command
     *
     * @return string
     */
    protected function getMemoryUsage()
    {
        return str_pad(sprintf("Memory usage: %s KB.",  round(memory_get_usage(true) / 1024)), 80, ' ', STR_PAD_RIGHT);
    }

    /**
     * Termina command and print banner with system resource usage
     *
     * @return void
     */
    public function terminateSwilenScript()
    {
        $this->streamOutput->writeln(
            PHP_EOL . "\033[" . Color::FOREGROUND_COLORS['green'] . "m" .
                " =================================================================================== " . PHP_EOL .
                " | " . $this->getElapsedTime() . "| " . PHP_EOL .
                " | " . $this->getMemoryUsage() . "| " . PHP_EOL .
                " =================================================================================== \033[0m" . PHP_EOL
        );
    }
}
