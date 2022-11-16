<?php

namespace Swilen\Validation\Rules;

class RuleObject extends BaseRule
{
    /**
     * The response message when is invalid.
     *
     * @var string
     */
    protected $message = 'The :attribute must be a array';

    /**
     * Check value id valid with given atribute.
     *
     * @return bool
     */
    public function validate(): bool
    {
        if ($this->value === []) {
            return true;
        }

        $filtered = array_filter($this->value, function ($key) {
            return !is_int($key);
        }, ARRAY_FILTER_USE_KEY);

        return !empty($filtered);
    }
}
