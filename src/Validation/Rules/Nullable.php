<?php

namespace Swilen\Validation\Rules;

class Nullable extends BaseRule
{
    /**
     * Check value id valid with given atribute.
     *
     * @return bool
     */
    public function validate(): bool
    {
        return true;
    }
}
