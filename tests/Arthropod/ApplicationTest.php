<?php

use Swilen\Arthropod\Application;
use Swilen\Arthropod\Env;
use Swilen\Container\Container;
use Swilen\Petiole\Facade;
use Swilen\Shared\Arthropod\Application as ArthropodApplication;

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
    /**
     * @var \PHPUnit\Framework\TestCase $this
     */
    $app = Application::getInstance();

    $this->app = $app;
});

afterAll(function () {
    Env::forget();
    Application::getInstance()->flush();
    Facade::flushFacadeInstances();
});

it('The application started successfully and your instance is correct', function () {
    /**
     * @var Swilen\Arthropod\Application
     */
    $app = $this->app;

    expect($app)->toBeInstanceOf(Application::class);
    expect($app)->toBeInstanceOf(ArthropodApplication::class);
    expect($app)->toBeInstanceOf(Container::class);
    expect($app)->toBeObject();
    expect($app->hasBeenBootstrapped())->toBeFalse();
    expect($app->isBooted())->toBeFalse();
});

it('Interact with application paths and files', function () {
    $directory = dirname(__DIR__);

    $app = new Application($directory);

    expect($app->environmentFile())->toBe('.env');

    $app->useEnvironmentFile('.env.example');
    expect($app->environmentFile())->toBe('.env.example');

    expect($app->environmentPath())->toBe($directory);
    $app->useEnvironmentPath('/test');
    expect($app->environmentPath())->toBe('/test');
});

it('Application is developent mode', function () {
    $app = new Application(dirname(__DIR__));

    expect($app->isDevelopmentMode())->toBeFalse();

    $app->useEnvironment('development');

    expect($app->isDevelopmentMode())->toBeTrue();
});

it('Flush application instance', function () {
    $app = new Application(dirname(__DIR__));

    expect(count($app->bindings()))->toBeGreaterThanOrEqual(2);

    $app->flush();

    expect($app->bindings())->toBeEmpty();

    unset($app);
});

final class HelperTesting
{
    public function retrieve()
    {
        return 10;
    }
}
