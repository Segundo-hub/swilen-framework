<?php

namespace Swilen\Validation\Rules;

class Url extends BaseRule
{
    /**
     * The response message when is invalid.
     *
     * @var string
     */
    protected $message = 'The :attribute must be a URL.';

    /**
     * Check value id valid with given atribute.
     *
     * @return bool
     */
    public function validate(): bool
    {
        if (!is_string($this->value)) {
            return false;
        }

        return $this->filterValidateUrl($this->value);
    }

    /**
     * Validate url basic.
     *
     * @param string $url
     *
     * @return bool
     */
    public function filterValidateUrl($url)
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }
}
