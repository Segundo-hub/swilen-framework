<?php

namespace Swilen\Http\Exception;

class HttpMethodNotAllowedException extends HttpException
{
    protected $code = 405;
    protected $message = 'Method Not Allowed.';
    protected $title = '405 Method Not Allowed.';
    protected $description = 'Request method not allowed in server.';
}
