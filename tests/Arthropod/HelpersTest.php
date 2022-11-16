<?php

use Swilen\Arthropod\Application;
use Swilen\Config\Repository;
use Swilen\Container\Container;
use Swilen\Http\Component\File\File;
use Swilen\Http\Request;
use Swilen\Http\Response;
use Swilen\Http\Response\BinaryFileResponse;
use Swilen\Http\Response\JsonResponse;
use Swilen\Http\Response\StreamedResponse;
use Swilen\Routing\Contract\ResponseFactory;
use Swilen\Shared\Arthropod\Application as ArthropodApplication;

uses()->group('Application', 'Helpers');

it('Application core helper app()', function () {
    $app = new Application();
    expect(app())->toBeInstanceOf(ArthropodApplication::class);
    expect(app())->toBeInstanceOf(Application::class);
    expect(app())->toBeInstanceOf(Container::class);
    expect(app('app'))->toBeInstanceOf(Application::class);

    expect(app(MakeClassAppHelperStub::class))->toBeInstanceOf(MakeClassAppHelperStub::class);

    $app->setInstance(null);
});

it('Response helper is instaceof Routing\ResponsiFactory', function () {
    $app = new Application();

    expect(response())->toBeInstanceOf(ResponseFactory::class);
    expect(response())->not->toHaveProperties(['getContent']);

    expect(response('content'))->toBeInstanceOf(Response::class);

    expect(response()->make())->toBeInstanceOf(Response::class);
    expect(response()->send())->toBeInstanceOf(JsonResponse::class);
    expect(response()->file(new File(getReadableFileStub())))->toBeInstanceOf(BinaryFileResponse::class);
    expect(response()->download(new File(getReadableFileStub())))->toBeInstanceOf(BinaryFileResponse::class);
    expect(response()->json())->toBeInstanceOf(JsonResponse::class);
    expect(response()->stream(function () {}))->toBeInstanceOf(StreamedResponse::class);

    $app[ResponseFactory::class] = new stdClass();

    expect(response())->toBeInstanceOf(stdClass::class);

    $app->setInstance(null);
});

it('Request helper is instaceof Http\Request', function () {
    $app = new Application();

    $app->instance('request', Request::create());

    expect(request())->toBeInstanceOf(Request::class);
    expect(request()->getMethod())->toBe('GET');
});

it('Work with application paths', function () {
    $app = new Application();

    $app->useBasePath(__DIR__);

    expect(base_path())->toBe(__DIR__);

    $app->useAppPath('test');

    expect(app_path())->toBe(__DIR__.DIRECTORY_SEPARATOR.'test');
    expect(storage_path())->toBe(__DIR__.DIRECTORY_SEPARATOR.'test'.DIRECTORY_SEPARATOR.'storage');
    expect(storage_path('test'))->toBe(__DIR__.DIRECTORY_SEPARATOR.'test'.DIRECTORY_SEPARATOR.'storage'.DIRECTORY_SEPARATOR.'test');

    $app->setInstance(null);
});

it('Work with application config', function () {
    $app = new Application();

    $app->instance('config', new Repository(['item' => 'test']));

    expect(config())->toBeInstanceOf(Repository::class);
    expect(config('item'))->toBe('test');
    expect(config('not-found', 'bar'))->toBe('bar');

    $app->setInstance(null);
});

it('env helper get environment variables', function () {
    expect(env('NULL'))->toBeNull();
    expect(env('REQUEST_METHOD', false))->toBeFalse();
});

it('Work with tap helper', function () {
    $object = new stdClass();

    $object->makeit = 'test';
    $value          = false;

    $result = tap($object, function ($object) use (&$value) {
        $value = $object->makeit;
    });

    expect($value)->toBe('test');
    expect($result)->toBeInstanceOf(stdClass::class);
});

it('Cannot redeclare helper functions', function () {
    if (!function_exists('app')) {
        function app()
        {
            return true;
        }
    }

    expect(app())->not->toBeTrue();

    if (!function_exists('tap')) {
        function tap()
        {
            return true;
        }
    }

    expect(tap('Hola', function () {}))->not->toBeTrue();
});

class MakeClassAppHelperStub
{
}
