<?php

namespace Swilen\Validation;

final class Validator
{
    /**
     * @var array<string, string>
     */
    private $errors = [];

    /**
     * @var array<string, string>
     */
    private $values = [];

    private const RULE_EMAIL = 'email';
    private const RULE_REQUIRED = 'required';
    private const RULE_DATE = 'date';
    private const RULE_NUMERIC = 'number';
    private const RULE_IN_LIST = 'in';

    private const RULE_ERRORS = [
        self::RULE_EMAIL => 'Invalid email',
        self::RULE_DATE  => 'Invalid date',
        self::RULE_REQUIRED => 'Invalid empty value',
        self::RULE_IN_LIST => 'Not found in list ',
        self::RULE_NUMERIC => 'Invalid number value'
    ];

    /**
     * @param array $values
     */
    public function __construct(array $values = [])
    {
        $this->errors = [];
        $this->values = $values;
    }

    /**
     * Main function for validate with validator rules
     *
     * @param array $rules
     *
     * @return $this
     */
    public function validate(array $rules)
    {
        foreach ($rules as $atribute => $_rules) {

            $internal_rules = is_array($_rules)
                ? $_rules
                : explode('|', $_rules);

            foreach ($internal_rules as $rule) {
                $value = key_exists($atribute, $this->values) ? $this->values[$atribute] : '';

                if ($rule === static::RULE_REQUIRED && !$value) {
                    $this->addError($atribute, static::RULE_ERRORS[static::RULE_REQUIRED]);
                }

                if ($rule === static::RULE_EMAIL && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addError($atribute, static::RULE_ERRORS[static::RULE_EMAIL]);
                }

                if ($rule === static::RULE_DATE && !strtotime($value)) {
                    $this->addError($atribute, static::RULE_ERRORS[static::RULE_DATE]);
                }

                if ($rule === static::RULE_NUMERIC && !is_numeric($value)) {
                    $this->addError($atribute, static::RULE_ERRORS[static::RULE_NUMERIC]);
                }

                if (strpos($rule, ':') !== false) {
                    [$rule, $expresion] = explode(':', $rule);
                    if ($rule === static::RULE_IN_LIST && !in_array($value, explode(',', $expresion))) {
                        $this->addError($atribute, static::RULE_ERRORS[static::RULE_IN_LIST] . $expresion);
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Add new error to list
     *
     * @param mixed $key
     * @param string $error
     */
    private function addError($key, string $error)
    {
        $this->errors[$key] = $error;
    }

    public function errors($key = null)
    {
        if (is_null($key)) {
            return $this->errors;
        }

        return isset($this->errors[$key]) ? $this->errors[$key] : null;
    }

    /**
     * Return if the validation is safe
     *
     * @return bool
     */
    public function isSafe()
    {
        return count($this->errors) === 0;
    }

    /**
     * Return if the validation is failed
     *
     * @return bool
     */
    public function fails()
    {
        return !$this->isSafe();
    }

    /**
     * Set validatable data
     *
     * @param object|array $data
     * @return $this
     */
    public function from($data)
    {
        $this->errors = [];
        $this->values = (array) $data;

        return $this;
    }

    /**
     * Set validatable data
     *
     * @param object|array $data
     * @return $this
     */
    public static function make($data, array $rules = [])
    {
        if (!empty($rules)) {
            return (new static($data))->validate($rules);
        }

        return new static($data);
    }

    /**
     * Get values storaged dynamicaly
     *
     * @param string|int $key
     * @return string|null
     */
    public function __get($key)
    {
        return $this->values[$key];
    }
}
