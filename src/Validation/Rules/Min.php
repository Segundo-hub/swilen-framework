<?php

namespace Swilen\Validation\Rules;

/**
 * @codeCoverageIgnore
 */
class Min extends BaseRule
{
    /**
     * The response message when is invalid.
     *
     * @var string
     */
    protected $message = 'The :attribute min of :allowed';

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
