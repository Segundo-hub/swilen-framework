<?php

namespace Swilen\Routing;

use Swilen\Container\Container;
use Swilen\Routing\Exception\HttpResponseException;
use Swilen\Routing\Exception\InvalidRouteHandlerException;
use Swilen\Shared\Support\Arrayable;
use Swilen\Shared\Support\JsonSerializable;

class Route implements Arrayable, JsonSerializable
{
    /**
     * The URI pattern the route responds to.
     *
     * @var string
     */
    protected $pattern;

    /**
     * The HTTP methods the route responds to.
     *
     * @var string
     */
    protected $method;

    /**
     * The route action array.
     *
     * @var array<string, \Closure|string>
     */
    protected $action;

    /**
     * The controller instance.
     *
     * @var mixed
     */
    protected $controller;

    /**
     * The regular expression requirements.
     *
     * @var array
     */
    protected $wheres = [];

    /**
     * The match regex generated.
     *
     * @var string
     */
    protected $matching;

    /**
     * The array of matched parameters.
     *
     * @var array
     */
    protected $parameters = [];

    /**
     * The parameter names for the route.
     *
     * @var array|null
     */
    protected $parameterNames;

    /**
     * Middleware collection for the route.
     *
     * @var mixed[]
     */
    protected $middleware = [];

    /**
     * The container instance used by the route.
     *
     * @var \Swilen\Container\Container
     */
    protected $container;

    /**
     * The router instance used by the route.
     *
     * @var \Swilen\Routing\Router;
     */
    protected $router;

    /**
     * Create new Route instance.
     *
     * @param string                $method
     * @param string                $pattern
     * @param string|array|\Closure $action
     *
     * @return void
     */
    public function __construct(string $method, string $pattern, $action)
    {
        $this->method  = $method;
        $this->pattern = $pattern;
        $this->action  = $this->parseAction($action);
    }

    /**
     * Parse the given action into an array.
     *
     * @param mixed $action
     *
     * @return array
     */
    public function parseAction($action)
    {
        return RouteAction::parse($this->pattern, $action);
    }

    /**
     * Set the container instance.
     *
     * @param \Swilen\Container\Container $container
     *
     * @return $this
     */
    public function setContainer($container)
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Set the router instance.
     *
     * @param \Swilen\Routing\Router $router
     *
     * @return $this
     */
    public function setRouter(Router $router)
    {
        $this->router = $router;

        return $this;
    }

    /**
     * Run route matched with the request.
     *
     * @return mixed
     *
     * @throws \Swilen\Routing\Exception\HttpResponseException
     */
    public function run()
    {
        $this->container = $this->container ?: new Container();

        try {
            if ($this->routeActionIsController()) {
                return $this->runActionAsController();
            }

            return $this->runActionAsCallable();
        } catch (HttpResponseException $e) {
            return $e->getResponse();
        }
    }

    /**
     * Run route has route action is Controller.
     *
     * @return mixed
     *
     * @throws \Swilen\Routing\Exception\InvalidRouteHandlerException
     */
    private function runActionAsController()
    {
        [$class, $method] = RouteAction::parseControllerAction($this->action['uses']);

        if (!method_exists($class, $method)) {
            throw InvalidRouteHandlerException::forController($class, $method);
        }

        return $this->runAction([$this->container->make($class), $method]);
    }

    /**
     * Run route has route action is Closure or callable.
     *
     * @return mixed
     */
    private function runActionAsCallable()
    {
        return $this->runAction($this->action['uses']);
    }

    /**
     * Check route action is controller.
     *
     * @return bool
     */
    private function routeActionIsController()
    {
        return is_string($this->action['uses']) && !$this->action['uses'] instanceof \Closure;
    }

    /**
     * Run action with parameters.
     *
     * @param \Closure|array $action
     *
     * @return mixed
     */
    protected function runAction($action)
    {
        return $this->container->call($action, $this->getParameters());
    }

    /**
     * Create regex from given pattern.
     *
     * @param string $pattern
     *
     * @return string
     */
    private function compilePatternMatching()
    {
        if ($this->matching !== null) {
            return $this->matching;
        }

        return $this->matching = RouteMatching::compile($this->pattern);
    }

    /**
     * Determine if the route matches a given request.
     *
     * @param string $path
     *
     * @return bool
     */
    public function matches(string $path)
    {
        $this->compilePatternMatching();

        if (preg_match('#^'.$this->matching.'$#D', rawurldecode($path), $matches)) {
            $this->matchToKeys(array_slice($matches, 1));

            return true;
        }

        return false;
    }

    /**
     * Compile parameters from raw regex matches.
     *
     * @param array<string, mixed> $params
     *
     * @return void
     */
    private function matchToKeys(array $params = [])
    {
        foreach ($params as $key => $value) {
            if (is_numeric($key) || is_null($value)) {
                continue;
            }

            $this->parameters[$key] = $value;
        }
    }

    /**
     * Get the parameter names for the route.
     *
     * @return array
     */
    protected function compileParametersNames()
    {
        preg_match_all('/\{(.*?)\}/', $this->pattern, $matches);

        return array_map(function ($m) {
            return trim($m, '?');
        }, $matches[1]);
    }

    /**
     * Get all of the parameter names for the route.
     *
     * @return array
     */
    public function parameterNames()
    {
        if ($this->parameterNames !== null) {
            return $this->parameterNames;
        }

        return $this->parameterNames = $this->compileParametersNames();
    }

    /**
     * Register a middleware for route.
     *
     * @param string|array $middlewares
     *
     * @return $this
     */
    public function use($middlewares)
    {
        foreach ((array) $middlewares as $middleware) {
            $this->middleware[] = $middleware;
        }

        return $this;
    }

    /**
     * Add or change the route name.
     *
     * @param string $name
     *
     * @return $this
     */
    public function name(string $name)
    {
        $this->action['as'] = isset($this->action['as']) ? $this->action['as'].$name : $name;

        return $this;
    }

    /**
     * Get a given parameter from the route.
     *
     * @param string             $name
     * @param string|object|null $default
     *
     * @return string|object|null
     */
    public function parameter($name, $default = null)
    {
        return $this->parameters[$name] ?? $default;
    }

    /**
     * Get the method of route.
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Get pattern (uri) of the route.
     *
     * @return string
     */
    public function getPattern()
    {
        return $this->pattern;
    }

    /**
     * Get route match incomplete regex.
     *
     * @return string|null
     */
    public function getMatch()
    {
        return $this->matching;
    }

    /**
     * Get action of the route based in given key or all.
     *
     * @return mixed
     */
    public function getAction(string $key = null)
    {
        return $key ? $this->action[$key] : $this->action;
    }

    /**
     * Retrieve middleware from the route.
     *
     * @return array
     */
    public function getMiddleware()
    {
        return $this->middleware;
    }

    /**
     * Retrieve middleware from the route.
     *
     * @return array
     */
    public function middlewares()
    {
        return $this->middleware;
    }

    /**
     * Get name of the route.
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->action['as'] ?? null;
    }

    /**
     * Get parameters of the reoute.
     *
     * @return array<string,mixed>
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * {@inheritdoc}
     *
     * Transform current route to Array
     *
     * @return array<string, mixed>
     */
    public function toArray()
    {
        return [
            'pattern' => $this->pattern,
            'method' => $this->method,
            'action' => $this->action,
            'middleware' => $this->middleware,
            'matching' => $this->compilePatternMatching(),
            'parameters' => $this->parameters,
        ];
    }

    /**
     * {@inheritdoc}
     *
     * Prepare route for serialization in json
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
