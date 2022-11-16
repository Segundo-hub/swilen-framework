<?php

use Swilen\Http\Common\HttpTransformJson;
use Swilen\Shared\Support\Arrayable;

uses()->group('Http', 'Transform');

it('Correct transform json content', function () {
    $transformer = new HttpTransformJson(['foo', 'bar']);

    expect($transformer->encode())->toBeJson();

    $decoder = new HttpTransformJson('{"name": "foo"}');

    expect($decoder->decode())->toBeObject();
    expect($decoder->decode(true))->toBeArray();
    expect($decoder->decode(true))->toBe([
        'name' => 'foo',
    ]);

    $decoder = new HttpTransformJson(true);

    expect($decoder->encode())->toBeTruthy();
});

it('Throw content for transform is invalid', function () {
    $decoder = new HttpTransformJson('{"name": "foo"');

    expect($decoder->decode())->toBeArray();
})->throws(JsonException::class);

it('Throw content for transform is invalid encoded', function () {
    $decoder = new HttpTransformJson('Parâmetros de consulta inválidos');

    expect($decoder->decode(true))->toBeArray();
})->throws(JsonException::class);

it('Throw content for transform is invalid decoded', function () {
    $file = fopen(getReadableFileStub(), 'r');

    $decoder = new HttpTransformJson($file);

    expect($decoder->encode())->toBeArray();

    fclose($file);
})->throws(JsonException::class, 'Failed encode to json: Type is not supported');

it('Should type is valid json transform', function () {
    expect(HttpTransformJson::shouldBeJson([]))->toBeTrue();
    expect(HttpTransformJson::shouldBeJson(new stdClass()))->toBeTrue();
    expect(HttpTransformJson::shouldBeJson(new UserStoreStub()))->toBeTrue();

    expect(HttpTransformJson::shouldBeJson(20))->toBeFalse();
    expect(HttpTransformJson::shouldBeJson('string'))->toBeFalse();

    expect(HttpTransformJson::morphToJsonable([]))->toBeArray();
    expect(HttpTransformJson::morphToJsonable(new UserStoreStub()))->toBeArray();
    expect(HttpTransformJson::morphToJsonable(new UserStoreJsonSerializableStub()))->toBeArray();
});

class UserStoreStub implements Arrayable
{
    public function toArray()
    {
        return [];
    }
}

class UserStoreJsonSerializableStub implements JsonSerializable
{
    public function jsonSerialize()
    {
        return [];
    }
}
