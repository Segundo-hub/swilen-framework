<?php

namespace Swilen\Routing;

use Swilen\Routing\Exception\HttpResponseException;
use Swilen\Routing\Exception\InvalidHttpHandlerException;

use Swilen\Container\Container;
use Swilen\Contracts\Support\Arrayable;
use Swilen\Contracts\Support\JsonSerializable;

final class Route implements Arrayable, JsonSerializable
{
    /**
     * @var string
     */
    private $uri;

    /**
     * @var string
     */
    private $method;

    /**
     * @var string
     */
    private $match;

    /**
     * @var string[]
     */
    private $matchedParameters = [];

    /**
     * @var string
     */
    private $name;

    /**
     * @var \Closure|array|string
     */
    private $action;

    /**
     * @var mixed[]
     */
    private $middleware = [];

    /**
     * @var string
     */
    public const UNMATCH_ROUTE = 0;

    /**
     * @var string
     */
    public const REG_MATCH_PARAM = '/{[^}]*}/';

    /**
     * @var string
     */
    public const REG_MATCH_PARAM_NAME = '/\{(.*?)\}/';

    /**
     * @var \Swilen\Container\Container;
     */
    private $container;

    /**
     * @var \Swilen\Routing\Router;
     */
    private $router;

    /**
     * Create new route
     *
     * @param string $method
     * @param string $uri
     * @param string|array|\Closure $action
     *
     * @return void
     */
    public function __construct(string $method, string $uri, $action)
    {
        $this->method     = $method;
        $this->match      = $this->matchFrom($uri);
        $this->uri        = $uri;
        $this->action     = $action;
    }

    /**
     * Set the container instance
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
     * Set the router instance
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
     * Run route matched
     *
     * @return mixed
     * @throws \Swilen\Routing\Exception\HttpResponseException
     */
    public function run()
    {
        $this->container = $this->container ?: new Container;

        try {
            if ($this->isControllerAction()) {
                return $this->runRouteWithController();
            }
            return $this->runRouteWithClosure();
        } catch (HttpResponseException $e) {
            return $e->getMessage();
        }
    }

    /**
     * Run route has route action is Controller
     *
     * @return mixed
     */
    private function runRouteWithController()
    {
        $controller = $this->container->make($this->action['class']);

        return $this->container->call(
            [$controller, $this->action['method']],
            $this->getParameters()
        );
    }

    /**
     * Run route has route action is Closure or callable
     *
     * @return mixed
     */
    private function runRouteWithClosure()
    {
        return $this->container->call($this->action, $this->getParameters());
    }

    /**
     * Check route action is controller
     *
     * @return true|void
     *
     * @throws \Swilen\Routing\Exception\InvalidHttpHandlerException
     */
    private function isControllerAction()
    {
        if (is_array($this->action) || is_string($this->action)) {
            [$class, $method] = !is_string($this->action)
                ? $this->action
                : explode('@', (string) $this->action);

            if (!method_exists($class, $method)) {
                throw new InvalidHttpHandlerException();
            }

            $this->action = compact('class', 'method');

            return true;
        }
    }

    /**
     * Create segement uri with params from current uri
     *
     * @param string $uri
     *
     * @return string
     */
    private function matchFrom(string $uri)
    {
        return $this->compileSegmentedParameters(rtrim($uri, '/') ?: '/');
    }

    /**
     * Compile segmented URL via uri with regex pattern
     *
     * @param string $uri
     *
     * @return string
     */
    private function compileSegmentedParameters($uri)
    {
        $segmentedParameters = $this->transformParametersToArray($uri);

        foreach ($segmentedParameters as $segment) {
            $value = trim($segment, '{\}');
            if (strpos($value, ':') !== false) {
                [$type, $valued] = explode(':', $value);

                if ($type === 'int') {
                    $uri = str_replace("{{$type}:{$valued}}", "(?P<{$valued}>[0-9]+)", $uri);
                }

                if ($type === 'string') {
                    $uri = str_replace("{{$type}:{$valued}}", "(?P<{$valued}>[a-zA-Z0-9]+)", $uri);
                }
            } else {
                $uri = str_replace("{{$value}}", "(?P<{$value}>.*)", $uri);
            }
        }

        return $uri;
    }

    /**
     * Return named paramater to array
     *
     * @param string|null $uri
     *
     * @return array<int, mixed>
     */
    private function transformParametersToArray($uri)
    {
        preg_match_all(static::REG_MATCH_PARAM, $uri, $matches);
        return reset($matches) ?? [];
    }

    /**
     * Match with route from action request
     *
     * @param string $action
     *
     * @return \Swilen\Routing\Route|null
     */
    public function matches(string $action)
    {
        $matched = static::UNMATCH_ROUTE;
        if (preg_match("#^{$this->match}$#", $action, $matches)) {
            $this->compileParameters($matches);
            $matched = $this;
        }

        return $matched;
    }

    /**
     * Compile params from regex matches
     *
     * @param array<string, mixed> $params
     *
     * @return void
     */
    private function compileParameters($params)
    {
        foreach ($params as $key => $value) {
            if (is_numeric($key)) continue;
            if (is_null($value)) continue;
            if (is_string($value)) $value  = (string) rawurldecode($value);
            if (is_numeric($value)) $value = intval($value);
            $this->matchedParameters[$key] = $value;
        }
    }

    /**
     * Register a middleware for route
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

    public function name(string $name)
    {
        $this->name = $name;

        return $this;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function getUri()
    {
        return $this->uri;
    }

    public function getMatch()
    {
        return $this->match;
    }

    public function getAction()
    {
        return $this->action;;
    }

    public function getMiddleware()
    {
        return $this->middleware;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getParameters()
    {
        return $this->matchedParameters;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return [
            'method'     => $this->getMethod(),
            'uri'        => $this->getUri(),
            'match'      => $this->getMatch(),
            'matched'    => $this->getParameters(),
            'action'     => $this->getAction(),
            'middleware' => $this->getMiddleware(),
            'name'       => $this->getName(),
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
