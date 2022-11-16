<?php

namespace Swilen\Security\Exception;

class JwtInvalidSignatureException extends JsonWebTokenException
{
    protected $code = 400;

    protected $message = 'Jwt: Invalid Signature Exception';
}
