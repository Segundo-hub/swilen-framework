<?php

namespace Swilen\Security\Token;

use Swilen\Security\TokenJsonable;

class JwtHeader implements TokenJsonable
{
    /**
     * @var array<string, mixed>
     */
    protected $headers;

    public function __construct(array $headers)
    {
        $this->headers = $headers;
    }

    /**
     * @return string
     */
    public function serialize()
    {
        return JwtUtil::url_encode($this->toJson());
    }

    /**
     * @return string
     */
    public function toJson()
    {
        return JwtUtil::json_encode($this->headers);
    }

    /**
     * @param string $data
     * @return self
     */
    public function fromJson($data)
    {
        $this->headers = (array) JwtUtil::json_decode($data);

        return $this;
    }
}
