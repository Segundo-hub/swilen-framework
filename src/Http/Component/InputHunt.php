<?php

namespace Swilen\Http\Component;

final class InputHunt extends ParameterHunt
{
    /**
     * The names of the attributes that should not be trimmed.
     *
     * @var array<int, string>
     */
    protected $prevent = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Create new InputHunt instance and store $params.
     *
     * @param array $params
     *
     * @return void
     */
    public function __construct(array $params = [])
    {
        $this->params = [];

        $this->addInputs($params);
    }

    /**
     * Add given parameters array to params store.
     *
     * @param array $params
     *
     * @return void
     */
    protected function addInputs(array $params = [])
    {
        foreach ($params as $key => $value) {
            parent::set($key, $this->cleanValue($key, $value));
        }
    }

    /**
     * Clean the given input values.
     *
     * @param string|in $key
     * @param mixed     $value
     *
     * @return mixed
     */
    protected function cleanValue($key, $value)
    {
        if (is_array($value)) {
            return $this->cleanArray($value);
        }

        if (in_array($key, $this->prevent, true)) {
            return $value;
        }

        return $this->transform($key, $value);
    }

    /**
     * Clean the given input values as array.
     *
     * @param array $data
     *
     * @return array
     */
    protected function cleanArray(array $data)
    {
        foreach ($data as $key => $value) {
            $data[$key] = $this->cleanValue($key, $value);
        }

        return $data;
    }

    /**
     * Tranform values to primitive.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    protected function transform($key, $value)
    {
        if (in_array($value, ['', 'null', null], true)) {
            return $value = null;
        }

        if ($value === 'true') {
            return $value = true;
        }

        if ($value === 'false') {
            return $value = false;
        }

        return is_string($value) ? trim($value) : $value;
    }
}
