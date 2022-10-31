<?php

use Swilen\Arthropod\Env;
use Swilen\Container\Container;
use Swilen\Http\Request;

if (!function_exists('app')) {
    /**
     * Get the available container instance.
     *
     * @param string|null  $abstract
     * @param array $parameters
     *
     * @return \Swilen\Shared\Arthropod\Application
     */
    function app($abstract = null, array $parameters = [])
    {
        if (is_null($abstract)) {
            return Container::getInstance();
        }

        return Container::getInstance()->make($abstract, $parameters);
    }
}

if (!function_exists('response')) {
    /**
     * @param resource|string|array|object|null $content
     * @param int $status
     * @param array $headers
     *
     * @return \Swilen\Http\Response
     */
    function response($content = null, int $status = 200, array $headers = [])
    {
        /**
         * @var \Swilen\Http\Response
         */
        $response = app()->make('response');

        if (!is_null($content)) {
            return $response->send($content, $status, $headers);
        }

        return $response;
    }
}

if (!function_exists('request')) {
    /**
     * @return \Swilen\Http\Request
     */
    function request()
    {
        return app()->make(Request::class);
    }
}

if (!function_exists('base_path')) {
    /**
     * @param string $path
     *
     * @return string
     */
    function base_path($path = '')
    {
        return app()->basePath($path);
    }
}

if (!function_exists('app_path')) {
    /**
     * @return string
     */
    function app_path($path = '')
    {
        return app()->appPath($path);
    }
}

if (!function_exists('app_config')) {
    /**
     * @return array|null
     */
    function app_config($key = '', $default = null)
    {
        $config = (array) app()->make('config');

        if (key_exists($key, $config)) {
            return $config[$key];
        }

        return $default;
    }
}

if (!function_exists('storage_path')) {
    /**
     * @return string The storage path
     */
    function storage_path($path = '')
    {
        return app_path('storage') . ($path ? DIRECTORY_SEPARATOR . $path : '');
    }
}

if (!function_exists('env')) {
    /**
     * @param string|int $key
     * @param string|int|bool $default
     *
     * @return string|int|null
     */
    function env($key, $default = NULL)
    {
        return Env::get($key, $default);
    }
}

if (!function_exists('tap')) {
    /**
     * Call the given Closure with the given value then return the value.
     *
     * @param mixed $target
     * @param callable $callback
     *
     * @return mixed
     */
    function tap($target, $callback)
    {
        $callback($target);

        return $target;
    }
};
