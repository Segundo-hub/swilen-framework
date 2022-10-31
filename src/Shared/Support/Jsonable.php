<?php

namespace Swilen\Shared\Support;

interface Jsonable
{
    /**
     * Transform values to json
     *
     * @param int $options
     *
     * @return string
     */
    public function toJson($options = 0);
}
