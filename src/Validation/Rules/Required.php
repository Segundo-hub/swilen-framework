<?php

namespace Swilen\Validation\Rules;

class Required extends BaseRule
{
    /**
     * @var string
     */
    protected $message = 'The :attribute field is required.';

    /**
     * Check value id valid with given atribute.
     *
     * @return bool
     */
    public function validate(): bool
    {
        $value = $this->value;

        if (is_string($value)) {
            return mb_strlen(trim($value), 'UTF-8') > 0;
        }

        if (is_array($value)) {
            return count($value) > 0;
        }

        return !is_null($value);
    }
}
