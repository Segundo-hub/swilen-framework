<?php

use Swilen\Config\ConfigContract;
use Swilen\Config\Repository;

uses()->group('Config');

it('Configuration repository', function () {
    $config = new Repository([
        'nested' => [
            'key' => 20,
        ],
    ]);

    expect($config)->toBeInstanceOf(ConfigContract::class);

    expect($config->get('nested.key'))->toBe(20);
    expect($config->get('config.not', '__default'))->toBe('__default');
    expect($config->has('nothing'))->toBeFalse();
    expect($config->has('nested'))->toBeTrue();
});

it('Config manage values in runtime', function () {
    $config = new Repository([
        'nested' => [
            'key' => 20,
        ],
    ]);

    expect($config->all())->toHaveCount(1);

    $config->set('bar', 'fo');

    expect($config->all())->toHaveCount(2);
    expect($config->get('bar'))->toBe('fo');

    $config->push('nested', 25);

    expect($config->all())->toHaveCount(2);
    expect($config->get('nested'))->toBe(['key' => 20, 25]);
});

it('Config get many values from a key', function () {
    $config = new Repository([
        'nested' => [
            'key' => 20,
        ],
    ]);

    expect($config->getManyConfig(['nested']))->toBe([
        'nested' => [
            'key' => 20,
        ],
    ]);

    expect($config->getManyConfig('nested'))->toBe([
        'nested' => [
            'key' => 20,
        ],
    ]);

    expect($config->getManyConfig('empty'))->toBe(['empty' => null]);
});

it('Interact with config instance as array', function () {
    $config = new Repository([
        'nested' => [
            'key' => 20,
        ],
    ]);

    expect($config->all())->toHaveCount(1);

    $config['other.value'] = true;

    expect($config->all())->toHaveCount(2);
    expect($config['other.value'])->toBeTrue();

    expect(isset($config['other']))->toBeTrue();
    expect(isset($config['nothing']))->toBeFalse();

    unset($config['other']);

    expect($config->all())->toHaveCount(2);
    expect($config['other'])->toBeNull();
});
