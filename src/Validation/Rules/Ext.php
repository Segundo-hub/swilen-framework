<?php

namespace Swilen\Validation\Rules;

use Swilen\Validation\Regex;

/**
 * @codeCoverageIgnore
 */
class Ext extends BaseRule
{
    /**
     * The response message when is invalid.
     *
     * @var string
     */
    protected $message = 'The :attribute must be a extension :list';

    /**
     * Check value id valid with given atribute.
     * !TODO.
     *
     * @return bool
     */
    public function validate(): bool
    {
        return (bool) preg_match(Regex::ALPHA, $this->value);
    }
}
