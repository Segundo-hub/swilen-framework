<?php

namespace Swilen\Http\Component;

use Swilen\Shared\Support\Enumerable;

class HeaderHunt implements Enumerable
{
    /**
     * The headers collection
     *
     * @var array<string, mixed>
     */
    protected $headers = [];

    /**
     * Create new HeaderHunt collection instance
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
     * Set new header to collection
     *
     * @param string $key
     * @param mixed $value
     *
     * @return void
     */
    public function set($key, $value)
    {
        $this->headers[$key] = $value;
    }

    /**
     * alias for set new header to collection
     *
     * @param string $key
     * @param mixed $value
     *
     * @return void
     */
    public function add($key, $value)
    {
        $this->set($key, $value);
    }

    /**
     * Remove a header from collection searched by key
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
     * Check if header is exists in collection
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
     * Replace existing header in collection
     *
     * @param string $key
     * @param mixed $replaced
     *
     * @return bool
     */
    public function replace($key, $replaced)
    {
        $this->headers[$key] = $replaced;
    }

     /**
     * Get one header from collection or null if not exists
     *
     * @param string $key
     * @param mixed $default
     *
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        return $this->has($key) ? $this->headers[$key] : $default;
    }

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function all()
    {
        return $this->headers;
    }

    /**
     * Filter value from header
     *
     * @param string $key
     * @param string|number|null $default
     * @param int $flags
     *
     * @return mixed
     */
    public function filter(string $key, $default = null, $flags = FILTER_SANITIZE_ENCODED)
    {
        return filter_var($this->get($key, $default), $flags);
    }
}
