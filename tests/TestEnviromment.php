<?php

use Swilen\Arthropod\Env;

uses()->group('enviroment');

function enviroment(string $key, $default = null)
{
    return Env::get($key, $default);
}

beforeAll(function () {
    (new Env())->createFrom(dirname(__FILE__))->config([
        'file' => '.env'
    ])->load();
});


it('The dotenv list is expected to be registered as an array', function () {
    expect(Env::registered())->toBeArray();
});

it('The variable is expected to return null if it is not found in the file', function () {
    expect(enviroment('TEST_NULL_ENV'))->toBeNull();
});

it('Expect a empty string if not found env variable in file', function () {
    expect(enviroment('TEST_EMPTY_ENV', ''))->toBeEmpty();
});

it('Expect if not found varaiable return default', function () {
    expect(enviroment('TEST_ENV', '__default'))->toBe('__default');
});

it('Found a env variable', function () {
    expect(enviroment('BASE_URL'))->toBe('http://localhost:8080');
});

it('Replace variable in env var has found', function () {
    expect(enviroment('EXTEND_URL'))->toBe('http://localhost:8080/api');
});

it('Replace All variables founded', function () {
    expect(enviroment('NESTED_URI'))->toBe('http://localhost:8080/api/v1/testing');
});
