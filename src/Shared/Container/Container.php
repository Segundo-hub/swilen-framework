<?php

namespace Swilen\Shared\Container;

use Psr\Container\ContainerInterface;

interface Container extends ContainerInterface
{
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
