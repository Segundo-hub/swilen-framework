<?php

namespace Swilen\Routing;

class RouteMatching
{
    /**
     * Create regex from given pattern.
     *
     * @param string $pattern
     *
     * @return string
     */
    public static function compile(string $pattern)
    {
        $pattern = rtrim($pattern, '/') ?: '/';
        $matches = static::compileParameters($pattern);

        return static::compilePatternMatching($matches, $pattern);
    }

   /**
    * Return named paramater to array.
    *
    * @param string|null $uri
    *
    * @return array<int, mixed>
    */
   private static function compileParameters($uri)
   {
       preg_match_all('/{[^}]*}/', $uri, $matches);

       return reset($matches) ?? [];
   }

    /**
     * Compile segmented URL via uri with regex pattern.
     *
     * @param array  $matches
     * @param string $uri
     *
     * @return string
     */
    protected static function compilePatternMatching(array $matches = [], string $uri)
    {
        foreach ($matches as $key => $segment) {
            $value = trim($segment, '{\}');
            if (strpos($value, ':') !== false && !empty([$type, $keyed] = explode(':', $value, 2))) {
                $target = '{'.$type.':'.$keyed.'}';
                if ($type === 'int') {
                    $uri = str_replace($target, sprintf('(?P<%s>[0-9]+)', $keyed), $uri);
                }

                if ($type === 'alpha') {
                    $uri = str_replace($target, sprintf('(?P<%s>[a-zA-Z\_\-]+)', $keyed), $uri);
                }

                if ($type === 'string') {
                    $uri = str_replace($target, sprintf('(?P<%s>[a-zA-Z0-9\_\-]+)', $keyed), $uri);
                }
            } else {
                $uri = str_replace($segment, sprintf('(?P<%s>.*)', $value), $uri);
            }
        }

        return $uri;
    }
}
