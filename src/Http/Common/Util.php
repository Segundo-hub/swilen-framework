<?php

namespace Swilen\Http\Common;

/**
 * @internal
 * The packages is internal, provide helpers functions
 */
class Util
{
    /**
     * Normalize token to Capitalize key when contains http.
     *
     * @param string $token
     *
     * @return string
     */
    public static function toNormalizeHttp(string $token)
    {
        return str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($token, 5)))));
    }

    /**
     * Normalize token to Capitalize key.
     *
     * @param string $token
     *
     * @return string
     */
    public static function toNormalize(string $token)
    {
        return implode('-', array_map('ucwords', explode('-', str_replace(['-', '_'], '-', strtolower($token)))));
    }
}
