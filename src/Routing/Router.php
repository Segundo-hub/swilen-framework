<?php

namespace Swilen\Routing;

use Swilen\Container\Container;
use Swilen\Contracts\Support\Arrayable;
use Swilen\Http\Request;
use Swilen\Http\Response;
use Swilen\Pipeline\Pipeline;

class Router
{
    /**
     * The container instance
     *
     * @var \Swilen\Container\Container
     */
    private $container;

    /**
     * Collection of routes
     *
     * @var \Swilen\Routing\RouteCollection
     */
    protected $routes;

    /**
     * Current Route
     *
     * @var \Swilen\Routing\Route
     */
    protected $currentRoute;

    /**
     * Current http Request
     *
     * @var \Swilen\Http\Request
     */
    protected $currentRequest;

    /**
     * Route group atributes
     *
     * @var array<string, mixed>
     */
    protected $groupStack = [];

    /**
     *  Http methods
     *
     * @var string[]
     */
    protected const SERVER_METHODS = [
        'OPTIONS', 'GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'TRACE', 'CONNECT', 'HEAD'
    ];

    /**
     * Create router instance and inject Service Container
     *
     * @param \Swilen\Container\Container|null $container
     *
     * @return void
     */
    public function __construct($container = null)
    {
        $this->container = $container ?: new Container;
        $this->routes    = new RouteCollection;
    }

    /**
     * Register a new GET route with the router.
     *
     * @param string $uri
     * @param array|string|callable|null $action
     *
     * @return \Swilen\Routing\Route
     */
    public function get(string $uri, $action)
    {
        return $this->addRoute("GET", $uri, $action);
    }

    /**
     * Register a new POST route with the router.
     *
     * @param string $uri
     * @param array|string|callable|null $action
     *
     * @return \Swilen\Routing\Route
     */
    public function post(string $uri, $action)
    {
        return $this->addRoute("POST", $uri, $action);
    }

    /**
     * Register a new PUT route with the router.
     *
     * @param string  $uri
     * @param array|string|callable|null $action
     *
     * @return \Swilen\Routing\Route
     */
    public function put(string $uri, $action)
    {
        return $this->addRoute("PUT", $uri, $action);
    }

    /**
     * Register a new PATCH route with the router.
     *
     * @param string  $uri
     * @param array|string|callable|null $action
     *
     * @return \Swilen\Routing\Route
     */
    public function path(string $uri, $action)
    {
        return $this->addRoute("PATCH", $uri, $action);
    }

    /**
     * Register a new DELETE route with the router.
     *
     * @param string $uri
     * @param array|string|callable|null $action
     *
     * @return \Swilen\Routing\Route
     */
    public function delete(string $uri, $action)
    {
        return $this->addRoute("DELETE", $uri, $action);
    }

    /**
     * Add new route to route collection
     *
     * @param string $method
     * @param string $uri
     * @param string|array|\Closure $action
     *
     * @return \Swilen\Routing\Route
     */
    protected function addRoute(string $method, string $uri, $action)
    {
        $route = $this->newRoute($method, $uri, $action);

        $this->routes->add($route);

        return $route;
    }

    /**
     * Create new Route
     *
     * @param string $method
     * @param string $uri
     * @param string|array|\Closure $action
     *
     * @return \Swilen\Routing\Route
     */
    private function newRoute(string $method, string $uri, $action)
    {
        return (new Route($method, $this->prefix($uri), $action))
            ->setContainer($this->container)
            ->setRouter($this);
    }

    /**
     * Create group routes with shared attributes
     *
     * @param array $atributes
     * @param \Closure|array|null $routes
     *
     * @return void
     */
    public function group(array $atributes, $routes)
    {
        foreach ($this->wrapGroupRoutes($routes) as $routeGroup) {
            $this->updateGroupStack($atributes);

            $this->loadRoutes($routeGroup);

            array_pop($this->groupStack);
        }
    }

    private function wrapGroupRoutes($routes)
    {
        return is_null($routes) ? [] : (is_array($routes) ? $routes : [$routes]);
    }

    /**
     * Determine if the router currently has a group stack.
     *
     * @return bool
     */
    public function hasGroupStack()
    {
        return !empty($this->groupStack);
    }

    /**
     * Update the group stack with the given attributes.
     *
     * @param array $attributes
     *
     * @return void
     */
    protected function updateGroupStack(array $attributes)
    {
        if ($this->hasGroupStack()) {
            $attributes = $this->mergeWithLastGroup($attributes);
        }

        $this->groupStack[] = $attributes;
    }

    /**
     * Merge the given array with the last group stack.
     *
     * @param array $new
     * @param bool $prependExistingPrefix
     *
     * @return array
     */
    public function mergeWithLastGroup($new, $prependExistingPrefix = true)
    {
        return RouteGroup::merge($new, end($this->groupStack), $prependExistingPrefix);
    }

    /**
     * Load routes
     *
     * @param \Closure|string $routes
     *
     * @return void
     */
    protected function loadRoutes($routes)
    {
        if ($routes instanceof \Closure) {
            $routes($this);
        } else {
            require $routes;
        }
    }

    protected function prefix($uri)
    {
        return '/' . trim(trim($this->getLastGroupPrefix(), '/') . '/' . trim($uri, '/'), '/') ?: '/';
    }

    public function getLastGroupPrefix()
    {
        if ($this->hasGroupStack()) {
            $atribute = end($this->groupStack);

            return $atribute['prefix'] ?: '';
        }

        return '';
    }

    /**
     * Handle incoming request and dispatch to route
     *
     * @param \Swilen\Http\Request $request
     * @return \Swilen\Http\Response
     */
    public function dispatch(Request $request)
    {
        $this->currentRequest = $request;

        return $this->dispatchToRoute($request);
    }

    /**
     * Send the current request to the route that matches the action
     *
     * @param \Swilen\Http\Request $request
     * @return \Swilen\Http\Response
     */
    protected function dispatchToRoute(Request $request)
    {
        $this->currentRoute = $route = $this->routes->match($request);

        $middlewares = $route->getMiddleware() ?? [];

        return (new Pipeline($this->container))
            ->from($request)
            ->through($middlewares)
            ->then(function ($request) use ($route) {
                return $this->prepareResponse(
                    $request,
                    $route->run()
                );
            });
    }

    public function prepareResponse(Request $request, $response)
    {
        return static::createResponse($request, $response);
    }

    public static function createResponse(Request $request, $response)
    {
        if ($response instanceof Response) {
            return $response->prepare($request);
        }

        if ($response instanceof Arrayable) {
            $response = $response->toArray();
        }

        return (new Response($response))->prepare($request);
    }

    public function __call($method, $arguments)
    {
        if (method_exists($this, $method) && !in_array($method, ['prefix', 'middleware'])) {
            return $this->{$method}(...$arguments);
        }

        return (new RouteRegister($this))->attribute($method, is_array($arguments) ? $arguments[0] : $arguments);
    }
}
