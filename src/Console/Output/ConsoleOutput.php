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

        $colorized .= $this->terminateScriptWithTime($colorized);

        return $colorized;
    }

    protected function consoleOutput()
    {
        return $this->consoleOutput ?? 'ARGUMENTS NOT FOUND';
    }

    protected function getElapsedTime()
    {
        return str_pad(sprintf("Execution time: %.5f seconds.", microtime(true) - Swilen_CMD_START), 80, ' ', STR_PAD_RIGHT);
    }

    protected function getMemoryUsage()
    {
        $memory = memory_get_usage(true);
        return str_pad(sprintf("Memory usage: %s KB.",  round($memory / 1024)), 80, ' ', STR_PAD_RIGHT);
    }

    private function terminateScriptWithTime(string $prepend)
    {
        $prepend .= $this->consoleOutput() . "\033[0m" . PHP_EOL;

        return $prepend;
    }

    public function printExecutionTime()
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
