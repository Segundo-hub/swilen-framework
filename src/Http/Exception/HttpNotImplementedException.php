<?php

namespace Swilen\Http\Exception;

class HttpNotImplementedException extends HttpException
{
    protected $code = 501;
    protected $message = 'Not Implemented.';
    protected $title = '501 Not Implemented';
    protected $description = 'Funcionality not implemented in your server.';
}
