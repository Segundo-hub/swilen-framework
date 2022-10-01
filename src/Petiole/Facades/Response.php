<?php

namespace Swilen\Petiole\Facades;

use Swilen\Http\ResponseFactory;

/**
 * @method static \Swilen\Http\ResponseFactory make(string|resource|array|object $content = '', int $status = 200, array $headers = [])
 * @method static \Swilen\Http\ResponseFactory send(string|resource|array|object $content = '', int $status = 200, array $headers = [])
 * @method static \Swilen\Http\ResponseFactory file(\SplFileInfo|string $file = '', array $headers = [])
 * @method static \Swilen\Http\ResponseFactory download(\SplFileInfo|string $file = '', string $name = ' , array $headers = [])
 *
 * @see \Swilen\Http\ResponseFactory
 */

class Response extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeName()
    {
        return ResponseFactory::class;
    }
}
