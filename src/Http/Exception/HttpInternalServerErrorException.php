<?php

namespace Swilen\Http\Exception;

class HttpInternalServerErrorException extends HttpException
{
    protected $code = 500;
    protected $message = 'Internal Server Error.';
    protected $title = '500 Internal Server Error';
    protected $description = 'The server process script encountered a runtime fatal error.';
}
