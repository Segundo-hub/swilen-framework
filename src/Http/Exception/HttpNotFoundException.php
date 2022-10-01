<?php

namespace Swilen\Http\Exception;

class HttpNotFoundException extends HttpException
{
    protected $code = 404;
    protected $message = 'Not Found.';
    protected $title = '404 Not Found.';
    protected $description = 'Request resource is missing';
}
