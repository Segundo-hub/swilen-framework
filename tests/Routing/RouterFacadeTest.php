<?php

use Swilen\Container\Container;
use Swilen\Petiole\Facade;
use Swilen\Petiole\Facades\Route;
use Swilen\Routing\Route as RoutingRoute;
use Swilen\Routing\Router;
use Swilen\Security\Middleware\Authenticate;

uses()->group('Routing');

beforeAll(function () {
    $app = Container::getInstance();

    $app->singleton('router', function ($app) {
        return new Router($app);
    });

    Facade::setFacadeApplication($app);
});

afterAll(function () {
    Container::getInstance()->flush();
});

it('Router Facade registered succesfully', function () {
    $route = Route::get('/hola', function () {
        return ['hola' => 'Mundo'];
    })->name('test-hola')->use(Authenticate::class);

    expect($route)->toBeInstanceOf(RoutingRoute::class);
    expect($route->getName())->toBe('test-hola');
    expect($route->middlewares())->toBe([Authenticate::class]);
    expect($route->getMethod())->toBe('GET');
});
