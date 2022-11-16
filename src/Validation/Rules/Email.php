<?php

namespace Swilen\Validation\Rules;

class Email extends BaseRule
{
    /**
     * The response message when is invalid.
     *
     * @var string
     */
    protected $message = 'The :attribute is not valid email.';

    /**
     * Check value id valid with given atribute.
     *
     * @return bool
     */
    public function validate(): bool
    {
        return is_string($this->value) && filter_var($this->value, FILTER_VALIDATE_EMAIL) !== false;
    }
}
