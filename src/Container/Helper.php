<?php

namespace Swilen\Container;

/**
 * @internal
 *
 * Internal package for container
 */
final class Helper
{
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
