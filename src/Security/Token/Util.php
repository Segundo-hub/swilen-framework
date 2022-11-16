<?php

namespace Swilen\Security\Token;

final class Util
{
    /**
     * Safe encode given target to base64.
     *
     * @param string $target
     *
     * @return string
     */
    public static function url_encode(string $target)
    {
        return \str_replace('=', '', \strtr(\base64_encode($target), '+/', '-_'));
    }

    /**
     * Safe decode given target from base64.
     *
     * @param string $target
     *
     * @return string
     */
    public static function url_decode(string $target)
    {
        return \base64_decode(\strtr($target, '-_', '+/'));
    }

    /**
     * Safely decode as json the given value.
     *
     * @param string $target
     *
     * @return mixed
     */
    public static function json_decode(string $target)
    {
        return \json_decode($target, true, 512, \JSON_BIGINT_AS_STRING);
    }

    /**
     * Safely encode as json the given value.
     *
     * @param mixed $target
     *
     * @return string
     */
    public static function json_encode($target)
    {
        return \json_encode($target, \JSON_UNESCAPED_SLASHES);
    }

    /**
     * Compare if two hashes has equals.
     *
     * @param string $left
     * @param string $right
     *
     * @return bool
     */
    public static function hash_equals(string $left, string $right)
    {
        if (function_exists('hash_equals')) {
            return \hash_equals($left, $right);
        }

        $len = min(strlen($left), strlen($right));

        $status = 0;
        for ($i = 0; $i < $len; ++$i) {
            $status |= (ord($left[$i]) ^ ord($right[$i]));
        }

        $status |= (strlen($left) ^ strlen($right));

        return $status === 0;
    }

    /**
     * Join string with dot delimiter.
     *
     * @param string[] $values
     * @param string   $delimiter
     *
     * @return string
     */
    public static function dotted(string ...$args)
    {
        return implode('.', $args);
    }
}
