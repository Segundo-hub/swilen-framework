<?php

namespace Swilen\Security\Token;

use Swilen\Security\Exception\JwtMalformedException;

class JwtDecoder
{
    /**
     * @var string
     */
    public $header;

    /**
     * @var string
     */
    public $payload;

    /**
     * @var string
     */
    public $signature;

    public function __construct(string $token)
    {
        $this->decode($token);
    }

    /**
     * Decode token provided as string
     *
     * @param string $token
     */
    protected function decode($token)
    {
        [$header, $payload, $signature] = $this->decodeAndHandleException($token);

        $this->header = base64_decode($header);
        $this->payload =  base64_decode($payload);
        $this->signature = $signature;
    }

    /**
     * Handle decode exception
     *
     * @param string $token
     *
     * @return array
     */
    protected function decodeAndHandleException($token)
    {
        if (count($segmented = explode('.', $token)) === 3) {
            return $segmented;
        }

        throw new JwtMalformedException();
    }
}
