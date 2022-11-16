<?php

namespace Swilen\Petiole\Facades;

use Swilen\Petiole\Facade;

/**
 * @method static \Swilen\Security\Token\JwtSignedExpression sign(array $payload)
 * @method static \Swilen\Security\Token\Payload             verify(string $token)
 *
 * @see \Swilen\Security\Contract\TokenContract
 */
class TokenManager extends Facade
{
    protected static function getFacadeName()
    {
        return 'jwt-token';
    }
}
