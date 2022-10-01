<?php

namespace Swilen\Container;

/**
 * @internal
 */
final class Helper
{
    /**
     * If the given value is not an array and not null, wrap it in one.
     *
     * @param mixed $value
     *
     * @return array
     */
    public static function arrayWrap($value)
    {
        return is_null($value)
            ? [] : (is_array($value) ? $value : [$value]);
    }

    /**
     * Return the default value of the given value.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public static function unwrapIfClosure($value)
    {
        return $value instanceof \Closure ? $value() : $value;
    }

    /**
     * Get the class name of the given parameter's type, if possible.
     *
     * @param \ReflectionParameter $parameter
     *
     * @return string|null
     */
    public static function getParameterClassName($parameter)
    {
        $type = $parameter->getType();

        if (!$type instanceof \ReflectionNamedType || $type->isBuiltin()) {
            return null;
        }

        $name = $type->getName();

        if (!is_null($class = $parameter->getDeclaringClass())) {
            if ($name === 'self') {
                return $class->getName();
            }

            if ($name === 'parent' && $parent = $class->getParentClass()) {
                return $parent->getName();
            }
        }

        return $name;
    }
}
