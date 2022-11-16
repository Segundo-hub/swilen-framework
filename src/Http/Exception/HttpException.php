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
     * Add HTPP headers to Exception.
     *
     * @param array<string, string> $headers
     * @param bool                  $replaced
     *
     * @return $this
     */
    public function withHeaders(array $headers = [], bool $replaced = false)
    {
        foreach ($headers as $key => $value) {
            $this->withHeader($key, $value, $replaced);
        }

        return $this;
    }

    /**
     * Add HTTP header to Exception.
     *
     * @param string $key
     * @param mixed  $value
     * @param bool   $replaced
     *
     * @return $this
     */
    public function withHeader(string $key, $value, bool $replaced = true)
    {
        if (!$replaced && isset($this->headers[$key])) {
            return $this;
        }

        $this->headers[$key] = $value;

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
