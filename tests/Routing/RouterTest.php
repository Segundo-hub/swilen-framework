<?php

use Swilen\Container\Container;
use Swilen\Http\Exception\HttpForbiddenException;
use Swilen\Http\Exception\HttpMethodNotAllowedException;
use Swilen\Http\Exception\HttpNotFoundException;
use Swilen\Http\Exception\HttpUnauthorizedException;
use Swilen\Http\Request;
use Swilen\Http\Response;
use Swilen\Routing\Router;
use Swilen\Security\Middleware\Authenticate;

uses()->group('Routing');

function newRequest(string $uri, string $method = 'GET', array $headers = [])
{
    $_SERVER = array_replace($_SERVER, array_merge([
        'REQUEST_URI' => $uri,
        'REQUEST_METHOD' => strtoupper($method)
    ], $headers));

    return Request::create();
}

beforeEach(function () {
    $this->container = new Container();
    $this->router = new Router($this->container);
});

it('Match route current request', function ($dataset) {
    $this->router->get('/test', function () use ($dataset) {
        return $dataset;
    });

    $response = $this->router->dispatch(newRequest('/test'));

    expect($response)->toBeInstanceOf(Response::class);
    expect($response->getContent())->toBeJson()->toBe(json_encode($dataset));
})->with([
    'dataset' => 'test'
]);

it('Throw not found if route not matches', function () {
    $this->router->get('/test', function () {
        return 'Testing Found';
    });

    $this->router->dispatch(newRequest('/testing'));
})->throws(HttpNotFoundException::class, 'Not Found.');


it('Throw if current method not implement in routes collection', function () {
    $this->router->get('/test', function () {
        return 'Test Expect';
    });

    $this->router->dispatch(newRequest('/testing', 'POST'));
})->throws(HttpMethodNotAllowedException::class, 'Method Not Allowed.');

it('Routing register shared middleware and return throw if bearer token not found in header', function () {

    $this->router->prefix('users')->use(Authenticate::class)->group(function () {
        $this->router->get('test', function () {
            return 1;
        })->name('user-test');
    });

    $this->router->dispatch(newRequest('/users/test'));
})->throws(HttpForbiddenException::class, 'Forbidden');

it('Routing register shared middleware and return throw if bearer token found', function () {

    $this->router->prefix('users')->use(Authenticate::class)->group(function () {
        $this->router->get('test', function () {
            return 1;
        })->name('user-test');
    });

    $this->router->dispatch(newRequest('/users/test', 'GET', [
        'Authorization' => ''
    ]));
})->throws(HttpForbiddenException::class, 'Forbidden');

