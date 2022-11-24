<?php

namespace Swilen\Routing;

use Swilen\Shared\Support\Arr;

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
            'where' => static::formatWhere($new, $old),
        ]);

        return array_merge_recursive(Arr::except(
            $old, ['prefix', 'where']
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

        return isset($new['prefix']) ? trim($old, '/').'/'.trim($new['prefix'], '/') : $old;
    }

    /**
     * Format the "wheres" for the new group attributes.
     *
     * @param array $new
     * @param array $old
     *
     * @return array
     */
    private static function formatWhere($new, $old)
    {
        return array_merge(
            $old['where'] ?? [],
            $new['where'] ?? []
        );
    }
}
