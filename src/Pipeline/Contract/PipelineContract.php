<?php

namespace Swilen\Pipeline\Contract;

interface PipelineContract
{
    /**
     * Set the object being sent through the pipeline.
     *
     * @param mixed $target
     *
     * @return $this
     */
    public function from($target);

    /**
     * Set array of pipes
     *
     * @param array|mixed $pipes
     *
     * @return $this
     */
    public function through($pipes);

    /**
     * Terminate pipeline with destination
     *
     * @param \Closure $destination
     *
     * @return mixed
     */
    public function then(\Closure $destination);

    /**
     * Set method each called pipes
     *
     * @param string $method The method name
     *
     * @return $this
     */
    public function viaMethod(string $method);
}
