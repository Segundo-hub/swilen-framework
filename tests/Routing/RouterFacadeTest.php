<?php

use Swilen\Container\Container;
use Swilen\Petiole\Facades\Facade;
use Swilen\Petiole\Facades\Route;
use Swilen\Routing\Route as RoutingRoute;
use Swilen\Routing\Router;

uses()->group('Routing');

beforeAll(function() {
    $app = Container::getInstance();

    $app->singleton('router', function ($app) {
        return new Router($app);
    });

    Facade::setFacadeApplication($app);
});

it('Facade Router registered succesfully', function () {

    $response = Route::get('/hola', function () {
        return ['hola' => 'Mundo'];
    });

    expect($response instanceof RoutingRoute)->toBeTrue();

});
