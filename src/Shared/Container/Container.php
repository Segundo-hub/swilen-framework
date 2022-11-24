<?php

namespace Swilen\Shared\Container;

interface Container
{
    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id identifier of the entry to look for
     *
     * @return mixed entry
     */
    public function get(string $id);

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * @param string $id identifier of the entry to look for
     *
     * @return bool
     */
    public function has(string $id): bool;

    /**
     * Resolve the given type from the container.
     *
     * @param string|callable $abstract
     * @param array           $parameters
     *
     * @return mixed
     */
    public function make(string $service, array $parameters = []);

    /**
     * Register a binding with the container.
     *
     * @param string               $abstract
     * @param \Closure|string|null $concrete
     * @param bool                 $shared
     *
     * @return void
     */
    public function bind(string $service, $abstract = null, $shared = false): void;

    /**
     * Unbind service from container.
     *
     * @param string $service
     *
     * @return void
     */
    public function unbind(string $service): void;

    /**
     * Register a shared binding in the container.
     *
     * @param string               $abstract
     * @param \Closure|string|null $concrete
     *
     * @return void
     */
    public function singleton(string $service, $abstract = null): void;

    /**
     * Register an existing instance as shared in the container.
     *
     * @param string $abstract
     * @param mixed  $instance
     *
     * @return mixed
     */
    public function instance(string $abstract, $instance);
}
