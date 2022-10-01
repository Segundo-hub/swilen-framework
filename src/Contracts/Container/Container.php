<?php

namespace Swilen\Contracts\Container;

interface Container
{
    /**
     * Find and return class binding instance or null if not found
     *
     * @param string $name
     *
     * @return mixed
     */
    public function get(string $service);

    /**
     * Find and return class binding instance or null if not found
     *
     * @param string $name
     *
     * @return mixed
     */
    public function make(string $service);

    /**
     * Find if service is binding
     *
     * @param string $service
     *
     * @return bool
     */
    public function has(string $service): bool;

    /**
     * Find and return class binding instance or null if not found
     *
     * @param string $service
     * @param \Closure|string|null $abstract
     * @param bool $shared
     *
     * @return void
     */
    public function bind(string $service, $abstract = null, $shared = false): void;

    /**
     * Unbind service from container
     *
     * @param string $service
     *
     * @return void
     */
    public function unbind(string $service): void;

    /**
     * Bind service has shared
     *
     * @param string $service
     * @param \Closure|string|null $abstract
     *
     * @return void
     */
    public function singleton(string $service, $abstract = null): void;
}
