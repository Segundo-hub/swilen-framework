<?php

namespace Swilen\Security\Token;

class JwtSignedExpression
{
    /**
     * @var string
     */
    public $token;

    /**
     * @var \Swilen\Security\Token\JwtPayload
     */
    public $payload;

    /**
     * @param string $token
     * @param \Swilen\Security\Token\JwtPayload $payload
     */
    public function __construct($token, JwtPayload $payload)
    {
        $this->token   = $token;
        $this->payload = $payload;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->token;
    }
}
