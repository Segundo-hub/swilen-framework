<?php

namespace Swilen\Security\Token;

use Swilen\Security\Contract\TokenComponent;

final class Payload implements TokenComponent, \JsonSerializable
{
    /**
     * The payload array.
     *
     * @var array<string, mixed>
     */
    private $payload = [];

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
        return Util::json_encode($this->payload);
    }

    /**
     * {@inheritdoc}
     */
    public function fromJson($data)
    {
        $this->payload = (array) Util::json_decode($data);

        return $this;
    }

    /**
     * @return string
     */
    public function encode()
    {
        return Util::url_encode($this->toJson());
    }

    /**
     * @return int
     */
    public function expires()
    {
        return $this->payload['exp'];
    }

    /**
     * Retrieve data from payload.
     *
     * @return mixed
     */
    public function data()
    {
        return $this->payload['data'] ?? [];
    }

    /**
     * @return int|null
     */
    public function iat()
    {
        return $this->payload['iat'];
    }

    /**
     * Specify data which should be serialized to JSON.
     *
     * @return mixed
     */
    public function jsonSerialize()
    {
        return $this->payload;
    }
}
