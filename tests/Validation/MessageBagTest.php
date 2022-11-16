<?php

use Swilen\Validation\MessageBag;

uses()->group('Validation', 'MessageBag');

it('Message bag instance created without errors', function () {
    $messages = new MessageBag([]);

    expect($messages->count())->toBe(0);
    expect($messages)->toBeIterable();
});

it('Iterate messages bag as array', function () {
    $messages = new MessageBag([
        'key' => [
            'message',
            'second',
        ],
    ]);

    foreach ($messages as $key => $message) {
        expect($key)->toBe('key');
        expect($message)->toBe(['message', 'second']);
    }

    expect($messages)->toBeIterable();
    expect($messages)->toBeInstanceOf(IteratorAggregate::class);
});

it('Get first message from messages bag or null if not exists', function () {
    $messages = new MessageBag([
        'test' => [
            'First test',
            'Second test',
        ],
    ]);

    expect($messages->first('test'))->toBe('First test');
    expect($messages->get('test'))->toBe(['First test', 'Second test']);
    expect($messages->first('not-found'))->toBeNull();
});

it('Get message formatted', function () {
    $messages = new MessageBag([
        'test' => [
            'First test',
            'Second test',
        ],
    ]);

    expect($messages->all('$ :message'))->toBe([
        'test' => [
            '$ First test',
            '$ Second test',
        ],
    ]);

    expect($messages->firstOfAll('1 :message'))->toBe([
        'test' => '1 First test',
    ]);

    expect($messages->get('test', '<li> :message </li>'))->toBe([
        '<li> First test </li>',
        '<li> Second test </li>',
    ]);

    expect($messages->first('test', 'TEST :message'))->toBe('TEST First test');
});

it('Message bag is empty and is not empty', function () {
    $messages = new MessageBag([
        'found' => [
            'Is not empty',
        ],
    ]);

    expect($messages->isNotEmpty())->toBeTrue();
    expect($messages->isEmpty())->toBeFalse();
});

it('Current messages keys are exists and is enumerable and deletable', function () {
    $messages = new MessageBag([
        'found' => [
            'Is not empty',
        ],
        'other' => [
            'found Error',
        ],
    ]);

    expect($messages->keys())->toBe(['found', 'other']);
    expect($messages->has('other'))->toBeTrue();
    expect($messages->has('not-found'))->toBeFalse();

    $messages->remove('found', 'other');

    expect($messages->count())->toBe(0);
    expect($messages->all())->not->toHaveKeys(['found', 'other']);
});

it('Add new messages to message bag', function () {
    $messages = new MessageBag([]);

    expect($messages->count())->toBe(0);

    $messages->add('first_key', 'First Message');

    expect($messages->count())->toBe(1);
    expect($messages->get('first_key'))->toBe(['First Message']);

    $messages->add('first_key', 'Second Message');

    expect($messages->get('first_key'))->toHaveCount(2);
    expect($messages->all())->toHaveCount(1);
});

it('Merge new messages to existing message bag', function () {
    $messages = new MessageBag([
        'initial' => [
            'message',
        ],
    ]);

    expect($messages->all())->toHaveCount(1);

    $messages->merge([
        'second' => [
            'another-message',
        ],
    ]);

    expect($messages->all())->toHaveCount(2);
    expect($messages->keys())->toBe(['initial', 'second']);
    expect($messages->getMessages())->toBe([
        'initial' => [
            'message',
        ],
        'second' => [
            'another-message',
        ],
    ]);
});

it('Interact with Message Bag as array with \ArrayAcces implementation', function () {
    $messages = new MessageBag([]);

    $messages['key'] = 'message';
    expect($messages['key'])->toBe(['message']);

    $messages['key'] = 'another-message';
    expect($messages['key'])->toBe(['message', 'another-message']);

    expect(isset($messages['key']))->toBeTrue();
    expect(isset($messages['random']))->toBeFalse();

    unset($messages['key']);

    expect(isset($messages['key']))->toBeFalse();
    expect(count($messages))->toBe(0);
});

it('Message bag imlement Arrayable', function () {
    $messages = new MessageBag([]);

    expect($messages->toArray())->toBeArray();
});
