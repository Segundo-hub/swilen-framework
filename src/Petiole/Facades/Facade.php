<?php

namespace Swilen\Petiole\Facades;

abstract class Facade
{
    /**
     * The application instance
     *
     * @var \Swilen\Contracts\Arthropod\Application
     */
    protected static $app;

    /**
     * The resolved object instances.
     *
     * @var array
     */
    protected static $resolvedInstances;

    /**
     * Set Facade application
     *
     * @param \Swilen\Contracts\Arthropod\Application $app
     *
     * @return void
     */
    public static function setFacadeApplication($app)
    {
        static::$app = $app;
    }

    /**
     * Get the name registered in the container
     *
     * @return string
     */
    protected static function getFacadeName()
    {
        throw new \RuntimeException();
    }

    /**
     * Resolve facade from the container instance
     *
     * @param string $name
     *
     * @return mixed
     */
    protected static function resolveFacadeInstance(string $name)
    {
        if (isset(static::$resolvedInstances[$name])) {
            return static::$resolvedInstances[$name];
        }

        if (static::$app) {
            return static::$resolvedInstances[$name] = static::$app[$name];
        }
    }

    /**
     * Clear resolver instance and prepare for other request
     *
     * @return void
     */
    public static function flushFacadeInstances()
    {
        static::$resolvedInstances = [];
    }

    /**
     * Clear resolver instance and prepare for other request
     *
     * @param string $name
     *
     * @return void
     */
    public static function flushFacadeInstance(string $name)
    {
        if (isset(static::$resolvedInstances[$name])) {
            unset(static::$resolvedInstances[$name]);
        }
    }

    /**
     * Get object behind the facade
     *
     * @return mixed
     */
    public static function getFacadeInstance()
    {
        return static::resolveFacadeInstance(static::getFacadeName());
    }

    /**
     * Handle dynamic calls to method inset object
     *
     * @param string $method
     * @param array $arguments
     *
     * @return mixed
     */
    public static function __callStatic($method, $arguments)
    {
        $instance = static::getFacadeInstance();
        if (!$instance) {
            throw new \RuntimeException('Facade root not resolved correctly');
        }
        return $instance->{$method}(...$arguments);
    }
}
