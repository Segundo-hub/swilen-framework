<?php

namespace Swilen\Validation\Rules;

/**
 * @codeCoverageIgnore
 */
class Different extends BaseRule
{
    /**
     * The response message when is invalid.
     *
     * @var string
     */
    protected $message = 'The :attribute must be a boolean.';

    /**
     * Check value id valid with given atribute.
     *!TODO.
     *
     * @return bool
     */
    public function validate(): bool
    {
        return is_bool($this->value);
    }
}
