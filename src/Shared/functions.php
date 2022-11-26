<?php

if (!function_exists('get_debug_type')) {
    /**
     * Get the type or object name of a variable.
     *
     * @param mixed $value
     *
     * @return string
     *
     * @codeCoverageIgnore
     */
    function get_debug_type($value): string
    {
        switch (true) {
            case is_null($value): return 'null';
            case is_bool($value): return 'bool';
            case is_string($value): return 'string';
            case is_array($value): return 'array';
            case is_int($value): return 'int';
            case is_float($value): return 'float';
            case is_object($value): break;
            default:
                if (is_null($type = @get_resource_type($value))) {
                    return 'unknown';
                }

                if ($type === 'Unknown') {
                    $type = 'closed';
                }

                return 'resource ('.$type.')';
        }

        $class = get_class($value);

        if (strpos($class, '@') === false) {
            return $class;
        }

        return (get_parent_class($class) ?: key(class_implements($class)) ?: 'class').'@anonymous';
    }
}
