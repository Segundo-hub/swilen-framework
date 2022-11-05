<?php

namespace Swilen\Http\Exception;

use Swilen\Arthropod\Exception\CoreException;

class HttpException extends CoreException
{
    /**
     * Headers collection for http response.
     *
     * @var array<string, string>
     */
    protected $headers = [];

    /**
     * Add http headers to exception.
     *
     * @param array<string, string> $headers
     */
    public function withHeaders(array $headers = [])
    {
        foreach ($headers as $key => $value) {
            $this->headers[$key] = $value;
        }

        return $this;
    }

    /**
     * Get all headers registered.
     */
    public function headers()
    {
        return $this->headers;
    }
}
