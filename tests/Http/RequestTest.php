<?php

use Swilen\Http\Component\File\UploadedFile;
use Swilen\Http\Exception\HttpNotOverridableMethodException;
use Swilen\Http\Request;

uses()->group('Http', 'Request');

beforeEach(function () {
    $this->request = Request::create();
});

it('Test if request is created without errors', function () {
    expect($this->request)->toBeObject();
    expect($this->request)->toBeInstanceOf(Request::class);
});

/*
 * @param \Swilen\Http\Request $request
 */
it('Request instance created with empty attributes', function () {
    $request = new Request();

    expect($request->server->all())->toBeArray()->toBeEmpty();
    expect($request->headers->all())->toBeArray()->toBeEmpty();
    expect($request->files->all())->toBeArray()->toBeEmpty();
    expect($request->request->all())->toBeArray()->toBeEmpty();
    expect($request->query->all())->toBeArray()->toBeEmpty();
    expect($request->getBody())->toBeFalsy();
    expect($request->getMethod())->toBe('GET');
    expect($request->isMethodSafe())->toBeTrue();
    expect($request->getInputSource()->all())->toBeEmpty();
});

it('Request instance created with superglobals from static method', function () {
    $request = Request::create();

    expect($request->server->all())->toBeArray()->not->toBeEmpty();
    expect($request->headers->all())->toBeArray()->toBeEmpty();
    expect($request->request->all())->toBeArray()->toBeEmpty();
    expect($request->query->all())->toBeArray()->toBeEmpty();
    expect($request->getBody())->toBeFalsy();
    expect($request->getMethod())->toBe('GET');
});

it('Access current request instance attributes with available methods', function () {
    $request = Request::create();

    expect($request->server('REQUEST_METHOD'))->toBeNull();
    expect($request->server('REQUEST_METHOD', 'GET'))->toBe('GET');

    expect($request->query('nothing', 'espect'))->toBe('espect');
    expect($request->input('data-item', 'not-found'))->toBe('not-found');
    expect($request->file('file-not-exists'))->toBeNull();

    expect($request->all())->toBeArray()->toBeEmpty();
});

it('Access current request instance attributes ass array or object by magic methods', function () {
    $request = Request::create();

    $request->property = null;

    expect($request->property)->toBeNull();
    expect($request['property'])->toBeNull();
    expect(isset($request->property))->toBeTrue();
    expect(isset($request['property']))->toBeTrue();

    $request['property'] = 'override';

    expect($request->property)->toBe('override');

    // Delete a property as array
    unset($request['property']);

    expect(isset($request['property']))->toBeFalse();

    $request->temp = 'allow';

    // Deelete a preperty as object
    unset($request->temp);

    expect(isset($request->temp))->toBeFalse();
    expect($request->all())->toBeEmpty();
});

it('Decode json request Content-Type has json', function () {
    /**
     * @var \Mockery\MockInterface|\Mockery\LegacyMockInterface|Request $request
     */
    $request = Mockery::mock(Request::class, [[], [], [], [], '{"test": true}']);
    $request = $request->makePartial();

    $request->shouldReceive('isJsonRequest')
        ->with()->andReturn(true);

    expect($request->getInputSource()->all())->toBe(['test' => true]);
    expect($request->getInputSource()->all())->toBe(['test' => true]);
    expect($request->request->all())->toBeEmpty();
});

it('Make request succesfully with attributes', function () {
    $request = Request::make('/other?name=hola', 'GET', [
        'test' => 'name',
    ]);

    expect($request->getMethod())->toBe('GET');
    expect($request->getPathInfo())->toBe('/other');
    expect($request->hasQueryString())->toBeTrue();
    expect($request->query->get('name'))->toBe('hola');
    expect($request->query->get('test'))->toBe('name');
    expect($request->query->all())->not->toBeEmpty();
});

it('Make request succesfully as POST', function () {
    $request = Request::make('/other?name=hola', 'POST', [
        'test' => 'name',
    ]);

    expect($request->getMethod())->toBe('POST');
    expect($request->getPathInfo())->toBe('/other');
    expect($request->hasQueryString())->toBeTrue();
    expect($request->query->get('name'))->toBe('hola');
    expect($request->request->get('test'))->toBe('name');
});

it('REQUEST_METHOD override succesfully', function () {
    $request = Request::make('put-request', 'POST', [], [], [
        'HTTP_X_METHOD_OVERRIDE' => 'PUT',
    ]);

    expect($request->getMethod())->toBe('PUT');
    expect($request->getRealMethod())->toBe('POST');
    expect($request->getPathInfo())->toBe('/put-request');
    expect($request->hasQueryString())->toBeFalse();

    $request->withMethod('PUT');

    expect($request->getMethod())->toBe('PUT');
    expect($request->getRealMethod())->toBe('PUT');
    expect($request->isMethod('PUT'))->toBeTrue();
});

it('REQUEST_METHOD override failed', function () {
    $request = Request::make('put-request', 'POST', [], [], [
        'HTTP_X_METHOD_OVERRIDE' => 'GET',
    ]);

    expect($request->getPathInfo())->toBe('/put-request');
    expect($request->getMethod())->toBe('PUT');
})->throws(HttpNotOverridableMethodException::class);

it('Remove slashes support for REQUEST_URI', function () {
    expect(Request::make('')->getPathInfo())->toBe('/');
    expect(Request::make('hola///')->getPathInfo())->toBe('/hola');
    expect(Request::make('/hola')->getPathInfo())->toBe('/hola');
});

it('Retrieve Bearer Token if exists', function () {
    $request = Request::make('test', 'GET', [], [], [
        'Authorization' => '',
    ]);

    expect($request->bearerToken())->toBeNull();
    expect($request->headers->get('Authorization'))->toBeEmpty();

    $request = Request::make('test', 'GET', [], [], [
        'Authorization' => 'Bearer xxx.xxx.xxxx.xxx',
    ]);

    expect($request->bearerToken())->toBe('xxx.xxx.xxxx.xxx');
    expect($request->headers->get('Authorization'))->toBe('Bearer xxx.xxx.xxxx.xxx');
});

it('Its filter when APP_BASE_URI provided', function () {
    $request = Request::make('app/other/value', 'GET', [], [], [
        'Authorization' => '',
    ]);

    $_ENV['APP_BASE_URI'] = 'app';

    expect($request->getPathInfo())->toBe('/other/value');

    unset($_ENV['APP_BASE_URI']);
});

it('Detect content type', function () {
    $request = Request::make('app/other/value', 'GET', [], [], [
        'Authorization' => '',
    ]);

    expect($request->isJsonRequest())->toBeFalse();
    $request->headers->set('Content-Type', 'application/json');
    expect($request->isJsonRequest())->toBeTrue();

    expect($request->isFormRequest())->toBeFalse();
    $request->headers->set('Content-Type', 'multipart/form-data');
    expect($request->isFormRequest())->toBeTrue();

    expect($request->hasHeader('Content-Type'))->toBeTrue();
});

it('Request pathInfo is cached', function () {
    $request = Request::make('app/other/value', 'GET', [], [], [
        'Authorization' => '',
    ]);

    expect($request->getPathInfo())->toBe('/app/other/value');

    $request->server->set('REQUEST_URI', 'falsy');

    expect($request->getPathInfo())->toBe('/app/other/value');
    expect($request->server('REQUEST_URI'))->toBe('falsy');
});

it('Set user to request instance', function () {
    $request = Request::create('');

    expect($request->user())->toBeNull();

    $request->withUser([
        'userId' => 24,
        'username' => 'foo',
    ]);

    expect($request->user())->toBeArray();
});

it('Read content is stream or resource', function () {
    $file = fopen(getReadableFileStub(), 'rb');

    $request = new Request([], [], [], [], $file);

    expect(trim($request->getBody()))->toBe('test');
});

it('Get file from request', function () {
    $request = new Request([], [
        'test-file' => [
            'name' => 'File.txt',
            'type' => 'plain/txt',
            'tmp_name' => getReadableFileStub(),
            'error' => UPLOAD_ERR_OK,
            'size' => 98174,
        ],
    ]);

    expect($request->hasFile('test-file'))->toBeTrue();
    expect($request->files->get('test-file'))->toBeInstanceOf(UploadedFile::class);
    expect($request->hasFile('not-found'))->toBeFalse();
    expect($request->files->get('not-found'))->toBeNull();
});
