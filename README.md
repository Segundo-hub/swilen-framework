# Swilen

`v0.8.0`

PHP library inspired in laravel and expressjs

## Components

[1 Application](#application)
2 Container
3 Database
4 Http
5 Facades(Petiole)
6 Routing
7 Security
8 Validation
9 Mail :waxing_crescent_moon:
10 Console :waxing_crescent_moon:

## Application

`<project-name>/public/index.php`

```PHP
<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Create application instance
$app = new \Swilen\Arthropod\Application(dirname(__DIR__));

// Create request instance for capture server variables
$request = \Swilen\Request::create();

// Handle incoming HTTP request
$response = $app->handle($request);

// Terminate application
$response->terminate();

```

## Application Service Container

Dependency Injection Container

`Container::make(string $service, array $parameters = [])`

```PHP
<?php

use Swilen\Container\Container;

$repository = Container::getInstance()->make(UserRepository::class);
// or
$repository = app()->make(UserRepository::class);

```

`Container::get(string $id)`

```PHP
<?php

use Swilen\Container\Container;

$repository = Container::getInstance()->get(UserRepository::class);
// or
$repository = app()->get(UserRepository::class);

```
