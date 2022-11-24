<?php

namespace Swilen\Shared\Support;

class Str
{
    /**
     * Determine given neddles itset in given haystack.
     *
     * @param string          $haystack
     * @param string|string[] $needles
     *
     * @return bool
     */
    public static function contains(string $haystack, $needles): bool
    {
        return static::compare($haystack, $needles, function ($haystack, $needle) {
            return $needle === '' || mb_strpos($haystack, $needle) !== false;
        });
    }

    /**
     * Determine given haystack start withn given needles.
     *
     * @param string          $haystack
     * @param string|string[] $needles
     *
     * @return bool
     */
    public static function startsWith(string $haystack, $needles): bool
    {
        return static::compare($haystack, $needles, function ($haystack, $needle) {
            return strncmp($haystack, $needle, \strlen($needle)) === 0;
        });
    }

    /**
     * Determine given haystack start withn given needles.
     *
     * @param string          $haystack
     * @param string|string[] $needles
     *
     * @return bool
     */
    public static function endsWith(string $haystack, $needles): bool
    {
        return static::compare($haystack, $needles, function ($haystack, $needle) {
            if ($haystack === '' && $needle !== '') {
                return false;
            }

            $len = strlen($needle);

            return substr_compare($haystack, $needle, -$len, $len) === 0;
        });
    }

    /**
     * High order comparer with given callback.
     *
     * @param string          $haystack
     * @param string|string[] $needles
     * @param \Closure        $callback
     *
     * @return bool
     */
    protected static function compare(string $haystack, $needles, \Closure $callback): bool
    {
        foreach ((array) $needles as $needle) {
            if ($callback($haystack, $needle)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Convert the given string to upper-case.
     *
     * @param string $value
     *
     * @return string
     */
    public static function upper(string $value)
    {
        return mb_strtoupper($value, 'UTF-8');
    }

    /**
     * Convert the given string to upper-case.
     *
     * @param string $value
     *
     * @return string
     */
    public static function lower(string $value)
    {
        return mb_strtolower($value, 'UTF-8');
    }

    /**
     * Get the string matching the given pattern.
     *
     * @param string $pattern
     * @param string $subject
     *
     * @return string
     */
    public static function match(string $pattern, string $subject)
    {
        preg_match($pattern, $subject, $matches);

        if (!$matches) {
            return '';
        }

        return $matches[1] ?? $matches[0];
    }

    /**
     * Return the length of the given string.
     *
     * @param string $value
     *
     * @return int
     */
    public static function length(string $value)
    {
        return mb_strlen($value);
    }

    /**
     * Transform and sanitize the given string.
     *
     * @param string $title
     * @param string $divider
     * @param string $default
     *
     * @return string
     */
    public static function slug($title, string $divider = '-', string $default = 'n-a')
    {
        // transliterate
        $title = iconv('utf-8', 'us-ascii//TRANSLIT', $title);

        // Convert all dashes/underscores into separator
        $flip = $divider === '-' ? '_' : '-';

        $title = preg_replace('!['.preg_quote($flip).']+!u', $divider, $title);

        // Replace @ with the word 'at'
        $title = str_replace('@', $divider.'at'.$divider, $title);

        // Remove all characters that are not the divider$divider, letters, numbers, or whitespace.
        $title = preg_replace('![^'.preg_quote($divider).'\pL\pN\s]+!u', '', static::lower($title));

        // Replace all divider$divider characters and whitespace by a single divider$divider
        $title = preg_replace('!['.preg_quote($divider).'\s]+!u', $divider, $title);

        return trim($title, $divider) ?: $default;
    }

    /**
     * Generate a random UUID (version 4).
     *
     * @return string
     */
    public static function uuid()
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xFFFF),
            mt_rand(0, 0xFFFF),
            mt_rand(0, 0xFFFF),
            mt_rand(0, 0x0FFF) | 0x4000,
            mt_rand(0, 0x3FFF) | 0x8000,
            mt_rand(0, 0xFFFF),
            mt_rand(0, 0xFFFF),
            mt_rand(0, 0xFFFF)
        );
    }

    /**
     * Check given string is a valid uuid format.
     *
     * @param string $uuid
     * @param bool   $v4   Indicates if given uuid is v4
     *
     * @return bool
     */
    public static function isUuid($uuid, bool $v4 = true)
    {
        if (!is_string($uuid)) {
            return false;
        }

        return (bool) preg_match(
            $v4 ? '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/'
                : '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/',
            $uuid
        );
    }
}
