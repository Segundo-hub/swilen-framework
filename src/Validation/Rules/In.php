<?php

namespace Swilen\Validation\Rules;

class In extends BaseRule
{
    /**
     * The response message when is invalid.
     *
     * @var string
     */
    protected $message = 'The :attribute must be a includes in :allowed.';

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
        $this->requireParameters($this->fillableParams);

        return in_array($this->value, $this->parameter('allowed'));
    }
}
