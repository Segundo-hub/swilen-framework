<?php

use Swilen\Container\Container;
use Swilen\Petiole\Facades\Facade;
use Swilen\Petiole\Facades\Route;
use Swilen\Routing\Route as RoutingRoute;
use Swilen\Routing\Router;

uses()->group('Routing');

it('Facade Router registered succesfully', function () {
    $app = Container::getInstance();

    $app->singleton('router', function ($app) {
        return new Router($app);
    });

    Facade::setFacadeApplication($app);

    $response = Route::get('/hola', function () {
        return ['hola' => 'Mundo'];
    });

    expect($response instanceof RoutingRoute)->toBeTrue();

});
