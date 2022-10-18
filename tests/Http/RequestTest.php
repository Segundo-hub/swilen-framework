<?php

use Swilen\Http\Request;

uses()->group('Http', 'Request');

beforeEach(function () {
    $this->request = Request::create();
});

it('Espect \Request instance created succesfully and is instance of \Swilen\Http\Request', function () {
    expect($this->request)->toBeObject();
    expect($this->request)->toBeInstanceOf(Request::class);
});

it('Make request succesfully', function () {
    $request = Request::make('/other?name=hola', 'GET', [
        'test' => 'name'
    ]);

    expect($request->getMethod())->toBe('GET');
    expect($request->getPathInfo())->toBe('/other');
    expect($request->hasQueryString())->toBeTrue();
    expect($request->query->get('name'))->toBe('hola');
    expect($request->query->get('test'))->toBe('name');
});

it('Make request succesfully as POST', function () {
    $request = Request::make('/other?name=hola', 'POST', [
        'test' => 'name'
    ]);

    expect($request->getMethod())->toBe('POST');
    expect($request->getPathInfo())->toBe('/other');
    expect($request->hasQueryString())->toBeTrue();
    expect($request->query->get('name'))->toBe('hola');
    expect($request->request->get('test'))->toBe('name');
});

it('REQUEST_METHOD override succesfully', function () {
    $request = Request::make('put-request', 'POST', [], [], [
        'HTTP_X_METHOD_OVERRIDE' => 'PUT'
    ]);

    expect($request->getMethod())->toBe('PUT');
    expect($request->getPathInfo())->toBe('/put-request');
    expect($request->hasQueryString())->toBeFalse();
});

it('Remove slashes support for REQUEST_URI', function () {
    $request = Request::make('');

    expect($request->getPathInfo())->toBe('/');

    $request = Request::make('hola///');

    expect($request->getPathInfo())->toBe('/hola');

    $request = Request::make('/hola');

    expect($request->getPathInfo())->toBe('/hola');
});




