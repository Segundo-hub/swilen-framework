<?php

use Swilen\Arthropod\Env;

uses()->group('Environment');

beforeAll(function () {
    Env::createFrom(dirname(__DIR__))->config([
        'file' => '.env.example',
    ])->load();
});

afterAll(function () {
    Env::forget();
});

it('Generate error when file not exist', function () {
    $deletable = Env::createFrom(__DIR__)->load();
    unset($deletable);
})->throws(RuntimeException::class);

it('Generate error when file not exist when use empty constructor', function () {
    $instance = (new Env())->load();
    unset($instance);
})->throws(RuntimeException::class);

it('Config env instance', function () {
    $instance = new Env();

    expect($instance->filename())->toBe('.env');
    expect($instance->path())->toBeNull();
    expect($instance->environmentFilePath())->toBe(DIRECTORY_SEPARATOR.'.env');
    expect($instance->isInmutable())->toBeTrue();

    $instance = $instance->config([
        'file' => '.env',
        'path' => __DIR__,
        'inmutable' => false,
    ]);

    expect($instance->filename())->toBe('.env');
    expect($instance->path())->toBe(__DIR__);
    expect($instance->environmentFilePath())->toBe(__DIR__.DIRECTORY_SEPARATOR.'.env');
    expect($instance->isInmutable())->toBeFalse();

    unset($instance);
});

it('Contains helper works', function () {
    $instance = new ReflectionClass($env = Env::getInstance());

    $method = $instance->getMethod('contains');
    $method->setAccessible(true);

    expect($method->invokeArgs($env, ['minutes + other -', ['+', '-']]))->toBeTrue();
    expect($method->invokeArgs($env, ['450--', ['/', '=']]))->toBeFalse();
});

it('Skip comment per line', function () {
    expect(Env::get('PREV_COMMENT'))->toBeTrue();
    expect(Env::get('NEXT_COMMNET'))->toBe('OK');
});

it('Commnet in line is ignored', function () {
    Env::getInstance()->set('COMMNET_IGNORED', 'COMPILE {NEXT_COMMNET} #COMMNET INLINE');

    expect(Env::get('COMMNET_IGNORED'))->toBe('COMPILE OK');
});

it('The variable is expected to return null if it is not found in the file', function () {
    expect(Env::get('TEST_NULL_ENV'))->toBeNull();
});

it('Expect a empty string if not found env variable in file', function () {
    expect(Env::get('TEST_EMPTY_ENV', ''))->toBeEmpty();
});

it('Return default variable when env is not defined', function () {
    expect(Env::get('TEST_ENV', '__default'))->toBe('__default');
});

it('Return variable if exists', function () {
    expect(Env::get('BASE_URL'))->toBe('http://localhost:8080');
});

it('Replace variable in env var has found', function () {
    expect(Env::get('EXTEND_URL'))->toBe('http://localhost:8080/api');
});

it('Insert enviroment variable in runtime', function () {
    Env::set('APP_DEBUGGER', false);

    expect(Env::get('APP_DEBUGGER'))->toBeFalse();
});

it('Replace enviroment variable in runtime', function () {
    Env::set('APP_BOOL', 'Hello');
    Env::set('APP_HELLO', '{APP_BOOL} World!');

    expect(Env::get('APP_HELLO'))->toBe('Hello World!');
});

it('Replace existing enviroment variable in runtime', function () {
    Env::replace('APP_DEBUG', true);

    expect(Env::get('APP_DEBUG'))->toBeTrue();
});

it('Replace All variables founded', function () {
    $uri = 'http://localhost:8080/api/v1/testing';

    expect(Env::get('NESTED_URI'))->toBe($uri);
    expect($_ENV['NESTED_URI'])->toBe($uri);
});

it('Compile value, put to env collection with same value when placedolders not found in stack', function () {
    $value = 'Variables replaced with ${REPLACED} and ${NOTHING}';

    Env::getInstance()->compile('NESTED_VALUE', $value);

    expect($_ENV['NESTED_VALUE'])->toBe($value);
});

it('Compile value, put to env collection with same value because not force replaced existing variables in collection', function () {
    Env::getInstance()->set('REPLACED', 'replaced');
    Env::getInstance()->set('NOTHING', 'nothing');

    $value = 'Variables replaced with ${REPLACED} and ${NOTHING}';

    Env::getInstance()->compile('NESTED_VALUE', $value);

    expect($_ENV['NESTED_VALUE'])->toBe($value);
});

it('Compile value, put to env collection with replaced value because is force replaced existing variables in collection', function () {
    Env::getInstance()->set('REPLACED', 'replaced');
    Env::getInstance()->set('NOTHING', 'nothing');

    $value = 'Variables replaced with ${REPLACED} and ${NOTHING}';

    Env::getInstance()->compile('NESTED_VALUE', $value, true);

    expect($_ENV['NESTED_VALUE'])->toBe('Variables replaced with replaced and nothing');
});

it('App secret decoded succesfully as Swilen', function () {
    Env::set('APP_KEYED', 'swilen:NzMxYTA2MjM3YzM5ZGFhYzQyM2I5N2E4NWZmOTI3Yzc');

    expect(Env::get('APP_KEYED'))->toBe('731a06237c39daac423b97a85ff927c7');
});

it('App secret decoded successfuly as base64', function () {
    Env::set('APP_KEYED_64', 'base64:NzMxYTA2MjM3YzM5ZGFhYzQyM2I5N2E4NWZmOTI3Yzc=');

    expect(Env::get('APP_KEYED_64'))->toBe('731a06237c39daac423b97a85ff927c7');

    Env::getInstance()->forget();
});

it('Transform values to primitive', function () {
    $env = Env::createFrom(__DIR__.'/__fixtures__')->config([
        'file' => '.env.primitives',
    ])->load();

    expect(Env::get('PRIMITIVE_TRUE'))->toBeTrue();
    expect(Env::get('STRING_TRUE'))->toBeTrue();
    expect(Env::get('NUMBER_TRUE'))->toBeTrue();
    expect(Env::get('SHORT_CIRCUIT_TRUE'))->toBeTrue();

    expect(Env::get('PRIMITIVE_FALSE'))->toBeFalse();
    expect(Env::get('STRING_FALSE'))->toBeFalse();
    expect(Env::get('NUMBER_FALSE'))->toBeFalse();
    expect(Env::get('SHORT_CIRCUIT_FALSE'))->toBeFalse();

    expect(Env::get('PRIMITIVE_NUMBER'))->toBeInt();
    expect(Env::get('STRING_NUMBER'))->toBeString();
    expect(Env::get('SIGN_NUMBER'))->toBeString();

    expect(Env::get('PRIMITIVE_NULL'))->toBeNull();
    expect(Env::get('EMPTY_NULL'))->toBeNull();
    expect(Env::get('STRING_NULL'))->toBeNull();

    Env::getInstance()->forget();
});

it('Remove all resolved variables and forget env instance', function () {
    Env::createFrom(__DIR__.'/__fixtures__')->config([
        'file' => '.env.primitives',
    ])->load();

    Env::getInstance()->forget();

    expect(Env::registered())->toBeEmpty();
    expect(Env::stack())->toBeEmpty();
});
