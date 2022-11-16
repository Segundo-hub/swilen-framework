<?php

namespace Swilen\Validation\Rules;

class Regex extends BaseRule
{
    /**
     * The response message when is invalid.
     *
     * @var string
     */
    protected $message = 'The :attribute is not valid format.';

    /**
     * The fillable parameters.
     *
     * @var array
     */
    protected $fillableParams = ['pattern'];

    /**
     * Check value id valid with given atribute.
     *
     * @return bool
     */
    public function validate(): bool
    {
        $this->requireParameters('pattern');

        $pattern = (string) $this->parameter('pattern')[0];

        return is_string($this->value) && (bool) preg_match($pattern, $this->value);
    }
}
