<?php

namespace Swilen\Pipeline;

use Swilen\Container\Container;
use Swilen\Pipeline\Contract\PipelineContract;

class Pipeline implements PipelineContract
{
    /**
     * The container instance.
     *
     * @var \Swilen\Container\Container
     */
    protected $container;

    /**
     * The object being passed through the pipeline.
     *
     * @var mixed
     */
    protected $target;

    /**
     * The array of class pipes.
     *
     * @var array
     */
    protected $pipes = [];

    /**
     * The method to call on each pipe.
     *
     * @var string
     */
    protected $method = 'handle';

    /**
     * Create a new class instance.
     *
     * @param \Swilen\Container\Container $container
     *
     * @return void
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function from($target)
    {
        $this->target = $target;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function through($pipes)
    {
        $this->pipes = is_array($pipes) ? $pipes : func_get_args();

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function then(\Closure $destination)
    {
        $pipeline = array_reduce(
            array_reverse($this->pipes), $this->carryPipes(), $this->prepareDestination($destination)
        );

        return $pipeline($this->target);
    }

    /**
     * {@inheritdoc}
     */
    public function viaMethod(string $method)
    {
        $this->method = $method;

        return $this;
    }

    /**
     * Prepare final destination.
     *
     * @param \Closure $destination
     *
     * @return \Closure
     */
    protected function prepareDestination(\Closure $destination)
    {
        return function ($target) use ($destination) {
            try {
                return $destination($target);
            } catch (\Throwable $e) {
                return $this->handleException($target, $e);
            }
        };
    }

    /**
     * Carry pipes list.
     *
     * @return \Closure
     */
    protected function carryPipes()
    {
        return function ($stack, $pipe) {
            return function ($target) use ($stack, $pipe) {
                try {
                    if (is_callable($pipe)) {
                        return $pipe($target, $stack);
                    } elseif (!is_object($pipe)) {
                        list($name, $parameters) = $this->parsePipeString($pipe);

                        $pipe = $this->container->make($name);

                        $parameters = array_merge([$target, $stack], $parameters);
                    } else {
                        $parameters = [$target, $stack];
                    }

                    $carry = method_exists($pipe, $this->method)
                        ? $pipe->{$this->method}(...$parameters)
                        : $pipe(...$parameters);

                    return $this->handleCarry($carry);
                } catch (\Throwable $e) {
                    return $this->handleException($target, $e);
                }
            };
        };
    }

    /**
     * Parse pipe function or method.
     *
     * @param string $pipe
     *
     * @return array
     */
    protected function parsePipeString($pipe)
    {
        list($name, $parameters) = array_pad(explode(':', $pipe, 2), 2, []);

        if (is_string($parameters)) {
            $parameters = explode(',', $parameters);
        }

        return [$name, $parameters];
    }

    /**
     * Get the array of configured pipes.
     *
     * @return array
     */
    protected function pipes()
    {
        return $this->pipes;
    }

    /**
     * Handle the value returned from each pipe before passing it to the next.
     *
     * @param mixed $carry
     *
     * @return mixed
     */
    protected function handleCarry($carry)
    {
        return $carry;
    }

    /**
     * Handle the given exception.
     *
     * @param mixed      $target
     * @param \Throwable $e
     *
     * @return mixed
     *
     * @throws \Throwable
     */
    protected function handleException($target, \Throwable $e)
    {
        throw $e;
    }
}
