<?php

namespace Swilen\Validation\Rules;

class NotIn extends BaseRule
{
    /**
     * The response message when is invalid.
     *
     * @var string
     */
    protected $message = 'The :attribute is not allowing :allowed.';

    /**
     * The fillable parameters.
     *
     * @var array
     */
    protected $fillableParams = ['allowed'];

    /**
     * Check value id valid with given atribute.
     *
     * @return bool
     */
    public function validate(): bool
    {
        $this->requireParameters('allowed');

        return !in_array($this->value, $this->parameter('allowed'));
    }
}
