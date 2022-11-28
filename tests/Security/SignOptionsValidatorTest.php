<?php

use Swilen\Security\Exception\JwtDomainException;
use Swilen\Security\Token\OptionsValidator;

uses()->group('Security', 'SignOptions');

it('Token validate initial config', function ($expires, $seconds) {
    $validate = OptionsValidator::validate([
        'expires' => $expires,
    ]);

    expect($validate['expires'])->toBe($seconds);
})->with([
    ['60s', 60],
    ['20m', 1200],
    ['10h', 36000],
    ['5d', 432000],
]);

it('Fail if options has invalid key"', function () {
    OptionsValidator::validate([
        'last' => '5x',
    ]);
})->throws(JwtDomainException::class, 'The "last" is not valid options. Valid options: expires, algorithm, issued');

it('Fail if options key "expires" is missing', function () {
    OptionsValidator::validate([
        'algorithm' => 'data',
    ]);
})->throws(JwtDomainException::class, 'Missing expires option');

it('Espect expires as number with format "60s"', function () {
    OptionsValidator::validate([
        'expires' => '5x',
    ]);
})->throws(JwtDomainException::class);

it('Espect expires as number type', function () {
    OptionsValidator::validate([
        'expires' => 'holas',
    ]);
})->throws(JwtDomainException::class, 'Expires options expect to int value with time prefix like "60s"');
