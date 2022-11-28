<?php

namespace Swilen\Security\Contract;

interface JwtService
{
    /**
     * Sing Json Web Token with given payload.
     *
     * @param array<string,string> $payload
     * @param string|null          $secret
     * @param string               $algo
     *
     * @return \Swilen\Security\Token\JwtSignedExpression
     */
    public function sign(array $payload, $secret = null, string $algo = null);

    /**
     * Verify if token is valid.
     *
     * @param string      $token
     * @param string|null $secret The secret key
     * @param string      $algo
     *
     * @return \Swilen\Security\Token\Payload
     */
    public function verify(string $token, $secret = null, string $algo = null);
}
