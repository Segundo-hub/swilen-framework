<?php

use Swilen\Config\Arr;

uses()->group('Config', 'Arr');

it('Detect is accesible when is array or implement \ArrayAccess', function () {
    $arrayAccess = makeArrayAccess([]);

    expect(Arr::accessible($arrayAccess))->toBeTrue();
    expect(Arr::accessible([]))->toBeTrue();
    expect(Arr::accessible('String'))->toBeFalse();
});

it('Detect key exists in array or offsetExist of \ArrayAccess implemention', function () {
    $arrayAccess = makeArrayAccess(['todo' => 'fo']);

    expect(Arr::exists($arrayAccess, 'todo'))->toBeTrue();
    expect(Arr::exists(['todo' => 'fo'], 'todo'))->toBeTrue();
    expect(Arr::exists($arrayAccess, 'nothing'))->toBeFalse();
    expect(Arr::exists(['todo' => false], 'nothing'))->toBeFalse();
});

it('Return default if key not exits or not provide', function () {
    $arrayAccess = makeArrayAccess(['todo' => 'fo']);

    // NOT EXISTS OR INNACESIBLE
    expect(Arr::get($arrayAccess, '404', 'FOO'))->toBe('FOO');
    expect(Arr::get('STRING', 'INNACESIBLE', '__default'))->toBe('__default');

    // EXISTS BUT NO KEY WAS PROVIDED
    expect(Arr::get(['todo' => 'fo'], null, 'todo'))->toBe(['todo' => 'fo']);

    // RETURN EXISTS
    expect(Arr::get($arrayAccess, 'todo'))->toBe('fo');
});

it('Verify if key has in given array', function () {
    $arrayAccess = makeArrayAccess(['todo' => 'fo']);
    $array       = ['data' => ['fo' => 'fo']];

    // WHEN IS EMPTY ARRAY AND KEY
    expect(Arr::has([], []))->toBeFalse();

    expect(Arr::has($arrayAccess, 'todo'))->toBeTrue();

    expect(Arr::has($array, 'data.fo'))->toBeTrue();
    expect(Arr::has($array, 'data.nothing'))->toBeFalse();
});

it('Set array with reference', function () {
    $arrayAccess = makeArrayAccess([]);
    $empty       = [];

    Arr::set($empty, null, ['data' => 'fo']);
    Arr::set($arrayAccess, null, ['data' => 'fo']);

    expect($empty)->toHaveKey('data');
    expect($arrayAccess)->toHaveKey('data');

    Arr::set($empty, 'data', ['FOO' => 'BAR']);
    Arr::set($arrayAccess, 'data', ['FOO' => 'BAR']);

    expect($empty)->toBe(['data' => ['FOO' => 'BAR']]);
    expect($arrayAccess)->toBe(['data' => ['FOO' => 'BAR']]);
});

function makeArrayAccess(array $items = [])
{
    return new class($items) implements ArrayAccess {
        public $items = [];

        public function __construct(array $items)
        {
            $this->items = $items;
        }

        #[ReturnTypeWillChange]
        public function offsetExists($offset)
        {
            return isset($this->items[$offset]);
        }

        #[ReturnTypeWillChange]
        public function offsetSet($offset, $value)
        {
            $this->items[$offset] = $value;
        }

        #[ReturnTypeWillChange]
        public function offsetGet($offset)
        {
            return $this->items[$offset];
        }

        #[ReturnTypeWillChange]
        public function offsetUnset($offset)
        {
            unset($this->items[$offset]);
        }
    };
}
