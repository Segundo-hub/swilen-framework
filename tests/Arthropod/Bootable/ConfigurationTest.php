<?php

use Swilen\Arthropod\Application;
use Swilen\Arthropod\Bootable\Configuration;
use Swilen\Arthropod\Env;
use Swilen\Config\Repository as ConfigRepository;

uses()->group('Application');

afterEach(function () {
    Mockery::close();
    Env::forget();
});

it('Load configuration file', function () {
    $app = new Application();

    $app->instance('path.config', __DIR__.'/../__fixtures__/config.test.php');

    (new Configuration())->puriyboot($app);

    expect($app->make('config'))->toBeInstanceOf(ConfigRepository::class);
    expect(mb_internal_encoding())->toBe('UTF-8');
    expect(date_default_timezone_get())->toBe('UTC');

    $app->setInstance(null);
});

it('Throw error when configuration file not exists', function () {
    $app = new Application();

    $app->instance('path.config', __DIR__.'/../__fixtures__/not-found.php');

    (new Configuration())->puriyboot($app);
})->throws(InvalidArgumentException::class);

it('Throw error when configuration env is not acceptable', function () {
    $app = new Application();

    $app->instance('path.config', __DIR__.'/../__fixtures__/config_invalid_env.test.php');

    (new Configuration())->puriyboot($app);
})->throws(LogicException::class);
