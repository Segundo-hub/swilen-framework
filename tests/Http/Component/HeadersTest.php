<?php

use Swilen\Http\Component\File\File;
use Swilen\Http\Component\File\UploadedFile;
use Swilen\Http\Component\HeaderHunt;
use Swilen\Http\Exception\FileNotFoundException;

uses()->group('Http');

it('Headers', function () {
    $headers = new HeaderHunt();
});
