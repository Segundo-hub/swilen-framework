<?php

namespace Swilen\Security\Token;

use JsonSerializable;
use Swilen\Security\TokenJsonable;

class JwtPayload implements TokenJsonable, JsonSerializable
{
    /**
     * The payload instance
     *
     * @var array<string, mixed>
     */
    protected $payload;

    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(array $payload = [])
    {
        $this->payload = $payload;
    }

    /**
     * @return string|false
     */
    public function toJson()
    {
        return JwtUtil::json_encode($this->payload);
    }

    public function fromJson($data)
    {
        $this->payload = (array) JwtUtil::json_decode($data);

        return $this;
    }

    /**
     * @return string
     */
    public function serialize()
    {
        return JwtUtil::url_encode($this->toJson());
    }

    /**
     * @return int|null
     */
    public function expires()
    {
        return $this->payload['exp'] ?? null;
    }

    /**
     * @return mixed
     */
    public function data()
    {
        return $this->payload['data'];
    }


    /**
     * @return int|null
     */
    public function iat()
    {
        return $this->payload['iat'];
    }

    public static function time($time)
    {
        return strtotime($time);
    }

    public function jsonSerialize()
    {
        return $this->payload;
    }
}
