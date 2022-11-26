<?php

use Swilen\Shared\Support\Arrayable;
use Swilen\Shared\Support\Json;

uses()->group('Http', 'Transform');

it('Correct transform json content', function () {
    $transformer = new Json(['foo', 'bar']);

    expect($transformer->encode())->toBeJson();

    $decoder = new Json('{"name": "foo"}');

    expect($decoder->decode())->toBeObject();
    expect($decoder->decode(true))->toBeArray();
    expect($decoder->decode(true))->toBe([
        'name' => 'foo',
    ]);

    $decoder = new Json(true);

    expect($decoder->encode())->toBeTruthy();
});

it('Throw content for transform is invalid', function () {
    $decoder = new Json('{"name": "foo"');

    expect($decoder->decode())->toBeArray();
})->throws(JsonException::class);

it('Throw content for transform is invalid encoded', function () {
    $decoder = new Json('Parâmetros de consulta inválidos');

    expect($decoder->decode(true))->toBeArray();
})->throws(JsonException::class);

it('Throw content for transform is invalid decoded', function () {
    $file = fopen(getReadableFileStub(), 'r');

    $decoder = new Json($file);

    expect($decoder->encode())->toBeArray();

    fclose($file);
})->throws(JsonException::class, 'Failed encode json: Type is not supported');

it('Should type is valid json transform', function () {
    expect(Json::shouldBeJson([]))->toBeTrue();
    expect(Json::shouldBeJson(new stdClass()))->toBeTrue();
    expect(Json::shouldBeJson(new UserStoreStub()))->toBeTrue();

    expect(Json::shouldBeJson(20))->toBeFalse();
    expect(Json::shouldBeJson('string'))->toBeFalse();
});

class UserStoreStub implements Arrayable
{
    public function toArray()
    {
        return [];
    }
}
