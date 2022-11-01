<?php

namespace Swilen\Petiole\Facades;

/**
 * @method static \Swilen\Routing\Route get(string $uri, \Closure|array|string|null $action = null)
 * @method static \Swilen\Routing\Route post(string $uri, \Closure|array|string|null $action = null)
 * @method static \Swilen\Routing\Route put(string $uri, \Closure|array|string|null $action = null)
 * @method static \Swilen\Routing\Route delete(string $uri, \Closure|array|string|null $action = null)
 * @method static \Swilen\Routing\Route patch(string $uri, \Closure|array|string|null $action = null)
 * @method static \Swilen\Routing\Route options(string $uri, \Closure|array|string|null $action = null)
 * @method static \Swilen\Routing\Router group(array $atributes, string|callable $callback)
 *
 * @method static \Swilen\Routing\RouteRegister middleware(array|string|null $middleware)
 * @method static \Swilen\Routing\RouteRegister use(array|string|null $middleware)
 * @method static \Swilen\Routing\RouteRegister prefix(string  $prefix)
 * @method static \Swilen\Routing\RouteRegister where(array  $where)
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
