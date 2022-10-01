<?php

namespace Swilen\Security\Exception;

use Swilen\Http\Exception\HttpException;

class JwtMalformedException extends HttpException
{
    protected $code = 400;

    protected $message = 'Jwt: Token Malformed Exception';
}
