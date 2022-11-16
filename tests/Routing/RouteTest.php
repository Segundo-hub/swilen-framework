<?php

use Swilen\Http\Common\Http;
use Swilen\Routing\Exception\InvalidRouteHandlerException;
use Swilen\Routing\Route;

uses()->group('Routing');

it('Route instanced succesfuly', function () {
    $route = new Route(Http::METHOD_GET, '/hola', function () {
        return 5;
    });

    expect($route->getMethod())->toBe(Http::METHOD_GET);
    expect($route->getPattern())->toBe('/hola');
    expect($route->getAction('uses'))->toBeCallable();
    expect($route->run())->toBe(5);
    expect($route->getParameters())->toBeEmpty();
});

it('Route handler is invalid', function ($action) {
    $route = new Route(Http::METHOD_GET, 'test', $action);

    $route->run();
})->with([
    'controller_string' => 'controller@invoke',
    'controller_array' => [['controller\\class', 'invoke']],
])->throws(InvalidRouteHandlerException::class);

it('Run route action is controller', function ($action) {
    $route = new Route(Http::METHOD_GET, 'test', $action);

    expect($route->run())->toBe(5);
})->with([
    'controller_string' => ControllerTestStub::class.'@method',
    'controller_array' => [[ControllerTestStub::class, 'method']],
]);

it('Run route action is callable or invocable', function ($action) {
    $route = new Route(Http::METHOD_GET, 'test', $action);

    expect($route->run())->toBe(5);
})->with([
    'closure' => function () {
        return function () {
            return 5;
        };
    },
    'invocable' => InvocableTestStub::class,
]);

it('Compile parameters matched', function () {
    $route = new Route('GET', 'test/{home}', function ($home) {
        return $home;
    });

    expect($route->matches('test/lima'))->toBeTrue();
    expect($route->matches('test/25'))->toBeTrue();

    $route->matches('test/cuzco');

    expect($route->run())->toBe('cuzco');
});

it('Compile parameters matched with data-type', function () {
    $route = new Route('GET', 'test/{string:home}', function ($home) {
        return $home;
    });

    expect($route->matches('test/lima'))->toBeTrue();
    expect($route->matches('test/25'))->toBeTrue();

    $route->matches('test/machu-picchu');

    expect($route->run())->toBe('machu-picchu');

    $route = new Route('GET', 'person/{int:age}', function (int $age) {
        return $age;
    });

    expect($route->matches('person/lima'))->toBeFalse();
    expect($route->matches('person/25'))->toBeTrue();

    $route->matches('person/25');

    expect($route->run())->toBeInt();
});

it('Correct resolve url encoded', function () {
    $route = new Route('GET', 'test/{url}', function ($url) {
        return $url;
    });

    expect($route->matches('test/Cuzco%2C%20Peru'))->toBeTrue();

    expect($route->run())->toBe('Cuzco, Peru');
});

it('Resolve multiples parameter names', function () {
    $route = new Route('GET', 'named/{uri}/{other}/{domain}', function () {
        return null;
    });

    expect($route->matches('named/my-uri/blog/google'))->toBeTrue();

    expect($route->run())->toBeNull();
});

class InvocableTestStub
{
    public function __invoke()
    {
        return 5;
    }
}

class ControllerTestStub
{
    public function method()
    {
        return 5;
    }
}
