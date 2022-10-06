# Swilen
`0.0.2-dev`

PHP library inspired in laravel and expressjs

## Application Container

Dependency Injection Container

#### `Container::make(string $service, array $parameters = [])`

```PHP 
<?php

use Swilen\Container\Container;

$repository = Container::getInstance()->make(UserRepository::class);
// or
$repository = app()->make(UserRepository::class);

```

#### `Container::get(string $id)`

```PHP 
<?php

use Swilen\Container\Container;

$repository = Container::getInstance()->get(UserRepository::class);
// or
$repository = app()->get(UserRepository::class);

```


