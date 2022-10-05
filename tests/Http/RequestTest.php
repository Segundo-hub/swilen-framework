<?php

use Swilen\Http\Request;

uses()->group('Request');

beforeEach(function () {
    $_SERVER['REQUEST_URI'] = 'app/database/api';
    $_SERVER['REQUEST_METHOD'] = 'get';

    $this->request = Request::create();
});

it('Request capture REQUEST_METHOD', function () {
    expect($this->request->getMethod())->toBe('GET');
});

it('Request capture REQUEST_URI', function () {
    expect($this->request->getAction())->toBe('/app/database/api');
});

it('Request capture REQUEST_URI filteted with base uri', function () {
    putenv('APP_BASE_URI=app');
    expect($this->request->getAction())->toBe('/database/api');
});

