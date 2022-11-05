<?php

use Swilen\Arthropod\Application;
use Swilen\Arthropod\Env;
use Swilen\Container\Container;
use Swilen\Shared\Arthropod\Application as ArthropodApplication;
use Swilen\Http\Common\Http;
use Swilen\Http\Response;
use Swilen\Petiole\Facade;

uses()->group('Application');

beforeAll(function () {
    $app = new Application(dirname(__DIR__));

    $app->useEnvironmentPath(dirname(__DIR__));

    $app->singleton(
        \Swilen\Arthropod\Contract\ExceptionHandler::class,
        \Swilen\Arthropod\Exception\Handler::class
    );
});

beforeEach(function () {
    $this->app = Application::getInstance();
});

afterAll(function () {
    Env::forget();
    Application::getInstance()->flush();
    Facade::flushFacadeInstances();
});

it('The application started successfully and your instance is correct', function () {
    expect($this->app)->toBeInstanceOf(Application::class);
    expect($this->app)->toBeInstanceOf(ArthropodApplication::class);
    expect($this->app)->toBeInstanceOf(Container::class);
    expect($this->app)->toBeObject();
});

it('The app() helper is instance of Application', function () {
    expect(app())->toBeInstanceOf(Application::class);
    expect(app())->toBeInstanceOf(Container::class);
    expect(app('app'))->toBeInstanceOf(Application::class);
});

it('The app() helper works correctly', function () {

    $instance = app()->make(HelperTesting::class);

    expect($instance)->toBeInstanceOf(HelperTesting::class);
    expect($instance)->toBeObject();
    expect($instance->retrieve())->toBeInt();

    expect(app(HelperTesting::class))->toBeInstanceOf(HelperTesting::class);
});

it('Handle incoming request and return response', function () {

    $response = $this->app->handle(fetch('api/test'));

    expect($response)->toBeInstanceOf(Response::class);
    expect($response->getContent())->toBeJson();
    expect($response->statusCode())->toBe(Http::OK);
});

it('Handle incoming request', function () {

    $response = $this->app->handle(fetch('api/testing/test'));

    expect($response)->toBeInstanceOf(Response::class);
    expect($response->getContent())->toBeJson();
    expect($response->statusCode())->toBe(Http::FORBIDDEN);
});


final class HelperTesting
{
    public function retrieve()
    {
        return 10;
    }
}
