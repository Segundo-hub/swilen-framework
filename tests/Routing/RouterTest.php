<?php

use Swilen\Container\Container;
use Swilen\Http\Exception\HttpMethodNotAllowedException;
use Swilen\Http\Exception\HttpNotFoundException;
use Swilen\Http\Request;
use Swilen\Http\Response;
use Swilen\Routing\Router;
use Swilen\Security\Middleware\Authenticate;

uses()->group('Routing');

function newRequest(string $uri, string $method = 'GET', array $headers = [])
{
    $_SERVER = array_replace($_SERVER, [
        'REQUEST_URI' => $uri,
        'REQUEST_METHOD' => strtoupper($method)
    ]);

    return Request::create();
}

beforeEach(function (){
    $this->container = new Container();
    $this->router = new Router($this->container);
});

test('Match route current request', function ()
{
    $this->router->get('/test', function () {
        return 1;
    });

    $response = $this->router->dispatch(newRequest('/test'));

    expect($response instanceof Response)->toBeTrue();
    expect($response->getContent())->toBe('1');
});

test('Throw not found if route not matches', function ()
{
    $this->router->get('/test', function () {
        return 'Test Expect';
    });

    $this->router->dispatch(newRequest('/testing'));
})->throws(HttpNotFoundException::class, 'Not Found.');


test('Throw if current method not implement in routes collection', function ()
{
    $this->router->get('/test', function () {
        return 'Test Expect';
    });

    $this->router->dispatch(newRequest('/testing', 'POST'));
})->throws(HttpMethodNotAllowedException::class, 'Method Not Allowed.');

