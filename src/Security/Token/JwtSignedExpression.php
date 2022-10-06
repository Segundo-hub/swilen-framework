<?php

namespace Swilen\Security\Token;

class JwtSignedExpression
{
    /**
     * @var string
     */
    public $token;

    /**
     * @var int
     */
    public $iat;

    /**
     * @var int
     */
    public $exp;

    /**
     * @param \Swilen\Security\Token\JwtPayload $payload
     */
    public function __construct($token, JwtPayload $payload)
    {
        $this->token = $token;
        $this->iat   = $payload->iat();
        $this->exp   = $payload->expires();
    }

    public function __toString()
    {
        return $this->token;
    }
}
