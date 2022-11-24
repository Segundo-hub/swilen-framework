<?php

namespace Swilen\Shared\Arthropod;

use Swilen\Console\Input\ArgvInput;

interface ConsoleApplication
{
    /**
     * Handle incoming command and dispatch to your controller.
     *
     * @param \Swilen\Console\Input\ArgvInput $input
     *
     * @return $this
     */
    public function handle(ArgvInput $input);
}
