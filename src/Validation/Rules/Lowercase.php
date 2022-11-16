<?php

namespace Swilen\Validation\Rules;

class Lowercase extends BaseRule
{
    /**
     * The response message when is invalid.
     *
     * @var string
     */
    protected $message = 'The :attribute must be lowercase letters.';

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

        return $value === mb_strtolower($value, mb_detect_encoding($value));
    }
}
