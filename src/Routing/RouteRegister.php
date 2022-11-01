<?php

namespace Swilen\Routing;

final class RouteRegister
{
    /**
     * The router instance for route register
     *
     * @var \Swilen\Routing\Router
     */
    private $router;

    /**
     * The attributes to pass on to the router.
     *
     * @var array<string, mixed>
     */
    private $attributes = [];

    /**
     * The attributes that can be set through this class.
     *
     * @var string[]
     */
    protected $allowedAttributes = [
        'as',
        'middleware',
        'use',
        'prefix',
        'where',
    ];

    /**
     * Create new RouteRegister instance
     *
     * @param \Swilen\Routing\Router $router
     *
     * @return void
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * Set the value for a given attribute.
     *
     * @param string $key
     * @param mixed $value
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function attribute($key, $value)
    {
        if (!in_array($key, $this->allowedAttributes)) {
            throw new \InvalidArgumentException("Attribute [{$key}] does not exist.");
        }

        $this->attributes[$key] = $value;

        return $this;
    }

    /**
     * Register middleware in current $attributes.
     *
     * @param string|array $middleware
     *
     * @return $this
     */
    public function use($middleware)
    {
        $this->attributes['middleware'] = $middleware;

        return $this;
    }

    /**
     * Create a route group with shared attributes.
     *
     * @param string|\Closure $callback
     *
     * @return void
     */
    public function group($callback)
    {
        $this->router->group($this->attributes, $callback);
    }

    /**
     * Dynamically handle calls into the route registrar.
     *
     * @param string $method
     * @param array $params
     *
     * @return $this
     * @throws \BadMethodCallException
     */
    public function __call($method, $params)
    {
        if (in_array($method, $this->allowedAttributes)) {
            return $this->attribute($method, key_exists(0, $params) ? $params[0] : true);
        }

        throw new \BadMethodCallException(sprintf(
            'Method %s::%s does not exist.',
            static::class,
            $method
        ));
    }
}
