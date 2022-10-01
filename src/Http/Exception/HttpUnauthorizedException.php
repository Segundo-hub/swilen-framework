<?php

namespace Swilen\Http\Exception;

class HttpUnauthorizedException extends HttpException
{
    protected $code = 401;
    protected $message = 'Unauthorized.';
    protected $title = '401 Unauthorized.';
    protected $description = 'The server needs authorization to serve this resource.';
}
