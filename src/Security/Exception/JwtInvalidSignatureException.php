<?php

namespace Swilen\Security\Exception;

use Swilen\Http\Exception\HttpException;

class JwtInvalidSignatureException extends HttpException
{
    protected $code = 400;

    protected $message = 'Jwt: Invalid Signature Exception';
}
