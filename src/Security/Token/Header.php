<?php

namespace Swilen\Security\Token;

use Swilen\Security\Contract\TokenComponent;

final class Header implements TokenComponent
{
    /**
     * The collections of Token header.
     *
     * @var array<string, mixed>
     */
    private $headers;

    /**
     * New instance of Jwt header from given values of array.
     *
     * @param array<string,mixed> $headers
     *
     * @return void
     */
    public function __construct(array $headers = [])
    {
        $this->headers = $headers;
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function encode()
    {
        return Util::url_encode($this->toJson());
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function toJson()
    {
        return Util::json_encode($this->headers);
    }

    /**
     * {@inheritdoc}
     */
    public function fromJson($data)
    {
        $this->headers = (array) Util::json_decode($data);

        return $this;
    }
}
