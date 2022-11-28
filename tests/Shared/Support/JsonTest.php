<?php

use Swilen\Shared\Support\Arrayable;
use Swilen\Shared\Support\Json;
use Swilen\Shared\Support\Jsonable;

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
})->throws(JsonException::class, 'Failed encode json: A value of a type that cannot be encoded was given.');

it('Should type is valid json transform', function () {
    expect(Json::shouldBeJson([]))->toBeTrue();
    expect(Json::shouldBeJson(new stdClass()))->toBeTrue();
    expect(Json::shouldBeJson(new UserStoreStub()))->toBeTrue();
    expect(Json::shouldBeJson(new JsonableStub()))->toBeTrue();

    expect(Json::shouldBeJson(20))->toBeFalse();
    expect(Json::shouldBeJson('string'))->toBeFalse();
});

it('Morph content to json', function () {
    expect(Json::morphToJson([]))->toBeJson();
    expect(Json::morphToJson(new stdClass()))->toBeJson();
    expect(Json::morphToJson(new UserStoreStub()))->toBeJson();
    expect(Json::morphToJson(new JsonableStub()))->toBeJson();
    expect(Json::morphToJson(new JsonSerializableStub()))->toBeJson();
});

class UserStoreStub implements Arrayable
{
    public function toArray()
    {
        return [];
    }
}

class JsonableStub implements Jsonable
{
    public function toJson($options = 0)
    {
        return json_encode(['foo'], $options);
    }
}

class JsonSerializableStub implements JsonSerializable
{
    #[ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return ['foo'];
    }
}
