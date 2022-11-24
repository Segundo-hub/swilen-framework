<?php

use Swilen\Shared\Support\Str;

uses()->group('Support', 'Arr');

it('If string contains given value', function () {
    expect(Str::contains('cuzco', 'z'))->toBeTrue();
    expect(Str::contains('cuzco', '20'))->toBeFalse();
});
