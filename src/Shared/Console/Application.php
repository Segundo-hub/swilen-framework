<?php

namespace Swilen\Shared\Console;

use Swilen\Shared\Container\Container;

interface Application extends Container
{
    /**
     * Console application exit codes
     *
     * @var int
     */
    public const SUCCESS = 0, WARNING = 1, ERROR = 2, FATAL_ERROR = 3;

    public function basePath(string $path = '');

    public function appPath(string $path = '');
}
