<?php

use Swilen\Shared\Support\Str;
use Swilen\Validation\Regex;

uses()->group('Support', 'Arr');

it('If string contains given value', function () {
    expect(Str::contains('cuzco', 'z'))->toBeTrue();
    expect(Str::contains('cuzco', '20'))->toBeFalse();
});

it('String ends with', function () {
    expect(Str::endsWith('foo>todo', 'todo'))->toBeTrue();
    expect(Str::endsWith('foo>todo', 'falsy'))->toBeFalse();
    expect(Str::endsWith('', 'search'))->toBeFalse();
});

it('String starts with', function () {
    expect(Str::startsWith('foo>todo', 'foo'))->toBeTrue();
    expect(Str::startsWith('foo>todo', 'falsy'))->toBeFalse();
});

it('String get length', function () {
    expect(Str::length(''))->toBe(0);
    expect(Str::length('hello'))->toBe(5);
    expect(Str::length("hello\35"))->toBe(6);
});

it('String transform to upper', function () {
    expect(Str::upper(''))->toBe('');
    expect(Str::upper('hello'))->toBe('HELLO');
    expect(Str::upper('hello_'))->toBe('HELLO_');
});

it('String transform to lower', function () {
    expect(Str::lower(''))->toBe('');
    expect(Str::lower('HEllo'))->toBe('hello');
    expect(Str::lower('OtHer'))->toBe('other');
});

it('String match', function () {
    expect(Str::match('/[0-9]+/', 'Helo35'))->toBe('35');
    expect(Str::match('/[0-9]/', 'Helo'))->toBe('');
});

it('String slug transform', function () {
    expect(Str::slug(' Must be a Title'))->toBe('must-be-a-title');
    expect(Str::slug(' Must be a Title', '_'))->toBe('must_be_a_title');
    expect(Str::slug('', '_', '__default'))->toBe('__default');
});

it('String generate uuid', function () {
    expect(Str::uuid())->toMatch(Regex::UUID_V4);
});

it('String check if given value is uuid', function () {
    expect(Str::isUuid([]))->toBeFalse();
    expect(Str::isUuid(Str::uuid()))->toBeTrue();
});
