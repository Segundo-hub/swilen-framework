<?php

namespace Swilen\Shared\Support;

use JsonSerializable as CoreJsonSerializable;

interface JsonSerializable extends CoreJsonSerializable
{
    /**
     * {@inheritdoc}
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize();
}
