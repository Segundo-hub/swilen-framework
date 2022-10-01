<?php

namespace Swilen\Routing;

final class RouteGroup
{
    /**
     * Merge route groups into a new array.
     *
     * @param array $new
     * @param array $old
     * @param bool $prependExistingPrefix
     *
     * @return array
     */
    public static function merge($new, $old, $prependExistingPrefix = true)
    {
        $new = array_merge([
            'prefix' => static::formatPrefix($new, $old, $prependExistingPrefix),
            'match' => static::formatMatch($new, $old),
        ]);

        return array_merge_recursive(static::arrayExcept(
            $old,
            ['prefix', 'match']
        ), $new);
    }

    /**
     * Format the prefix for the new group attributes.
     *
     * @param array $new
     * @param array $old
     * @param bool $prependExistingPrefix
     *
     * @return string|null
     */
    protected static function formatPrefix($new, $old, $prependExistingPrefix = true)
    {
        $old = $old['prefix'] ?? '';

        if ($prependExistingPrefix) {
            return isset($new['prefix']) ? trim($old, '/') . '/' . trim($new['prefix'], '/') : $old;
        } else {
            return isset($new['prefix']) ? trim($new['prefix'], '/') . '/' . trim($old, '/') : $old;
        }
    }

    /**
     * Format the "wheres" for the new group attributes.
     *
     * @param  array  $new
     * @param  array  $old
     *
     * @return array
     */
    protected static function formatMatch($new, $old)
    {
        return array_merge(
            $old['match'] ?? [],
            $new['match'] ?? []
        );
    }

    protected static function arrayExcept($target, array $keys)
    {
        foreach ($keys as $key) {
            unset($target[$key]);
        }
        return $target;
    }
}
