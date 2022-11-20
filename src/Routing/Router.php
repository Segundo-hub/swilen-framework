<?php

namespace Swilen\Routing;

use Swilen\Container\Container;
use Swilen\Http\Common\Http;
use Swilen\Http\Common\HttpTransformJson;
use Swilen\Http\Request;
use Swilen\Http\Response;
use Swilen\Http\Response\JsonResponse;
use Swilen\Pipeline\Pipeline;
use Swilen\Routing\Contract\RouterContract;

class Router implements RouterContract
{
    /**
     * The application container instance.
     *
     * @var \Swilen\Container\Container
     */
    private $container;

    /**
     * Collection of routes.
     *
     * @var \Swilen\Routing\RouteCollection
     */
    protected $routes;

    /**
     * Current Route matched.
     *
     * @var \Swilen\Routing\Route|null
     */
    protected $current;

    /**
     * Http request instance.
     *
     * @var \Swilen\Http\Request
     */
    protected $request;

    /**
     * Route group atributes.
     *
     * @var array<string, mixed>
     */
    protected $groupStack = [];

    /**
     * The all server HTTP methods.
     *
     * @var string[]
     */
    protected const ALL_HTTP_METHODS = [
        'OPTIONS', 'GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'TRACE', 'CONNECT', 'HEAD',
    ];

    /**
     * The server HTTP methods.
     *
     * @var string[]
     */
    public const HTTP_METHODS = ['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'];

    /**
     * Create new Router instance.
     *
     * @param \Swilen\Container\Container|null $container
     *
     * @return void
     */
    public function __construct($container = null)
    {
        $this->container = $container ?: new Container();
        $this->routes    = new RouteCollection();
    }

    /**
     * Register a new GET route with the router.
     *
     * @param string                $uri
     * @param array|string|callable $action
     *
     * @return \Swilen\Routing\Route
     */
    public function get(string $uri, $action)
    {
        return $this->addRoute('GET', $uri, $action);
    }

    /**
     * Register a new POST route with the router.
     *
     * @param string                $uri
     * @param array|string|callable $action
     *
     * @return \Swilen\Routing\Route
     */
    public function post(string $uri, $action)
    {
        return $this->addRoute('POST', $uri, $action);
    }

    /**
     * Register a new PUT route with the router.
     *
     * @param string                $uri
     * @param array|string|callable $action
     *
     * @return \Swilen\Routing\Route
     */
    public function put(string $uri, $action)
    {
        return $this->addRoute('PUT', $uri, $action);
    }

    /**
     * Register a new PATCH route with the router.
     *
     * @param string                $uri
     * @param array|string|callable $action
     *
     * @return \Swilen\Routing\Route
     */
    public function patch(string $uri, $action)
    {
        return $this->addRoute('PATCH', $uri, $action);
    }

    /**
     * Register a new DELETE route with the router.
     *
     * @param string                $uri
     * @param array|string|callable $action
     *
     * @return \Swilen\Routing\Route
     */
    public function delete(string $uri, $action)
    {
        return $this->addRoute('DELETE', $uri, $action);
    }

    /**
     * Register a new OPTIONS route with the router.
     *
     * @param string                $uri
     * @param array|string|callable $action
     *
     * @return \Swilen\Routing\Route
     */
    public function options(string $uri, $action)
    {
        return $this->addRoute('OPTIONS', $uri, $action);
    }

    /**
     * Add new route to route collection.
     *
     * @param string                $method
     * @param string                $uri
     * @param string|array|\Closure $action
     *
     * @return \Swilen\Routing\Route
     */
    protected function addRoute(string $method, string $uri, $action)
    {
        $route = $this->newRoute($method, $uri, $action);

        if ($this->hasGroupStack()) {
            $this->mergeSharedRouteAttributes($route);
        }

        return $this->routes->add($route);
    }

    /**
     * Create new Route.
     *
     * @param string                $method
     * @param string                $uri
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
     * Create group routes with shared attributes.
     *
     * @param array          $atributes
     * @param \Closure|array $routes
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

    /**
     * Merge attributes into route.
     *
     * @param \Swilen\Routing\Route $route
     *
     * @return void
     */
    protected function mergeSharedRouteAttributes(Route $route)
    {
        $attributes  = end($this->groupStack);

        $route->use($attributes['middleware'] ?? $attributes['use'] ?? []);
    }

    /**
     * Wrap group routes as array.
     *
     * @param mixed $routes
     *
     * @return array
     */
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
            $attributes = $this->mergeGroupAttributes($attributes);
        }

        $this->groupStack[] = $attributes;
    }

    /**
     * Merge the given array with the last group stack.
     *
     * @param array $new
     *
     * @return array
     */
    public function mergeGroupAttributes($new)
    {
        return Group::merge($new, end($this->groupStack));
    }

    /**
     * Load routes.
     *
     * @param \Closure|string $routes
     *
     * @return void
     */
    protected function loadRoutes($routes)
    {
        if ($routes instanceof \Closure) {
            $result = $routes($this);
        } elseif (is_file($routes)) {
            require_once $routes;
        }

        if (is_file($result)) {
            require_once $result;
        }
    }

    /**
     * Prefix group of routes.
     *
     * @param string $uri
     *
     * @return string
     */
    protected function prefix($uri)
    {
        return '/'.trim(trim($this->getLastGroupPrefix(), '/').'/'.trim($uri, '/'), '/') ?: '/';
    }

    /**
     * Get the prefix of the last group or an empty string if not defined.
     *
     * @return string
     */
    public function getLastGroupPrefix()
    {
        if ($this->hasGroupStack()) {
            $prefix = end($this->groupStack);

            return $prefix['prefix'] ?? '';
        }

        return '';
    }

    /**
     * Handle incoming request and dispatch to route.
     *
     * @param \Swilen\Http\Request $request
     *
     * @return \Swilen\Http\Response
     */
    public function dispatch(Request $request)
    {
        $this->request = $request;

        return $this->dispatchToRoute($request);
    }

    /**
     * Send the current request to the route that matches the action.
     *
     * @param \Swilen\Http\Request $request
     *
     * @return \Swilen\Http\Response
     */
    protected function dispatchToRoute(Request $request)
    {
        $this->current = $route = $this->routes->match($request);

        return (new Pipeline($this->container))
            ->from($request)
            ->through($route->middlewares() ?? [])
            ->then(function ($request) use ($route) {
                return $this->prepareResponse($request, $route->run());
            });
    }

    /**
     * Prepare response from incoming request.
     *
     * @param \Swilen\Http\Request $request
     * @param mixed                $response
     *
     * @return \Swilen\Http\Response
     */
    public function prepareResponse(Request $request, $response)
    {
        if ($response instanceof Response) {
            return $response->prepare($request);
        }

        if ($response instanceof \Stringable) {
            $response = new Response($response->__toString(), 200, ['Content-Type' => 'text/html']);
        } elseif (HttpTransformJson::shouldBeJson($response)) {
            $response = new JsonResponse($response);
        } else {
            $response = new Response($response);
        }

        if ($response->getStatusCode() === Http::NOT_MODIFIED) {
            $response->setNotModified();
        }

        return $response->prepare($request);
    }

    /**
     * Return routes collection.
     *
     * @return \Swilen\Routing\RouteCollection
     */
    public function routes()
    {
        return $this->routes;
    }

    /**
     * Return current route matched.
     *
     * @return \Swilen\Routing\Route|null
     */
    public function current()
    {
        return $this->current;
    }

    /**
     * Return the current request captured by the router.
     *
     * @return \Swilen\Http\Request
     */
    public function request()
    {
        return $this->request;
    }

    /**
     * Dynamically handle calls into the router instance.
     *
     * @param string $method
     * @param array  $arguments
     *
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        if ($method === 'middleware' || $method === 'use') {
            return (new RouteRegister($this))->attribute($method, is_array($arguments[0]) ? $arguments[0] : $arguments);
        }

        return (new RouteRegister($this))->attribute($method, array_key_exists(0, $arguments) ? $arguments[0] : true);
    }
}
