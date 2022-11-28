<?php

use Swilen\Http\Common\Http;
use Swilen\Http\Request;
use Swilen\Http\Response\StreamedResponse;

uses()->group('Http', 'Response');

it('Espect \Response instance created succesfully and is instance of \Swilen\Http\Response\StreamedResponse', function () {
    $response = new StreamedResponse(function () {});
    expect($response)->toBeObject();
    expect($response)->toBeInstanceOf(StreamedResponse::class);
    expect($response->getStatusCode())->toBe(Http::OK);
    expect($response->getBody())->toBeNull();
});

it('Expect StreamedResponse() send content as json', function () {
    /** @var StreamedResponse $response */
    list($response, $content) = getBuffer(function () {
        return (new StreamedResponse(function () {
            echo json_encode(['hello' => 'World']);
        }, 200, [
            'Content-Type' => 'application/json',
        ]))->prepare(Request::make(''))->terminate();
    });
    expect($response->headers->get('Content-Type'))->toBeGreaterThanOrEqual('application/json');
    expect($response->isOk())->toBeTrue();
    expect($response->getBody())->toBeNull();
    expect($content)->toBe('{"hello":"World"}');
});

it('Expect StreamedResponse() send content with sleep', function () {
    /** @var StreamedResponse $response */
    list($response, $content) = getBuffer(function () {
        return (new StreamedResponse(function () {
            echo 'First,';
            flush();
            usleep(150);
            echo 'Second';
            flush();
        }))->prepare(Request::make(''))->terminate();
    });
    expect($response->isOk())->toBeTrue();
    expect($response->getBody())->toBeNull();
    expect($content)->toBe('First,Second');
});

it('Expect StreamedResponse() send headers and callback call once', function () {
    $callable = new StreamedCallbackStub();
    /** @var StreamedResponse $response */
    list($response, $content) = getBuffer(function () use ($callable) {
        return (new StreamedResponse($callable))->prepare(Request::make(''))->terminate()->terminate();
    });
    expect($response->isOk())->toBeTrue();
    expect($response->getBody())->toBeNull();
    expect($content)->toBe('Hello');
    expect($callable->call)->toBe(1);
});

class StreamedCallbackStub
{
    public $call = 0;

    public function __invoke()
    {
        ++$this->call;

        echo 'Hello';
    }
}
