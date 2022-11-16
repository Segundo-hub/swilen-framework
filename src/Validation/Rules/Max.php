<?php

namespace Swilen\Validation\Rules;

/**
 * @codeCoverageIgnore
 */
class Max extends BaseRule
{
    /**
     * The response message when is invalid.
     *
     * @var string
     */
    protected $message = 'The :attribute must max of :allowed';

    /**
     * Check value id valid with given atribute.
     * !TODO.
     *
     * @return bool
     */
    public function validate(): bool
    {
        return false;
    }
}
