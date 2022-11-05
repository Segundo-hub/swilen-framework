<?php

namespace Swilen\Petiole\Facades;

use Swilen\Petiole\Facade;

/**
 * @method static \Swilen\Security\Token\JwtSignedExpression sign(array $payload, string $secret = null, string $algo = null)
 * @method static \Swilen\Security\Token\Payload             verify(string $token, $secret = null, ?string $algo = null)
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
