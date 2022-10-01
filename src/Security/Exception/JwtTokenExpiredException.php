<?php

namespace Swilen\Security\Exception;

use Swilen\Http\Exception\HttpException;

class JwtTokenExpiredException extends HttpException
{
    protected $code = 400;

    protected $message = 'Jwt: Token Time Expired Exception';
}
