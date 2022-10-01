<?php

namespace Swilen\Security;

interface TokenJsonable
{
    /**
     * Serialize or transform token to json format with formatting options
     *
     * @return string|false
     */
    public function toJson();

    /**
     * Create new Jwt instrance from json
     *
     * @param string $data
     *
     * @return $this
     */
    public function fromJson(string $data);
}
