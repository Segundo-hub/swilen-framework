<?php
use Swilen\Arthropod\Application;
use Swilen\Http\Request;
use Swilen\Security\Middleware\Authenticate;

/**  @var \Swilen\Routing\Router */
$router = Application::getInstance()->make('router');

$router->get('testa', function (Request $request) {
    return ['Swilen' => 'TESTING'];
});

$router->get('testf', function (Request $request) {
    return ['Swilen' => 'TESTING'];
});

$router->get('found-error', function (Request $request) {
    trigger_error(
        'custom error'
    );
});

$router->get('test', function (Request $request) {
    return ['Swilen' => 'TESTING'];
});

$router->group([
    'prefix' => 'testing',
    'middleware' => Authenticate::class
], function () use ($router) {
    $router->get('/test', fn () => 'Hola Mundo');
});
