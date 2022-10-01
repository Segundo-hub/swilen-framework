<?php

namespace Swilen\Http\Exception;

class HttpBadRequestException extends HttpException
{
    protected $code = 400;
    protected $message = 'Bad Request.';
    protected $title = '400 Bad Request';
    protected $description = 'Request process failed!.';
}
