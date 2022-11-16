<?php

namespace Swilen\Validation\Rules;

use Swilen\Validation\Regex;

class Alpha extends BaseRule
{
    /**
     * The response message when is invalid.
     *
     * @var string
     */
    protected $message = 'The :attribute only allows alphabet characters.';

    /**
     * Check value id valid with given atribute.
     *
     * @return bool
     */
    public function validate(): bool
    {
        return is_string($this->value) && preg_match(Regex::ALPHA, $this->value);
    }
}
