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
    expect($response->getBody())->toBeNull();
});

it('Expect JsonResponse() send content as json', function () {
    /** @var  JsonResponse $response */
    list($response, $content) = getBuffer(function () {
        return (new JsonResponse(['hello' => 'World']))->prepare(Request::make(''))->terminate();
    });
    expect($response->headers->get('Content-Type'))->toBeGreaterThanOrEqual('application/json');
    expect($response->getStatusCode())->toBe(Http::OK);
    expect($response->getBody())->toBeJson();
    expect($content)->toBe('{"hello": "World"}');
});
