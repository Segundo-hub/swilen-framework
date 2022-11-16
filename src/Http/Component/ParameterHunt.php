<?php

namespace Swilen\Http\Component;

class ParameterHunt implements \Countable, \IteratorAggregate
{
    /**
     * The params storage.
     *
     * @var array<string|int, mixed>
     */
    protected $params = [];

    /**
     * Create new ParameterHunt instance with default params.
     *
     * @param array $param
     *
     * @return void
     */
    public function __construct(array $params = [])
    {
        $this->params = $params;
    }

    /**
     * Replace params with new Set.
     *
     * @param array $replaced
     *
     * @return void
     */
    public function replace(array $replaced)
    {
        $this->params = $replaced;
    }

    /**
     * {@inheritdoc} Get all parameters stored
     *
     * @return array<string|int, mixed>
     */
    public function all()
    {
        return $this->params;
    }

    /**
     * Returns the parameter keys.
     *
     * @return int[]|string[] an array of all the keys in input
     */
    public function keys()
    {
        return array_keys($this->params);
    }

    /**
     * Returns the parameter values.
     *
     * @return int[]|string[] an array of all the values in input
     */
    public function values()
    {
        return array_values($this->params);
    }

    /**
     * Adds params to params set.
     *
     * @param array $params
     *
     * @return void
     */
    public function add(array $params = [])
    {
        $this->params = array_replace($this->params, $params);
    }

    /**
     * Get one value or default if not exists.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        return $this->has($key) ? $this->params[$key] : $default;
    }

    /**
     * Insert one value to params set.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return void
     */
    public function set(string $key, $value)
    {
        $this->params[$key] = $value;
    }

    /**
     * Returns true if the parameter is defined.
     *
     * @param string $key
     *
     * @return bool
     */
    public function has(string $key)
    {
        return key_exists($key, $this->params);
    }

    /**
     * Removes a parameter.
     */
    public function remove(string $key)
    {
        unset($this->params[$key]);
    }

    /**
     * Returns the number of input params.
     *
     * @return int
     */
    #[\ReturnTypeWillChange]
    public function count()
    {
        return \count($this->params);
    }

    /**
     * Returns an iterator for input params.
     *
     * @return \ArrayIterator<string, list<string|null>>
     */
    #[\ReturnTypeWillChange]
    public function getIterator()
    {
        return new \ArrayIterator($this->params);
    }
}
