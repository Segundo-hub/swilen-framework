<?php

namespace Swilen\Validation\Rules;

/**
 * @codeCoverageIgnore
 */
class Same extends BaseRule
{
    /**
     * The response message when is invalid.
     *
     * @var string
     */
    protected $message = 'The :attribute must be same :another-attribute.';

    /**
     * Check value id valid with given atribute.
     * !TODO.
     *
     * @return bool
     */
    public function validate(): bool
    {
        return is_string($this->value) && ($this->value === strtoupper($this->value));
    }
}
