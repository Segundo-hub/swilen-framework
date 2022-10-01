<?php

namespace Swilen\Http\Exception;

class HttpForbiddenException extends HttpException
{
    protected $code = 403;
    protected $message = 'Forbidden';
    protected $title = '403 Forbidden';
    protected $description = 'Timed out before receiving response from the upstream server.';
}
