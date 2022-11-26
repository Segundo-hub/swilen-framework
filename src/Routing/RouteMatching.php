<?php

namespace Swilen\Routing;

class RouteMatching
{
    /**
     * Regex for match parameters in strings.
     *
     * @var string
     */
    public const MATCH_PARAMETER = '/{[^}]*}/';

    /**
     * Create regex from given pattern.
     *
     * @param string $pattern
     *
     * @return string
     */
    public static function compile(string $pattern)
    {
        if (($pattern = rtrim($pattern, '\/')) === '') {
            return '/';
        }

        return static::compilePatternMatching(
            static::compileParameters($pattern), $pattern
        );
    }

   /**
    * Return named paramater to array.
    *
    * @param string $uri
    *
    * @return array<int, mixed>
    */
   private static function compileParameters(string $pattern)
   {
       preg_match_all(static::MATCH_PARAMETER, $pattern, $matches);

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
    protected static function compilePatternMatching(array $matches, string $uri)
    {
        foreach ($matches as $segment) {
            $value = trim($segment, '{\}');

            if (strpos($value, ':') !== false && strlen($value) > 1) {
                [$type, $key] = explode(':', $value, 2);
                $target       = '{'.$type.':'.$key.'}';

                if ($type === 'int') {
                    $uri = str_replace($target, '(?<'.$key.'>\d+)', $uri);
                }

                if ($type === 'alpha') {
                    $uri = str_replace($target, '(?<'.$key.'>[a-zA-Z\_\-]+)', $uri);
                }

                if ($type === 'string') {
                    $uri = str_replace($target, '(?<'.$key.'>[a-zA-Z0-9\_\-]+)', $uri);
                }
            } else {
                $uri = str_replace($segment, '(?<'.$value.'>.*)', $uri);
            }
        }

        return $uri;
    }
}
