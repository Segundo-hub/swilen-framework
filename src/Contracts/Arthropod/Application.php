<?php

namespace Swilen\Contracts\Arthropod;

use Swilen\Contracts\Container\Container;

interface Application extends Container
{
    public function basePath(string $path = '');

    public function appPath(string $path = '');

    public function appUri(string $path = '');
}
