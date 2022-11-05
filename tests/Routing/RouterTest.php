<?php

use Swilen\Container\Container;
use Swilen\Http\Exception\HttpForbiddenException;
use Swilen\Http\Exception\HttpMethodNotAllowedException;
use Swilen\Http\Exception\HttpNotFoundException;
use Swilen\Http\Request;
use Swilen\Http\Response;
use Swilen\Routing\Router;
use Swilen\Security\Middleware\Authenticate;

uses()->group('Routing');

beforeEach(function () {
    $this->container = new Container();
    $this->router = new Router($this->container);
});

it('Match route current request', function () {
    $this->router->get('/test', function () {
        return ['slwien' => 'test'];
    });

    /** @var Response */
    $response = $this->router->dispatch(fetch('/test'));

    expect($response)->toBeInstanceOf(Response::class);
    expect($response->getContent())->toBeJson();
});

it('Throw not found if route not matches', function () {
    $this->router->get('/test', function () {
        return 'Testing Not Found';
    });

    $this->router->dispatch(fetch('/testing'));
})->throws(HttpNotFoundException::class, 'Not Found.');

it('Throw if current method not implement in routes collection', function () {
    $this->router->get('/test', function () {
        return 'Test Expect';
    });

    $this->router->dispatch(fetch('/testing', 'POST'));
})->throws(HttpMethodNotAllowedException::class, 'Method Not Allowed.');

it('Routing register shared middleware and return throw if bearer token not found in header', function () {
    $this->router->prefix('users')->use(Authenticate::class)->group(function () {
        $this->router->get('test', function () {
            return 1;
        })->name('user-test');
    });

    $this->router->dispatch(fetch('/users/test'));
})->throws(HttpForbiddenException::class, 'Forbidden');

it('Routing register shared middleware and return throw if bearer token found', function () {
    $this->router->prefix('users')->use(Authenticate::class)->group(function () {
        $this->router->get('test', function () {
            return 1;
        })->name('user-test');
    });

    $this->router->dispatch(fetch('/users/test', 'GET', [
        'Authorization' => '',
    ]));
})->throws(HttpForbiddenException::class, 'Forbidden');

it('Route attributes as registered', function () {
    /** @var \Swilen\Routing\Route */
    $route = $this->router->get('/hello/{world}', function () {
        return 5;
    })->name('api.hello')->use(function (Request $request, Closure $next) {
        $response = $next($request);

        $response->withHeader('Fo', 'bar');

        return $response;
    });

    expect($route->getPattern())->toBe('/hello/{world}');
    expect($route->getName())->toBe('api.hello');
    expect($route->getMiddleware())->toBeArray();
    expect($route->getAction('uses'))->toBeCallable();
    expect($route->getAction())->not->toHaveKey('controller');

    $response = $this->router->dispatch(fetch('/hello/lima'));

    expect($response->getContent())->toBeNumeric();
    expect($response->headers->get('Fo'))->toBe('bar');

    expect($route->parameter('world'))->toBe('lima');
    expect($route->getParameters())->not->toBeEmpty();
});

it('Shared route attributes registered', function () {
    /* @var \Swilen\Routing\Route */

    $this->router->use(function (Request $request, Closure $next) {
        $response = $next($request);

        $response->withHeader('Use-Token', 'true');

        return $response;
    })->prefix('name')->group(function () {
        $this->router->get('/route/{match}', function () {
            return ['hi!'];
        });
    });

    $response = $this->router->dispatch(fetch('/name/route/cuzco'));

    expect($response->getContent())->toBeJson();
    expect($response->headers->get('Use-Token'))->toBe('true');

    /** @var \Swilen\Routing\Route */
    $route = $this->router->current();

    expect($route->getPattern())->toBe('/name/route/{match}');
    expect($route->getName())->toBeNull();
    expect($route->getMiddleware())->toBeArray();
    expect($route->getAction('uses'))->toBeCallable();
    expect($route->getAction())->not->toHaveKey('controller');
    expect($route->parameter('match'))->toBe('cuzco');
});
