<?php

namespace Swilen\Validation\Rules;

use Swilen\Shared\Support\Arr;
use Swilen\Validation\Exception\MissingRequiredParameterException;

abstract class BaseRule
{
    /**
     * The response message when is invalid.
     *
     * @var string
     */
    protected $message = 'The :attribute is not valid.';

    /**
     * The current value for checking.
     *
     * @var mixed
     */
    protected $value;

    /**
     * The current attribute validating.
     *
     * @var string
     */
    protected $attribute;

    /**
     * Aditional parameters passed.
     *
     * @var array<string, string>
     */
    protected $params = [];

    /**
     * The fillable parameters.
     *
     * @var array
     */
    protected $fillableParams = [];

    /**
     * Create new Rule Validator instance.
     *
     * @param mixed $value
     *
     * @return void
     */
    public function __construct($value = null, string $attribute = null)
    {
        $this->value     = $value;
        $this->attribute = $attribute;
    }

    /**
     * Set value for validate.
     *
     * @param mixed $value
     *
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Retrieve current value.
     *
     * @return mixed
     */
    public function value()
    {
        return $this->value;
    }

    /**
     * Set value for validate.
     *
     * @param string $atribute
     *
     * @return $this
     */
    public function setAttribute(string $attribute)
    {
        $this->attribute = $attribute;

        return $this;
    }

    /**
     * Set parameters for validate.
     *
     * @param mixed $params
     *
     * @return $this
     */
    public function setParameters($params)
    {
        if (!empty($fillable = $this->fillableParams)) {
            foreach ($fillable as $value) {
                $this->params[$value] = $params;
            }
        } else {
            $this->params = array_merge($this->params, $params);
        }

        return $this;
    }

    /**
     * Get message formatted.
     *
     * @return string
     */
    public function message()
    {
        $placeholders = [
            ':attribute' => $this->attribute,
            ':allowed' => implode(',', $this->parameter('allowed') ?? []),
        ];

        $message = $this->message;

        foreach ($placeholders as $key => $value) {
            $message = str_replace($key, $value, $message);
        }

        return $message;
    }

    /**
     * Get key of the rule.
     *
     * @return string
     */
    public function getKey()
    {
        return get_class($this);
    }

    /**
     * Get all params of the rule.
     *
     * @return array
     */
    public function parameters()
    {
        return $this->params;
    }

    /**
     * Get parameter from given $key, return null if it not exists.
     *
     * @param string $key
     *
     * @return mixed|null
     */
    public function parameter(string $key)
    {
        return Arr::get($this->params, $key);
    }

    /**
     * Retrieve required parameters.
     *
     * @param string|string[] $keys
     *
     * @return void
     */
    public function requireParameters($keys)
    {
        $params = is_array($keys) ? $keys : func_get_args();

        foreach ($params as $param) {
            if (!isset($this->params[$param]) && empty($this->params[$param])) {
                throw new MissingRequiredParameterException($param, $this->getKey());
            }
        }
    }

    /**
     * Validate value with given atribute.
     *
     * @return bool
     */
    abstract public function validate(): bool;
}
