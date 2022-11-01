<?php

use Swilen\Http\Component\File\File;
use Swilen\Http\Component\File\UploadedFile;
use Swilen\Http\Exception\FileNotFoundException;

uses()->group('Http', 'File');

it('File Content is valid', function () {
    $file = new UploadedFile(__DIR__ . '/fixtures/test.txt', 'test.txt');

    expect(trim($file->getContent()))->toBe('No content for movement this file');
    expect($file->getMimeType())->toBe('text/plain');
});

it('Generate error when file not exists', function () {
    new UploadedFile(__DIR__ . '/fixture/not-found.txt', 'not-found.txt');
})->throws(FileNotFoundException::class);


it('Verify if file is instance of SplFileInfo', function () {
    $file = new File(__DIR__ . '/fixtures/test.txt', true);

    expect($file)->toBeInstanceOf(SplFileInfo::class);
});

