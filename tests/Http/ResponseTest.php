<?php

use Swilen\Http\Common\Http;
use Swilen\Http\Request;
use Swilen\Http\Response;

uses()->group('Http', 'Response');

it('Espect \Response instance created succesfully and is instance of \Swilen\Http\Response', function () {
    $response = new Response();
    expect($response)->toBeObject();
    expect($response)->toBeInstanceOf(Response::class);
    expect($response->getStatusCode())->toBe(Http::OK);
    expect($response->getBody())->toBeNull();
});

it('Response with empty content', function () {
    $response = new Response('testing', Http::NO_CONTENT);
    $prepared = $response->prepare(Request::make('/'));

    expect($prepared->getBody())->toBeNull();
    expect($prepared->getStatusCode())->toBe(Http::NO_CONTENT);
    expect($prepared->isEmpty())->toBeTrue();

    $response = new Response('content-ignored');
    expect($response->getBody())->not->toBeNull();
    expect($response->isEmpty())->toBeFalse();

    $response->setNotModified();

    expect($response->isEmpty())->toBeTrue();
    expect($response->getBody())->toBeNull();

    $response = new Response('omited');
    expect($response->getBody())->not->toBeNull();

    $response->prepare(Request::make('', Http::METHOD_HEAD));
    expect($response->getBody())->toBeNull();
});

it('Throw error when insert invalid status code', function () {
    $response = new Response();

    $response->withStatus(10);
})->throws(InvalidArgumentException::class, 'The HTTP status code "10" is not valid.');

it('Work with status codes', function () {
    $response = new Response();

    expect($response->isSuccessful())->toBeTrue();
    expect($response->isOk())->toBeTrue();
    expect($response->isServerError())->toBeFalse();

    $response = $response->withStatus(100);
    expect($response->isInformational())->toBeTrue();
    expect($response->isOk())->toBeFalse();

    $response = $response->withStatus(Http::NO_CONTENT);
    expect($response->isEmpty())->toBeTrue();
    expect($response->isOk())->toBeFalse();

    $response = $response->withStatus(Http::MOVED_PERMANENTLY);
    expect($response->isRedirection())->toBeTrue();
    expect($response->isOk())->toBeFalse();

    $response = $response->withStatus(400);
    expect($response->isClientError())->toBeTrue();
    expect($response->isOk())->toBeFalse();

    $response = $response->withStatus(Http::NOT_FOUND);
    expect($response->isNotFound())->toBeTrue();
    expect($response->isClientError())->toBeTrue();

    $response = $response->withStatus(500);
    expect($response->isServerError())->toBeTrue();
    expect($response->isClientError())->toBeFalse();
    expect($response->isOk())->toBeFalse();

    $response = $response->withStatus(403);
    expect($response->isForbidden())->toBeTrue();
    expect($response->isOk())->toBeFalse();
});

it('Status text in response', function () {
    $response = new Response();
    expect($response->getReasonPhrase())->toBe('OK');

    $response->withStatus(100);
    expect($response->getReasonPhrase())->toBe('Continue');
});

it('Insert header succesfully', function () {
    $response = new Response();
    $response->withHeader('Fo', 'bar');
    expect($response->headers->get('Fo'))->toBe('bar');

    $response->withHeaders([
        'bar-1' => 'foo',
        'foo-1' => 'bar',
    ]);
    expect($response->headers->all())->toHaveKeys(['Fo', 'bar-1', 'foo-1']);

    $response = $response->header('X-Key', 'x-value');
    expect($response->headers->has('X-Key'))->toBeTrue();

    $response = $response->headers([
        'x-header' => 'data',
        'x-token' => 'data-token',
    ]);

    expect($response->headers->has('x-header'))->toBeTrue();
    expect($response->headers->has('x-token'))->toBeTrue();
});

it('Interact with response body', function () {
    $response = new Response();

    expect($response->getBody())->toBeNull();
    $response = $response->withBody('test');
    expect($response->getBody())->toBe('test');
});

it('Body to send client', function () {
    /** @var Response $response */
    list($response, $content) = getBuffer(function () {
        return (new Response('simple-text'))->prepare(Request::make(''))->terminate();
    });

    expect($content)->toBe('simple-text');
    expect($response->headers->get('Content-Type'))->toBeIn(['text/html', 'text/html; charset=utf-8']);
    expect($response->getBody())->toBe('simple-text');
});

it('Content-Type and charset fixed in response', function () {
    $response = new Response(null);

    expect($response->headers->get('Content-Type'))->toBeNull();

    $response->withHeader('Content-Type', 'text/html');
    expect($response->headers->get('Content-Type'))->not->toBeNull();

    $response->prepare(Request::make(''));
    expect($response->headers->get('Content-Type'))->toMatch('/^text\/html;\scharset=+/');
});

it('Fix content length when found Transfer-Encoding present', function () {
    $response = new Response(null);

    $response->withHeader('Transfer-Encoding', 'gzip')->prepare(Request::make(''));
    expect($response->headers->has('Content-Length'))->toBeFalse();
});

it('Fix cache headers based in server protocol', function () {
    $response = new Response();

    expect($response->getProtocolVersion())->toBe('1.0');
    $response->prepare(Request::make(''));
    expect($response->getProtocolVersion())->toBe('1.1');

    $response = (new Response(null, 200, [
        'Cache-Control' => 'no-cache',
    ]))->prepare(Request::make('/'));

    expect($response->hasHeader('pragma'))->toBeTrue();
    expect($response->hasHeader('expires'))->toBeTrue();
});

it('finish request with fastcgi_finish_request', function () {
    list($response, $content) = getBuffer(function () {
		mockFinishRequestFunc();
        return (new Response())->prepare(Request::make('/'))->terminate();
    });

    expect($content)->toBe('');
    expect($response->getBody())->toBeNull();
});

function mockFinishRequestFunc()
{
    if (!function_exists('fastcgi_finish_request')) {
        function fastcgi_finish_request()
        {
            return null;
        }
    }
}
