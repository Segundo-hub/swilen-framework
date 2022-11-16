<?php

namespace Swilen\Validation\Rules;

class Uppercase extends BaseRule
{
    /**
     * The response message when is invalid.
     *
     * @var string
     */
    protected $message = 'The :attribute must be uppercase letters.';

    /**
     * Check value id valid with given atribute.
     *
     * @return bool
     */
    public function validate(): bool
    {
        if (!is_string($value = $this->value)) {
            return false;
        }

        return $value === mb_strtoupper($value, mb_detect_encoding($value));
    }
}
