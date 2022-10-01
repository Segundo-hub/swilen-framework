<?php

namespace Swilen\Routing;

final class RouteRegister
{
    /**
     * @var string|array<string, string>
     */
    private $attributes = [];

    /**
     * @var \Swilen\Routing\Router
     */
    private $router;
    /**
     * @param \Swilen\Routing\Router $router
     *
     * @return void
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * @param string $key
     * @param string $value
     *
     * @return $this
     */
    public function attribute($key, $value)
    {
        $this->attributes[$key] = $value;

        return $this;
    }

    /**
     * Group route callback
     *
     * @param string|\Closure $callback
     */
    public function group($callback)
    {
        return $this->router->group($this->attributes, $callback);
    }
}
