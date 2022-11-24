<?php

namespace Swilen\Shared\Support;

class Func
{
    /**
     * Return the default value of the given value.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public static function unwrap($value)
    {
        return $value instanceof \Closure ? $value() : $value;
    }

    /**
     * Pipe given value to pipes closures.
     *
     * @param mixed                 $value
     * @param \Closure[]|callable[] ...$pipes
     *
     * @return mixed
     */
    public static function pipe($value, $pipes)
    {
        $pipes = is_array($pipes) ? $pipes : array_slice(func_get_args(), 1);

        return array_reduce($pipes, function ($stack, $pipe) {
            return $pipe($stack);
        }, $value);
    }
}
