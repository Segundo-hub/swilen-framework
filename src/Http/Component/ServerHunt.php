<?php

namespace Swilen\Http\Component;

final class ServerHunt extends ParameterHunt
{
    /**
     * Make default header for request.
     *
     * @return array<string, string>
     */
    public function headers()
    {
        $headers = [];

        foreach ($this->params as $key => $value) {
            if (substr($key, 0, 5) === 'HTTP_') {
                $key = $this->toNormalizeHttp($key);
                $headers[$key] = $value;
            } elseif (\in_array($key, ['CONTENT_TYPE', 'CONTENT_LENGTH', 'CONTENT_MD5'], true)) {
                $key = $this->toNormalize($key);
                $headers[$key] = $value;
            }
        }

        if (function_exists('apache_request_headers')) {
            $apacheRequestHeaders = apache_request_headers();
            foreach ($apacheRequestHeaders as $key => $value) {
                $key = $this->toNormalize($key);
                if (!isset($headers[$key])) {
                    $headers[$key] = $value;
                }
            }
        }

        $authorization = null;

        if (isset($this->params['Authorization'])) {
            $authorization = $this->params['Authorization'];
        } elseif (isset($this->params['HTTP_AUTHORIZATION'])) {
            $authorization = $this->params['HTTP_AUTHORIZATION'];
        } elseif (isset($this->params['REDIRECT_HTTP_AUTHORIZATION'])) {
            $authorization = $this->params['REDIRECT_HTTP_AUTHORIZATION'];
        }

        if ($authorization !== null) {
            $headers['Authorization'] = trim($authorization);
        }

        return $headers;
    }

    /**
     * Filter INPUT_SERVER with default value.
     *
     * @param string                $key
     * @param string|int|mixed|null $default
     * @param int                   $filters
     *
     * @return mixed
     */
    public function filter(string $key, $default = null, $filters = FILTER_DEFAULT | FILTER_SANITIZE_ENCODED)
    {
        return \filter_var($this->get($key, $default), $filters) ?: $default;
    }
}
