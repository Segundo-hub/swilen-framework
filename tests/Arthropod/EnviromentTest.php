<?php

use Swilen\Arthropod\Env;

uses()->group('enviroment');

beforeAll(function () {
    (new Env())->createFrom(dirname(__DIR__))->config([
        'file' => '.env'
    ])->load();
});


it('The dotenv list is expected to be registered as an array', function () {
    expect(Env::registered())->toBeArray();
});

it('The variable is expected to return null if it is not found in the file', function () {
    expect(env('TEST_NULL_ENV'))->toBeNull();
});

it('Expect a empty string if not found env variable in file', function () {
    expect(env('TEST_EMPTY_ENV', ''))->toBeEmpty();
});

it('Expect if not found varaiable return default', function () {
    expect(env('TEST_ENV', '__default'))->toBe('__default');
});

it('Found a env variable', function () {
    expect(env('BASE_URL'))->toBe('http://localhost:8080');
});

it('Replace variable in env var has found', function () {
    expect(env('EXTEND_URL'))->toBe('http://localhost:8080/api');
});

it('Replace All variables founded', function () {
    expect(env('NESTED_URI'))->toBe('http://localhost:8080/api/v1/testing');
});
