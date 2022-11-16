<?php

namespace Swilen\Routing;

use Swilen\Http\Common\Http;
use Swilen\Http\Exception\HttpMethodNotAllowedException;
use Swilen\Http\Exception\HttpNotFoundException;
use Swilen\Http\Request;
use Swilen\Http\Response;
use Swilen\Shared\Support\Arrayable;

class RouteCollection implements Arrayable, \IteratorAggregate, \Countable
{
    /**
     * The routes collection.
     *
     * @var array<string, \Swilen\Routing\Route[]>
     */
    protected $routes = [];

    /**
     * Count all routes.
     *
     * @var int
     */
    protected $count = 0;

    /**
     * Methods registered in the collection.
     *
     * @var string[]
     */
    protected $methods = [];

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
        $this->addRouteToCollection($route);

        return $route;
    }

    /**
     * Add route to collection adn increment count of routes.
     *
     * @param \Swilen\Routing\Route $route
     *
     * @return void
     */
    protected function addRouteToCollection(Route $route)
    {
        $this->routes[$route->getMethod()][] = $route;

        ++$this->count;
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

        // We try if we can find a matching route for this current request
        $route = $this->matchRoutes($routes, $request);

        return $this->handleMatchedRoute($request, $route);
    }

    /**
     * @param string $method
     *
     * @return \Swilen\Routing\Route[]
     */
    protected function get(string $method)
    {
        return $this->routes[$method] ?? [];
    }

    /**
     * Match route in all routes match by method.
     *
     * @param \Swilen\Routing\Route[] $routes
     * @param \Swilen\Http\Request    $request
     *
     * @return \Swilen\Routing\Route|null
     */
    protected function matchRoutes(array $routes, Request $request)
    {
        foreach ($routes as $route) {
            if ($route->matches($request->getPathInfo())) {
                return $route;
            }
        }
    }

    /**
     * Handle the matched route.
     *
     * @param \Swilen\Http\Request       $request
     * @param \Swilen\Routing\Route|null $route
     *
     * @return \Swilen\Routing\Route
     */
    protected function handleMatchedRoute(Request $request, $route)
    {
        if (!is_null($route)) {
            return $route;
        }

        if (!empty($methods = $this->findAllowedMethods($request))) {
            return $this->handleRouteForMethods($request, $methods);
        }

        throw new HttpNotFoundException();
    }

    /**
     * Find allowed methods for this URI by matching against all other HTTP methods as well.
     *
     * @param \Swilen\Http\Request $request
     *
     * @return string[]
     */
    protected function findAllowedMethods(Request $request)
    {
        $methods = array_diff(Router::HTTP_METHODS, [$request->getMethod()]);

        return array_filter($methods, function ($method) use ($request) {
            return !is_null($this->matchRoutes($this->get($method), $request));
        });
    }

    /**
     * Manage request method is another in route collection.
     *
     * @param \Swilen\Http\Request $request
     * @param string[]             $methods
     *
     * @return \Swilen\Routing\Route|null
     *
     * @throws \Swilen\Http\Exception\HttpMethodNotAllowedException
     */
    protected function handleRouteForMethods(Request $request, array $methods = [])
    {
        if ($request->getMethod() === Http::METHOD_OPTIONS) {
            return new Route(Http::METHOD_OPTIONS, $request->getPathInfo(), function () use ($methods) {
                return new Response('', 200, ['Allow' => implode(', ', $methods)]);
            });
        }

        $this->methodNotAllowed($request->getMethod(), $methods);
    }

    /**
     * Handle exepcion if method not allowed in route collection.
     *
     * @param string   $method
     * @param string[] $methods
     *
     * @throws \Swilen\Http\Exception\HttpMethodNotAllowedException
     */
    protected function methodNotAllowed(string $method, array $methods = [])
    {
        $message = sprintf('The %s method is not supported. Must be one of: %s.', $method, $methods = strtoupper(implode(', ', $methods)));

        throw (new HttpMethodNotAllowedException($message))->withHeader('Allow', $methods);
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

    /**
     * Get count all routes.
     *
     * @return int
     */
    #[\ReturnTypeWillChange]
    public function count()
    {
        return $this->count;
    }

    /**
     * Get iterator for all routes.
     *
     * @return \ArrayIterator<string,\Swilen\Routing\Route[]>
     */
    #[\ReturnTypeWillChange]
    public function getIterator()
    {
        return new \ArrayIterator($this->routes);
    }
}
