<?php

use Swilen\Arthropod\Env;
use Swilen\Container\Container;
use Swilen\Http\Request;
use Swilen\Http\ResponseFactory;

if (!function_exists('app')) {
    /**
     * Get the available container instance.
     *
     * @param string|null  $abstract
     * @param array $parameters
     *
     * @return \Swilen\Contracts\Arthropod\Application
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
     * @return \Swilen\Http\Contract\ResponseFactoryContract
     */
    function response($content = null, int $status = 200, array $headers = [])
    {
        /**
         * @var \Swilen\Http\Contract\ResponseFactoryContract
         */
        $response = app()->make(ResponseFactory::class);

        if (!is_null($content)) {
            return $response->make($content, $status, $headers);
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

if (!function_exists('isJson')) {
    /**
     * @param mixed $target
     * @return bool
     */
    function isJson($target)
    {
        json_decode($target);
        return json_last_error() === JSON_ERROR_NONE;
    }
}

if (!function_exists('strpos_arr')) {
    /**
     * Find string post in array
     *
     * @param string $haystack
     * @param mixed[]|mixed $needle
     */
    function strpos_arr($haystack, $needle)
    {
        if (!is_array($needle)) $needle = array($needle);
        $min = false;
        foreach ($needle as $what)
            if (($pos = strpos($haystack, $what)) !== false && ($min == false || $pos < $min))
                $min = $pos;
        return $min;
    }
}
