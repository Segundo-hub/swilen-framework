<?php

namespace Swilen\Security\Contract;

interface TokenComponent
{
    /**
     * Encode given value as safe url base64 encode.
     *
     * @return string
     */
    public function encode();

    /**
     * Serialize or transform token to json format with formatting options.
     *
     * @return string
     */
    public function toJson();

    /**
     * Create new TokenComponent instance from json.
     *
     * @param string $data
     *
     * @return $this
     */
    public function fromJson(string $data);
}
