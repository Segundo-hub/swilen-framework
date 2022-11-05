<?php

use Swilen\Arthropod\Env;

uses()->group('Enviroment');

beforeAll(function () {
    define('ENVIROMENT_START', hrtime(true));

    Env::createFrom(dirname(__DIR__))->config([
        'file' => '.env',
    ])->load();

    define('ENVIROMENT_LOADED', hrtime(true));
});

beforeEach(function () {
    $this->env = Env::getInstance();
});

afterAll(function () {
    Env::forget();
});

it('The variable is expected to return null if it is not found in the file', function () {
    expect($this->env::get('TEST_NULL_ENV'))->toBeNull();
});

it('Expect a empty string if not found env variable in file', function () {
    expect($this->env::get('TEST_EMPTY_ENV', ''))->toBeEmpty();
});

it('Expect if not found varaiable return default', function () {
    expect($this->env::get('TEST_ENV', '__default'))->toBe('__default');
});

it('Found a env variable', function () {
    expect($this->env::get('BASE_URL'))->toBe('http://localhost:8080');
});

it('Replace variable in env var has found', function () {
    expect($this->env::get('EXTEND_URL'))->toBe('http://localhost:8080/api');
});

it('Insert enviroment variable in runtime', function () {
    $this->env::set('APP_DEBUGGER', false);

    expect($this->env::get('APP_DEBUGGER'))->toBeFalse();
});

it('Replace enviroment variable in runtime', function () {
    $this->env::set('APP_BOOL', 'Hello');
    $this->env::set('APP_HELLO', '{APP_BOOL} World!');

    expect($this->env::get('APP_HELLO'))->toBe('Hello World!');
});

it('Replace existing enviroment variable in runtime', function () {
    $this->env::replace('APP_DEBUG', true);

    expect($this->env::get('APP_DEBUG'))->toBeTrue();
});

it('Replace All variables founded', function () {
    expect($this->env::get('NESTED_URI'))->toBe('http://localhost:8080/api/v1/testing');
});

it('App secret decoded succesfully as Swilen', function () {
    $this->env::set('APP_KEYED', 'swilen:NzMxYTA2MjM3YzM5ZGFhYzQyM2I5N2E4NWZmOTI3Yzc');

    expect($this->env::get('APP_KEYED'))->toBe('731a06237c39daac423b97a85ff927c7');
});

it('App secret decoded successfuly as base64', function () {
    $this->env::set('APP_KEYED_64', 'base64:NzMxYTA2MjM3YzM5ZGFhYzQyM2I5N2E4NWZmOTI3Yzc=');

    expect($this->env::get('APP_KEYED_64'))->toBe('731a06237c39daac423b97a85ff927c7');
});
