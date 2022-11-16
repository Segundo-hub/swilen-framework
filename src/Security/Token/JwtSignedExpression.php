<?php

namespace Swilen\Security\Token;

class JwtSignedExpression
{
    /**
     * The token generated encoded base64.
     *
     * @var string
     */
    public $token;

    /**
     * @var \Swilen\Security\Token\Payload
     */
    public $payload;

    /**
     * @param string                         $token
     * @param \Swilen\Security\Token\Payload $payload
     *
     * @return void
     */
    public function __construct($token, Payload $payload)
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
