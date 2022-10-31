<?php

namespace Swilen\Shared\Arthropod;

use Swilen\Shared\Container\Container;

interface Application extends Container
{
    public function basePath(string $path = '');

    public function appPath(string $path = '');

    public function appUri(string $path = '');
}
