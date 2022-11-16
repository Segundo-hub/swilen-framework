<?php

use Swilen\Http\Response;
use Swilen\Http\Response\BinaryFileResponse;
use Swilen\Http\Response\JsonResponse;
use Swilen\Http\Response\StreamedResponse;
use Swilen\Routing\ResponseFactory;

uses()->group('Routing');

it('Response Factory return correspond instance for the method', function () {
    $factory = new ResponseFactory();

    expect($factory->make(''))->toBeInstanceOf(Response::class);
    expect($factory->send(''))->toBeInstanceOf(JsonResponse::class);
    expect($factory->json(''))->toBeInstanceOf(JsonResponse::class);
    expect($factory->file(getReadableFileStub()))->toBeInstanceOf(BinaryFileResponse::class);
    expect($factory->download(getReadableFileStub()))->toBeInstanceOf(BinaryFileResponse::class);
    expect($factory->stream(function () {}))->toBeInstanceOf(StreamedResponse::class);

    $binary = new ResponseFactory();
    $filename = 'testing.txt';
    $binary = $binary->download(getReadableFileStub(), $filename);

    expect($binary->headers->get('Content-Disposition'))->toBe('attachment; filename="'.$filename.'"');
});
