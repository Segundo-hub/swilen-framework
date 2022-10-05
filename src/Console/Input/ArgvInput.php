<?php

namespace Swilen\Console\Input;

use Swilen\Contracts\Support\Jsonable;

final class ArgvInput implements Jsonable
{
    /**
     * @var array
     */
    protected $tokens = [];

    /**
     * @var string
     */
    protected $command;

    /**
     * @var array
     */
    protected $params = [];

    /**
     * @var array
     */
    protected $flags = [];

    /**
     * Create new ArgvInput instance and capture cmd arguments
     *
     * @return void
     */
    public function __construct()
    {
        $this->tokens = $this->captureRawInputArgv();

        $this->transformInputArguments();
    }

    /**
     * Capture raw cmd arguments
     *
     * @return array
     */
    private function captureRawInputArgv()
    {
        $argv = $argv ?? $_SERVER['argv'] ?? [];

        array_shift($argv);

        return $argv;
    }

    /**
     * Capture and parse command arguments
     *
     * @return void
     */
    protected function transformInputArguments()
    {
        $this->command = $this->tokens[0] ?? null;

        $this->parseInput();
    }

    /**
     * Parse input arguments, flags and option
     *
     * @return void
     */
    public function parseInput()
    {
        foreach ($this->getRawOptions() as $option) {
            if ($this->startWith($option, '--') || $this->startWith($option, '-')) {
                $this->flags[$option] = true;
            }

            if (strpos($option, '=') !== false && preg_match('#^[a-zA-Z]+#', $option) === 1) {
                [$key, $value] = $this->explodeOption($option);
                $this->params[$key] = $value;
            }
        }
    }

    /**
     * Explode option and return { key: value }
     *
     * @param string $option
     *
     * @return array
     */
    protected function explodeOption($option)
    {
        [$key, $value] = explode('=', $option);

        if ($value === '' || $value === "") {
            throw new \InvalidArgumentException(sprintf(' Missing option "%s" need a value', $key), 2);
        }

        return [$key, $value];
    }

    /**
     * Get raw options omit the command signature
     *
     * @return array
     */
    protected function getRawOptions()
    {
        $options = $this->tokens;

        array_shift($options);

        return $options;
    }

    public function getCommand()
    {
        return $this->command;
    }

    public function getCommands()
    {
        return $this->tokens;
    }

    public function getFlags()
    {
        return $this->flags;
    }

    public function hasFlag(string $flag)
    {
        return key_exists($flag, $this->flags);
    }

    public function getFlag(string $flag)
    {
        return $this->flags[$flag] ?? null;
    }

    /**
     * Check string starts with
     *
     * @param string $haystack
     * @param array|string $needle
     *
     * @return bool
     */
    private function startWith(string $haystack, string $needle)
    {
        return \strpos($haystack, $needle) === 0;
    }

    /**
     * {@inheritdoc}
     */
    public function toJson($options = 0)
    {
        return json_encode([
            'command' => $this->getCommand(),
            'params' => $this->params,
            'tokens' => $this->getCommands(),
            'flags' => $this->flags,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}
