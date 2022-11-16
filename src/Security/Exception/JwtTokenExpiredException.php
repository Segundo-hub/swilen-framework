<?php

namespace Swilen\Security\Exception;

class JwtTokenExpiredException extends JsonWebTokenException
{
    protected $code = 400;

    protected $message = 'Jwt: Token Time Expired Exception';
}
