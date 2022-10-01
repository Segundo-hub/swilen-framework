<?php

namespace Swilen\Console;

abstract class SwilenCommand
{
    /**
     * @var \Swilen\Console\Application
     */
    protected $Swilen;

    public function __construct(Application $console)
    {
        $this->Swilen = $console;
    }

    public function getCommand()
    {
        return $this->command;
    }
}
