<?php

namespace Swilen\Contracts\Support;

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
