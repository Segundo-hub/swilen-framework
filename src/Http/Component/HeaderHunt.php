<?php

namespace Swilen\Http\Component;

class HeaderHunt implements \Countable, \IteratorAggregate
{
    /**
     * The headers collection.
     *
     * @var array<string, mixed>
     */
    protected $headers = [];

    /**
     * Create new HeaderHunt collection instance.
     *
     * @param array<string, mixed> $headers
     *
     * @return void
     */
    public function __construct(array $headers = [])
    {
        foreach ($headers as $key => $value) {
            $this->set($key, $value);
        }
    }

    /**
     * Return keys of headers as iterator.
     *
     * @return \ArrayIterator<int,string>
     */
    public function keys()
    {
        return new \ArrayIterator(array_keys($this->headers));
    }

    /**
     * Return values of headers as iterator.
     *
     * @return \ArrayIterator<int,string>
     */
    public function values()
    {
        return new \ArrayIterator(array_values($this->headers));
    }

    /**
     * Each headers with key and value.
     *
     * @param \Closure<string, string[]|string> $callback
     *
     * @return void
     */
    public function each(\Closure $callback)
    {
        foreach ($this->headers as $key => $value) {
            $callback($key, $value, $this);
        }
    }

    /**
     * Set new header to collection.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return void
     */
    public function set($key, $value)
    {
        $this->headers[$key] = $value;
    }

    /**
     * Remove a header from collection searched by key.
     *
     * @param string $key
     *
     * @return void
     */
    public function remove($key)
    {
        unset($this->headers[$key]);
    }

    /**
     * Remove given keys from collection.
     *
     * @param string|string[] $keys
     *
     * @return void
     */
    public function removeAt($keys)
    {
        $keys = is_array($keys) ? $keys : func_get_args();

        foreach ($keys as $value) {
            $this->remove($value);
        }
    }

    /**
     * Indicating whether the collection contains a certain header.
     *
     * @param string $key
     *
     * @return bool
     */
    public function has($key)
    {
        return isset($this->headers[$key]);
    }

    /**
     * Replace existing header in collection.
     *
     * @param string $key
     * @param mixed  $replaced
     *
     * @return bool
     */
    public function replace($key, $replaced)
    {
        $this->headers[$key] = $replaced;
    }

    /**
     * Get one header from collection or null if not exists.
     *
     * @param string     $key
     * @param mixed|null $default
     *
     * @return string|null
     */
    public function get(string $key, $default = null)
    {
        return $this->headers[$key] ?? $default;
    }

    /**
     * Retrieve all headers collection. Return empty array if is empty headers.
     *
     * @return array
     */
    public function all()
    {
        return $this->headers;
    }

    /**
     * Returns the number of headers.
     *
     * @return int
     */
    #[\ReturnTypeWillChange]
    public function count()
    {
        return \count($this->headers);
    }

    /**
     * Returns an iterator for headers.
     *
     * @return \ArrayIterator<string,string[]|string>
     */
    #[\ReturnTypeWillChange]
    public function getIterator()
    {
        return new \ArrayIterator($this->headers);
    }

    /**
     * Filter value from header.
     *
     * @param string             $key
     * @param string|number|null $default
     * @param int                $flags
     *
     * @return mixed
     */
    public function filter(string $key, $default = null, $flags = FILTER_SANITIZE_ENCODED)
    {
        return filter_var($this->get($key, $default), $flags);
    }
}
