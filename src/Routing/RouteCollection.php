<?php

namespace Swilen\Routing;

use Swilen\Http\Exception\HttpMethodNotAllowedException;
use Swilen\Http\Exception\HttpNotFoundException;
use Swilen\Http\Request;
use Swilen\Shared\Support\Arrayable;

class RouteCollection implements Arrayable
{
    /**
     * The routes collection.
     *
     * @var array<string, \Swilen\Routing\Route[]>
     */
    protected $routes = [];

    /**
     * The current matched route.
     *
     * @var \Swilen\Routing\Route|null
     */
    protected $current;

    /**
     * Collection of http verbs alloweb.
     *
     * @var string[]
     */
    protected $allowedHttpVerbs = [
        'get', 'post', 'options', 'put', 'delete', 'any', 'patch',
    ];

    /**
     * The application container instance.
     *
     * @var \Swilen\Container\Container;
     */
    protected $container;

    /**
     * The router instance used by the routes collection.
     *
     * @var \Swilen\Routing\Router
     */
    protected $router;

    /**
     * Add new Route to collection.
     *
     * @param \Swilen\Routing\Route $route
     *
     * @return \Swilen\Routing\Route
     */
    public function add(Route $route)
    {
        $this->routes[$route->getMethod()][] = $route;

        return $route;
    }

    /**
     * Find and match route from current method and current request action.
     *
     * @param \Swilen\Http\Request $request
     *
     * @return \Swilen\Routing\Route
     *
     * @throws \Swilen\Http\Exception\HttpMethodNotAllowedException
     * @throws \Swilen\Http\Exception\HttpNotFoundException
     */
    public function match(Request $request)
    {
        $routes = $this->get($request->getMethod());

        foreach ($routes as $route) {
            if ($route->matches($request->getPathInfo())) {
                $this->current = $route;
                break;
            }
        }

        if ($this->current !== null) {
            return $this->current;
        }

        throw new HttpNotFoundException();
    }

    /**
     * @param string $method
     *
     * @return \Swilen\Routing\Route[]
     *
     * @throws \Swilen\Http\Exception\HttpMethodNotAllowedException
     */
    protected function get(string $method)
    {
        if (isset($this->routes[$method])) {
            return $this->routes[$method];
        }

        return $this->methodNotAllowed($method);
    }

    /**
     * Handle exepcion if method not allowed in route collection.
     *
     * @param string $method
     *
     * @throws \Swilen\Http\Exception\HttpMethodNotAllowedException
     */
    private function methodNotAllowed(string $method)
    {
        throw new HttpMethodNotAllowedException(sprintf('%s Method Not Allowed. %s Methods allowed', strtoupper($method), implode(',', array_keys($this->routes))));
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        $routes = [];

        foreach ($this->routes as $method => $route) {
            $routes[$method] = array_map(function ($e) {
                return $e->toArray();
            }, $route);
        }

        return $routes;
    }
}
