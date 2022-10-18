<?php

use Swilen\Arthropod\Application;
use Swilen\Container\Container;
use Swilen\Contracts\Arthropod\Application as ArthropodApplication;
use Swilen\Http\Common\Http;
use Swilen\Http\Response;

uses()->group('Application');

beforeAll(function () {
    $app = new Application(dirname(__DIR__));

    $app->useEnviromentPath(dirname(__DIR__));
});

beforeEach(function () {
    $this->app = Application::getInstance();
});


it('Application started successfully', function () {
    expect($this->app)->toBeInstanceOf(Application::class);
    expect($this->app)->toBeInstanceOf(ArthropodApplication::class);
    expect($this->app)->toBeInstanceOf(Container::class);
});

it('The app() helper works correctly', function () {

    $instance = app()->make(HelperTesting::class);

    expect($instance)->toBeInstanceOf(HelperTesting::class);
    expect($instance)->toBeObject();
    expect($instance->retrieve())->toBeInt();

    $other = app(HelperTesting::class);

    expect($other)->toBeInstanceOf(HelperTesting::class);
});

it('Handle incoming request and return response', function () {

    $response = $this->app->dispatch(fetch('api/test'));

    expect($response)->toBeInstanceOf(Response::class);
    expect($response->getContent())->toBeJson();
    expect($response->statusCode())->toBe(Http::OK);
});

it('Handle incoming request', function () {

    $response = $this->app->dispatch(fetch('api/testing/test'));

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
