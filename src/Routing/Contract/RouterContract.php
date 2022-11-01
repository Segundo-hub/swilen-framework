<?php

namespace Swilen\Routing\Contract;

interface RouterContract
{
    /**
     * Register a new GET route with the router.
     *
     * @param string $uri
     * @param array|string|callable $action
     *
     * @return \Swilen\Routing\Route
     */
    public function get(string $uri, $action);

    /**
     * Register a new POST route with the router.
     *
     * @param string $uri
     * @param array|string|callable $action
     *
     * @return \Swilen\Routing\Route
     */
    public function post(string $uri, $action);

    /**
     * Register a new PUT route with the router.
     *
     * @param string $uri
     * @param array|string|callable $action
     *
     * @return \Swilen\Routing\Route
     */
    public function put(string $uri, $action);

    /**
     * Register a new DELETE route with the router.
     *
     * @param string $uri
     * @param array|string|callable $action
     *
     * @return \Swilen\Routing\Route
     */
    public function delete(string $uri, $action);

    /**
     * Register a new PATCH route with the router.
     *
     * @param string $uri
     * @param array|string|callable $action
     *
     * @return \Swilen\Routing\Route
     */
    public function patch(string $uri, $action);

    /**
     * Register a new OPTIONS route with the router.
     *
     * @param string $uri
     * @param array|string|callable $action
     *
     * @return \Swilen\Routing\Route
     */
    public function options(string $uri, $action);

    /**
     * Create a route group with shared attributes.
     *
     * @param  array  $attributes
     * @param  \Closure|string  $routes
     * @return void
     */
    public function group(array $attributes, $routes);
}
