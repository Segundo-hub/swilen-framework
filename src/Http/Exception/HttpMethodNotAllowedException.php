<?php

namespace Swilen\Http\Exception;

class HttpMethodNotAllowedException extends HttpException
{
    protected $code        = 405;
    protected $message     = 'Method Not Allowed.';
    protected $title       = '405 Method Not Allowed.';
    protected $description = 'Request method not allowed in server.';

    /**
     * Set methods allowed in this context.
     *
     * @param string[] $methods
     */
    public function withAllow(array $methods = [])
    {
        $this->withHeader('Allow', implode(', ', $methods), true);
    }

    /**
     * Create new exception for given method is not allowed, with allowed methods.
     *
     * @param string $method  The method not allowed
     * @param array  $methods The allowed methods
     *
     * @return static
     */
    public static function forMethod(string $method, array $methods = [])
    {
        $methods = strtoupper(implode(', ', $methods));
        $message = sprintf('The %s method is not supported. Must be one of: %s.', $method, $methods);

        return (new static($message))->withHeader('Allow', $methods, true);
    }
}
