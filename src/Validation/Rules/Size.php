<?php

namespace Swilen\Validation\Rules;

/**
 * @codeCoverageIgnore
 */
class Size extends BaseRule
{
    /**
     * The response message when is invalid.
     *
     * @var string
     */
    protected $message = 'The :attribute must be a IP format';

    /**
     * Check value id valid with given atribute.
     * !TODO.
     *
     * @return bool
     */
    public function validate(): bool
    {
        return is_string($this->value) && ($this->value === strtolower($this->value));
    }
}
