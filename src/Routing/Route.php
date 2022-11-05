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
     * @var array
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
     * @var string
     */
    public const REG_MATCH_PARAM = '/{[^}]*}/';

    /**
     * @var string
     */
    public const REG_MATCH_PARAM_NAME = '/\{(.*?)\}/';

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
        $this->method = $method;
        $this->pattern = $pattern;
        $this->action = $this->parseAction($action);
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
     * Run route matched.
     *
     * @return mixed
     *
     * @throws \Swilen\Routing\Exception\HttpResponseException
     */
    public function run()
    {
        $this->container = $this->container ?: new Container();

        try {
            if ($this->actionIsController()) {
                return $this->runRouteActionAsController();
            }

            return $this->runRouteActionAsClosure();
        } catch (\Throwable $e) {
            throw new HttpResponseException($e->getMessage(), $e->getCode(), $e->getPrevious());
        }
    }

    /**
     * Run route has route action is Controller.
     *
     * @return mixed
     *
     * @throws \Swilen\Routing\Exception\InvalidRouteHandlerException
     */
    private function runRouteActionAsController()
    {
        [$class, $method] = RouteAction::parseControllerAction($this->action['uses']);

        if (!method_exists($class, $method)) {
            throw InvalidRouteHandlerException::forController($class, $method);
        }

        return $this->container->call([$class, $method], $this->getParameters());
    }

    /**
     * Run route has route action is Closure or callable.
     *
     * @return mixed
     */
    private function runRouteActionAsClosure()
    {
        return $this->container->call($this->action['uses'], $this->getParameters());
    }

    /**
     * Check route action is controller.
     *
     * @return bool
     */
    private function actionIsController()
    {
        return is_string($this->action['uses'] && !is_callable($this->action['uses']));
    }

    /**
     * Create regex from given pattern.
     *
     * @param string $pattern
     *
     * @return string
     */
    private function compilePattern()
    {
        if ($this->matching !== null) {
            return $this->matching;
        }

        $pattern = rtrim($this->pattern, '/') ?: '/';
        $matches = $this->compileParameters($pattern);

        return $this->matching = $this->compilePatternMatching($matches, $pattern);
    }

    /**
     * Compile segmented URL via uri with regex pattern.
     *
     * @param array  $matches
     * @param string $uri
     *
     * @return string
     */
    protected function compilePatternMatching(array $matches = [], string $uri)
    {
        foreach ($matches as $key => $segment) {
            $target = $segment;
            $value = trim($segment, '{\}');

            if (strpos($value, ':') !== false && !empty([$type, $valued] = explode(':', $value))) {
                $target = '{'.$type.':'.$valued.'}';

                if ($type === 'int') {
                    $uri = str_replace($target, sprintf('(?P<%s>[0-9]+)', $valued), $uri);
                }

                if ($type === 'alpha') {
                    $uri = str_replace($target, sprintf('(?P<%s>[a-zA-Z\_\-]+)', $valued), $uri);
                }

                if ($type === 'string') {
                    $uri = str_replace($target, sprintf('(?P<%s>[a-zA-Z0-9\_\-]+)', $valued), $uri);
                }
            } else {
                $uri = str_replace($target, sprintf('(?P<%s>.*)', $value), $uri);
            }
        }

        return $uri;
    }

    /**
     * Return named paramater to array.
     *
     * @param string|null $uri
     *
     * @return array<int, mixed>
     */
    private function compileParameters($uri)
    {
        preg_match_all(static::REG_MATCH_PARAM, $uri, $matches);

        return reset($matches) ?? [];
    }

    /**
     * Match request path with route match regex.
     *
     * @param string $path
     *
     * @return bool
     */
    public function matches(string $path)
    {
        $this->compilePattern();

        $matched = false;

        if (preg_match("#^{$this->matching}$#D", rawurldecode($path), $matches)) {
            $this->matchToKeys(array_slice($matches, 1));
            $matched = true;
        }

        return $matched;
    }

    /**
     * Compile params from regex matches.
     *
     * @param array<string, mixed> $params
     *
     * @return void
     */
    private function matchToKeys(array $params = [])
    {
        foreach ($params as $key => $value) {
            if (is_int($key) || is_null($value)) {
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
        preg_match_all(static::REG_MATCH_PARAM_NAME, $this->pattern, $matches);

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
        if (!empty($this->parameterNames)) {
            return $this->parameterNames;
        }

        return $this->parameterNames = $this->compileParametersNames();
    }

    /**
     * Register a middleware for route.
     *
     * @param string|array $middlewares
     *
     * @return \Swilen\Routing\Route
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
        return key_exists($name, $this->parameters) ? $this->parameters[$name] : $default;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function getPattern()
    {
        return $this->pattern;
    }

    public function getMatch()
    {
        return $this->matching;
    }

    public function getAction(string $key = null)
    {
        return $key ? $this->action[$key] : $this->action;
    }

    public function getMiddleware()
    {
        return $this->middleware;
    }

    public function getName()
    {
        return $this->action['as'] ?? null;
    }

    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return [
            'pattern' => $this->getPattern(),
            'method' => $this->getMethod(),
            'action' => $this->getAction(),
            'middleware' => $this->getMiddleware(),
            'matching' => $this->getMatch(),
            'parameters' => $this->getParameters(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
