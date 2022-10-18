<?php

namespace Swilen\Console;

abstract class SwilenCommand
{
    /**
     * Swilen console application instance
     *
     * @var \Swilen\Console\Application
     */
    protected $app;

    /**
     * Create new SwilenCommand instance for abstracting app injection and getCommand method
     *
     * @param \Swilen\Console\Application $app
     *
     * @return void
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Get current command
     *
     * @return string
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * Get command option
     *
     * @param string $option
     *
     * @return string|bool|null
     */
    protected function inputOption(string $option)
    {
        return $this->app->input()->getFlag($option);
    }
}
