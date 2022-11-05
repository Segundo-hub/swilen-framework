<?php

namespace Swilen\Routing\Exception;

class InvalidRouteHandlerException extends \Exception
{
    /**
     * Create exception for action is callable.
     *
     * @param string $message
     * @param int    $code
     *
     * @return static
     */
    public static function forCallable(string $message, int $code = 500)
    {
        return new self(sprintf('Invalid Route Handler: call undefined function "%s"', $message), $code);
    }

    /**
     * Create exception for action is controller method.
     *
     * @param string $class
     * @param string $method
     * @param int    $code
     *
     * @return static
     */
    public static function forController(string $class, string $method = null, int $code = 500)
    {
        return new self(sprintf('Invalid Route Handler: call undefined method "%s::%s"', $class, $method), $code);
    }
}
