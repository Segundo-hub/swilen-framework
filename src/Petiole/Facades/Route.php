<?php

namespace Swilen\Petiole\Facades;

/**
 * @method static \Swilen\Routing\Route get(string $uri, array|string|callable|null $action = null)
 * @method static \Swilen\Routing\Route post(string $uri, array|string|callable|null $action = null)
 * @method static \Swilen\Routing\Route put(string $uri, array|string|callable|null $action = null)
 * @method static \Swilen\Routing\Route delete(string $uri, array|string|callable|null $action = null)
 * @method static \Swilen\Routing\Route patch(string $uri, array|string|callable|null $action = null)
 * @method static \Swilen\Routing\RouteRegister prefix(string $name)
 * @method static \Swilen\Routing\Router group(array $atributes, string|callable $callback)
 *
 * @see \Swilen\Routing\Router
 */

class Route extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeName()
    {
        return "router";
    }
}
