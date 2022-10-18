<?php

use Swilen\Arthropod\Env;

uses()->group('Enviroment');

beforeAll(function () {
    Env::createFrom(dirname(__DIR__))->config([
        'file' => '.env'
    ])->load();
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
    expect($this->env::get('APP_SECRET'))->toBe('a9bb6de2d1e03e3e7e4c2c14e990e3a5');
})->skip('Ignore because the console generates a random key and does not match the cached value.');

it('App secret decoded successfuly as base64', function () {
    expect($this->env::get('APP_SECRET_64'))->toBe('8f62183d7e8c5ec2c446137515b173d3');
})->skip('Ignore because the console generates a random key and does not match the cached value.');
