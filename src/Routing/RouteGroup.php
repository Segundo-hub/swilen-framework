<?php

namespace Swilen\Routing;

final class RouteGroup
{
    /**
     * Merge route groups into a new array.
     *
     * @param array $new
     * @param array $old
     * @param bool  $prependPrefix
     *
     * @return array
     */
    public static function merge($new, $old, $prependPrefix = true)
    {
        $new = array_merge($new, [
            'prefix' => static::formatPrefix($new, $old, $prependPrefix),
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
     * @param bool  $prependPrefix
     *
     * @return string|null
     */
    private static function formatPrefix($new, $old, bool $prependPrefix = true)
    {
        $old = $old['prefix'] ?? '';

        if ($prependPrefix) {
            return isset($new['prefix']) ? trim($old, '/').'/'.trim($new['prefix'], '/') : $old;
        }

        return isset($new['prefix']) ? trim($new['prefix'], '/').'/'.trim($old, '/') : $old;
    }

    /**
     * Format the "wheres" for the new group attributes.
     *
     * @param array $new
     * @param array $old
     *
     * @return array
     */
    private static function formatMatch($new, $old)
    {
        return array_merge(
            $old['match'] ?? [],
            $new['match'] ?? []
        );
    }

    /**
     * Delete values ​​based on array keys.
     *
     * @param array $target
     *
     * @return array
     */
    private static function arrayExcept($target, array $keys)
    {
        foreach ($keys as $key) {
            unset($target[$key]);
        }

        return $target;
    }
}
