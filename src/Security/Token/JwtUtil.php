<?php

namespace Swilen\Security\Token;

final class JwtUtil
{
    /**
     * @param string $target
     * @return string
     */
    public static function url_encode(string $target)
    {
        return str_replace('=', '', strtr(base64_encode($target), '+/', '-_'));
    }

    /**
     * @param string $target
     * @return string
     */
    public static function url_decode(string $target)
    {
        $remainder = strlen($target) % 4;
        if ($remainder) {
            $padlen = 4 - $remainder;
            $target .= str_repeat('=', $padlen);
        }
        return base64_decode(strtr($target, '-_', '+/'));
    }

    public static function json_decode(string $target)
    {
        return json_decode($target, true, 512, JSON_BIGINT_AS_STRING);
    }

    public static function json_encode($target)
    {
        if (PHP_VERSION_ID >= 50400) {
            return json_encode($target, \JSON_UNESCAPED_SLASHES);
        }

        return json_encode($target);
    }
}
