<?php

namespace Swilen\Security\Token;

use Swilen\Security\Exception\JwtMalformedException;

class Decoder
{
    /**
     * The decoded header.
     *
     * @var \Swilen\Security\Token\Header
     */
    public $header;

    /**
     * The decoded payload.
     *
     * @var \Swilen\Security\Token\Payload
     */
    public $payload;

    /**
     * The signature of token.
     *
     * @var string
     */
    public $signature;

    /**
     * The token string decoded in base64 as string.
     *
     * @var string
     */
    protected $token;

    /**
     * Create new jwt decoder instance.
     *
     * @param string $token
     *
     * @return void
     */
    public function __construct(string $token)
    {
        $this->token = $token;
    }

    /**
     * Decode token and validate if it is safe.
     *
     * @return $this
     *
     * @throws \Swilen\Security\Exception\JwtMalformedException
     */
    public function decode()
    {
        [$header, $payload, $signature] = $this->decodeWithErrorHandling($this->token);

        $this->signature = $signature;

        $this->header = (new Header())->fromJson(Util::url_decode($header));
        $this->payload = (new Payload())->fromJson(Util::url_decode($payload));

        return $this;
    }

    /**
     * Decode and handle exceptions.
     *
     * @param string $token
     *
     * @return string[]
     *
     * @throws \Swilen\Security\Exception\JwtMalformedException
     */
    protected function decodeWithErrorHandling($token)
    {
        if (count($segmented = explode('.', $token)) === 3) {
            return $segmented;
        }

        throw new JwtMalformedException();
    }

    /**
     * Retrieve the token for decode
     *
     * @return string
     */
    public function token()
    {
        return $this->token;
    }
}
