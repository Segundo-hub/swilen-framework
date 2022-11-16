<?php

namespace Swilen\Validation\Rules;

class Date extends BaseRule
{
    /**
     * The response message when is invalid.
     *
     * @var string
     */
    protected $message = 'The :attribute must be a date';

    /**
     * The fillable parameters.
     *
     * @var array
     */
    protected $fillableParams = ['format'];

    /**
     * Check value id valid with given atribute.
     *
     * @return bool
     */
    public function validate(): bool
    {
        if (!is_string($value = $this->value)) {
            return false;
        }

        if (!is_null($format = $this->parameter('format')[0] ?? null)) {
            $date = \DateTime::createFromFormat($format, $value);

            return $this->validated($date, $value, $format);
        }

        return (bool) strtotime($value);
    }

    /**
     * Internal validate date within format.
     *
     * @param \DateTime|false $date
     * @param string          $value
     * @param string          $format
     *
     * @return bool
     */
    private function validated($date, $value, $format)
    {
        return $date && $date->format($format) === $value && !array_sum($date::getLastErrors());
    }
}
