<?php

use Swilen\Http\Common\Http;
use Swilen\Http\Exception\FileNotFoundException;
use Swilen\Http\Request;
use Swilen\Http\Response;

uses()->group('Http', 'Response');

beforeEach(function () {
    $this->response = new Response();
});

it('Espect \Response instance created succesfully and is instance of \Swilen\Http\Response', function () {
    expect($this->response)->toBeObject();
    expect($this->response)->toBeInstanceOf(Response::class);
    expect($this->response->statusCode())->toBe(Http::OK);
    expect($this->response->getContent())->toBeNull();
});

/**
 * Testing binary file response
 */
it('Expect \Response::file() get file as resource via stream', function () {
    ob_start();

    $this->response->file(__DIR__ . '/fixtures/testing.md')->prepare(Request::make(''))->terminate();

    expect(trim(ob_get_clean()))->toBe('Testing Markdown');
    expect($this->response->headers->get('Content-Type'))->toBeIn([
        'text/markdown', 'application/octet-stream'
    ]);
    expect($this->response->statusCode())->toBe(Http::OK);
});

it('Expect \Response::file() get file generate error when file is not exists', function () {
    expect($this->response->file(__DIR__ . '/fixtures/nothing.md'))->toBeResource();
})->throws(FileNotFoundException::class);

/**
 * Testing json, array response
 */
it('Expect \Response::send() send content as json', function ($dataset) {
    ob_start();
    $this->response->send($dataset)->prepare(Request::make(''))->terminate();

    expect(trim(ob_get_clean()))->toBeJson();
    expect($this->response->headers->get('Content-Type'))->toBeGreaterThanOrEqual('application/json');
    expect($this->response->statusCode())->toBe(Http::OK);
    expect(json_decode($this->response->getContent()))->toBe($dataset);
})->with([
    'json' => true
]);

it('Expect factory get json prevent encoding', function () {
    $response = new Response(['test' => 'swilen'], Http::NO_CONTENT);
    $prepared = $response->prepare(Request::make('/'));

    expect($prepared->getContent())->toBeNull();
    expect($prepared->statusCode())->toBe(Http::NO_CONTENT);
});

it('Insert header succesfully', function () {
    $this->response->withHeader('Fo', 'bar');

    expect($this->response->headers->get('Fo'))->toBe('bar');
});
