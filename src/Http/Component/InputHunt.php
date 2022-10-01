<?php

namespace Swilen\Http\Component;

final class InputHunt extends ParameterHunt
{
    /**
     * Create new InputHunt instance and store $params
     *
     * @param array $params
     *
     * @return void
     */
    public function __construct(array $params = [])
    {
        $this->decoded($params);
    }

    /**
     * Store params with decoded
     *
     * @param array $params
     */
    public function decoded(array $params = [])
    {
        foreach ($params as $key => $value) {
            parent::set($key, $this->cleanValue($key, $value));
        }
    }

    /**
     * Store params with filtered
     *
     * @param array $params
     */
    public function filtered(array $params = [], $via)
    {
        foreach ($params as $key => $_value) {
            parent::set($key, $this->filterInputHunt($via, $key));
        }
    }

    protected function cleanValue($key, $value)
    {
        if (is_array($value)) {
            return $this->cleanArray($value, $key);
        }

        return $this->transform($key, $value);
    }

    protected function cleanArray(array $data)
    {
        foreach ($data as $key => $value) {
            $data[$key] = $this->cleanValue($key, $value);
        }

        return $data;
    }

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

    /**
     * Filter params
     *
     * @param mixed $target
     * @param int $flags
     */
    protected function filterInputHunt($target, int $flags = FILTER_SANITIZE_FULL_SPECIAL_CHARS | FILTER_SANITIZE_ENCODED)
    {
        $value = filter_var($target, $flags);

        return rawurldecode($value);
    }
}
