<?php

namespace Swilen\Http\Component;

use Swilen\Contracts\Support\Enumerable;

class HeaderHunt implements Enumerable
{
    /**
     * The headers collection
     *
     * @var array<string, mixed>
     */
    protected $headers = [];

    /**
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

    public function set($key, $value)
    {
        $this->headers[$key] = $value;
    }

    public function add($key, $value)
    {
        $this->set($key, $value);
    }


    public function remove($key)
    {
        unset($this->headers[$key]);
    }

    public function has($key)
    {
        return isset($this->headers[$key]);
    }

    public function replace($key, $replaced)
    {
        $this->headers[$key] = $replaced;
    }

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
