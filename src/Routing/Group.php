<?php

namespace Swilen\Routing;

final class Group
{
    /**
     * Merge route groups into a new array.
     *
     * @param array $new
     * @param array $old
     *
     * @return array
     */
    public static function merge($new, $old)
    {
        $new = array_merge($new, [
            'prefix' => static::formatPrefix($new, $old),
            'match' => static::formatMatch($new, $old),
        ]);

        return array_merge_recursive(static::except(
            $old, ['prefix', 'match']
        ), $new);
    }

    /**
     * Format the prefix for the new group attributes.
     *
     * @param array $new
     * @param array $old
     *
     * @return string|null
     */
    private static function formatPrefix($new, $old)
    {
        $old = $old['prefix'] ?? '';

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
    private static function except($target, array $keys)
    {
        foreach ($keys as $key) {
            unset($target[$key]);
        }

        return $target;
    }
}
