<?php

namespace Swilen\Security\Exception;

class JwtMalformedException extends JsonWebTokenException
{
    protected $code = 400;

    protected $message = 'Jwt: Token Malformed Exception';
}
