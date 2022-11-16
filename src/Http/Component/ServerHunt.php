<?php

namespace Swilen\Http\Component;

use Swilen\Http\Common\Util;

final class ServerHunt extends ParameterHunt
{
    /**
     * Make default header for request.
     *
     * @return array<string, string>
     */
    public function headers()
    {
        $headers    = [];
        $additional = [
            'CONTENT_TYPE' => true,
            'CONTENT_LENGTH' => true,
            'CONTENT_MD5' => true,
        ];

        foreach ($this->params as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $headers[Util::toNormalizeHttp($key)] = $value;
            }
            // Add additional server headers to headers
            // CONTENT_* headers are not prefixed with HTTP_.
            elseif (isset($additional[$key])) {
                $headers[Util::toNormalize($key)] = $value;
            }
        }

        if (function_exists('apache_request_headers')) {
            $apacheRequestHeaders = apache_request_headers();
            foreach ($apacheRequestHeaders as $key => $value) {
                $key = Util::toNormalize($key);
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
