<?php

use Swilen\Http\Common\Http;
use Swilen\Http\Request;
use Swilen\Http\Response\JsonResponse;

uses()->group('Http', 'Response');

it('Espect \Response instance created succesfully and is instance of \Swilen\Http\Response\JsonResponse', function () {
    $response = new JsonResponse();
    expect($response)->toBeObject();
    expect($response)->toBeInstanceOf(JsonResponse::class);
    expect($response->getStatusCode())->toBe(Http::OK);
});

it('Expect JsonResponse() send content as json', function () {
    /** @var JsonResponse $response */
    list($response, $content) = getBuffer(function () {
        return (new JsonResponse(['hello' => 'World']))->prepare(Request::make(''))->terminate();
    });
    expect($response->headers->get('Content-Type'))->toMatch('/^application\/json+/');
    expect($response->getStatusCode())->toBe(Http::OK);
    expect($response->getBody())->toBeJson();
    expect($content)->toBe('{"hello":"World"}');
});

it('Prevent override Content-Type in header', function () {
    $response = new JsonResponse([], 200, ['Content-Type' => 'text/html']);

    expect($response->headers->get('Content-Type'))->toMatch('/^application\/json+/');
});

it('Send new JsonResponse from jsonString', function () {
    $response = JsonResponse::fromJson('{"bar":"foo"}');

    expect($response->getBody())->toBe('{"bar":"foo"}');
    expect($response->isOk())->toBeTrue();
});

it('Interact with json body', function () {
    $response = new JsonResponse();

	expect($response->getBody())->toBeJson();
	expect($response->getBody())->toBe('{}');

	$response->withBody([]);
	expect($response->getBody())->toBe('[]');
});


it('Throw TypeError when passing data as invalid data type', function () {
    new JsonResponse([], 200, [], true);
})->throws(TypeError::class, '"Swilen\Http\Response\JsonResponse::setBody": If $json is set to true, argument $data must be a string, "array" given.');
