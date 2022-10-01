<?php

namespace Swilen\Console\Input;

class ArgvInput
{
    /**
     * @var array
     */
    protected $tokens = [];

    /**
     * @var array
     */
    protected $commands = [];

    /**
     * @var array
     */
    protected $flags = [];

    protected $currentCommand = null;

    protected $currentCommandValue = null;

    public function __construct()
    {
        $this->captureInputArgv();
    }

    protected function captureInputArgv()
    {
        $this->tokens = $this->captureRawInputArgv();
        $this->currentCommand = $this->tokens[0] ?? null;

        foreach ($this->tokens as $command) {
            if (strpos($command, '=') !== false && substr($command, 0, 2) == '--') {
                [$key, $value] = explode('=', $command);
                if (!$value) {
                    throw new \InvalidArgumentException(sprintf('The "%s" espected a valid value', $key), 1);
                }
                $this->flags[$key] = $value;
            }

            if (strpos($command, '=') !== false && preg_match('#^[a-zA-Z]+#', $command) === 1) {
                [$key, $value] = explode('=', $command);
                $this->commands[$key] = $value;
            }
        }
    }

    /**
     * @return array
     */
    private function captureRawInputArgv()
    {
        $argv = $argv ?? $_SERVER['argv'] ?? [];
        array_shift($argv);

        return $argv;
    }

    public function getCommand()
    {
        return $this->currentCommand;
    }

    public function getCommands()
    {
        return $this->tokens;
    }

    public function toJson()
    {
        return json_encode([
            'commad' => $this->getCommand(),
            'sub-commands' => $this->commands,
            'flags' => $this->flags,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    public function getFlags()
    {
        return $this->flags;
    }
}
