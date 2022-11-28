<?php

namespace Swilen\Shared\Support;

class Arr
{
    /**
     * Determine whether the given value is array accessible.
     *
     * @param mixed $value
     *
     * @return bool
     */
    public static function accessible($value)
    {
        return is_array($value) || $value instanceof \ArrayAccess;
    }

    /**
     * Determine if the given key exists in the provided array.
     *
     * @param array<mixed>|\ArrayAccess $array
     * @param string|int                $key
     *
     * @return bool
     */
    public static function exists($array, $key)
    {
        if ($array instanceof \ArrayAccess) {
            return $array->offsetExists($key);
        }

        return key_exists($key, $array);
    }

    /**
     * Get an item from an array using "dot" notation.
     *
     * @param array<mixed>|\ArrayAccess $array
     * @param string|int|null           $key
     * @param mixed                     $default
     *
     * @return mixed
     */
    public static function get($array, $key, $default = null)
    {
        if (!static::accessible($array)) {
            return $default;
        }

        if (is_null($key)) {
            return $array;
        }

        if (static::exists($array, $key)) {
            return $array[$key];
        }

        if (strpos($key, '.') === false) {
            return $array[$key] ?? $default;
        }

        foreach ((array) explode('.', $key) as $segment) {
            if (static::accessible($array) && static::exists($array, $segment)) {
                $array = $array[$segment];
            } else {
                return $default;
            }
        }

        return $array;
    }

    /**
     * Check if an item or items exist in an array using "dot" notation.
     *
     * @param array<mixed>|\ArrayAccess $array
     * @param string|array              $keys
     *
     * @return bool
     */
    public static function has($array, $keys)
    {
        $keys = (array) $keys;

        if ((!$array || empty($array)) || $keys === []) {
            return false;
        }

        foreach ($keys as $key) {
            $items = $array;

            if (static::exists($items, $key)) {
                continue;
            }

            foreach (explode('.', $key) as $segment) {
                if (!static::accessible($items) || !static::exists($items, $segment)) {
                    return false;
                }

                $items = $items[$segment];
            }
        }

        return true;
    }

    /**
     * Set an array item to a given value using "dot" notation.
     *
     * @param array<mixed>|\ArrayAccess $array
     * @param string|null               $key
     * @param mixed                     $value
     *
     * @return array
     */
    public static function set(&$array, $key, $value)
    {
        if (is_null($key)) {
            return $array = $value;
        }

        $keys = explode('.', $key);

        foreach ($keys as $i => $key) {
            if (count($keys) === 1) {
                break;
            }

            unset($keys[$i]);

            if (!isset($array[$key]) || !is_array($array[$key])) {
                $array[$key] = [];
            }

            $array = &$array[$key];
        }

        $array[array_shift($keys)] = $value;

        return $array;
    }

    /**
     * If the given value is not an array and not null, wrap it in one.
     *
     * @param mixed $value
     *
     * @return array
     */
    public static function wrap($value)
    {
        return is_null($value) ? [] : (is_array($value) ? $value : [$value]);
    }

    /**
     * Delete values ​​based on array keys.
     *
     * @param array<mixed>|\ArrayAccess $target
     *
     * @return array
     */
    public static function except($target, array $keys)
    {
        foreach ($keys as $key) {
            unset($target[$key]);
        }

        return $target;
    }

    /**
     * Morph givent target to array.
     *
     * @param mixed $target
     *
     * @return array
     */
    public static function morph($target)
    {
        if (is_array($target)) {
            return $target;
        }

        if ($target instanceof Arrayable) {
            $target = $target->toArray();
        } elseif ($target instanceof \JsonSerializable) {
            $target = $target->jsonSerialize();
        } elseif ($target instanceof \stdClass) {
            $target = (array) $target;
        } elseif ($target instanceof \ArrayObject) {
            $target = $target->getArrayCopy();
        }

        return static::wrap($target);
    }
}
