<?php

use Swilen\Validation\Validator;

uses()->group('validation');

/**
 * @property \Swilen\Validation\Validator $validate
 */
beforeEach(function () {
    $this->validate = new Validator;
});

it('Validate email', function () {
    /** @var \Swilen\Validation\Validator */
    $dataset = $this->validate->from([
        'email' => 'alexito@gmail.com'
    ]);

    $final = $dataset->validate([
        'email' => 'email|required',
    ]);

    expect($final->isSafe())->toBeTrue();
});

it('Validate email is envalid', function () {
    $this->validate = $this->validate->from([
        'email' => 'alexitogmail.com'
    ]);

    $final = $this->validate->validate([
        'email' => 'email|required',
    ]);

    expect($final->isSafe())->toBeFalse();
});

it('Validate set of values', function () {
    $validable = $this->validate->from([
        'no-required' => null,
        'username' => 'Junior',
        'age' => 34,
        'date' => '2022-09-07'
    ]);

    $final = $validable->validate([
        'username' => 'string|required',
        'age' => 'number|required',
        'date' => 'date|required',
    ]);

    expect($final->isSafe())->toBeTrue();
});



it('Validate value is number', function () {
    $this->validate = $this->validate->from([
        'data' => 300
    ]);

    $final = $this->validate->validate([
        'data' => 'number|required',
    ]);

    expect($final->isSafe())->toBeTrue();
});

// it('Request Succesfully')->get('');
