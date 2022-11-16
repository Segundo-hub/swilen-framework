<?php

namespace Swilen\Validation\Rules;

class Number extends BaseRule
{
    /**
     * @var string
     */
    protected $message = 'The :attribute must be numeric.';

    /**
     * Check value id valid with given atribute.
     *
     * @return bool
     */
    public function validate(): bool
    {
        return is_numeric($this->value);
    }
}
