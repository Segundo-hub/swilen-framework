<?php

namespace Swilen\Petiole\Facades;

/**
 * @method static \Swilen\Http\Response make(string|resource|array|object $content = '', int $status = 200, array $headers = [])
 * @method static \Swilen\Http\Response send(string|resource|array|object $content = null, int $status = 200, array $headers = [])
 * @method static \Swilen\Http\Response file(\SplFileInfo|string $file = '', array $headers = [])
 * @method static \Swilen\Http\Response download(\SplFileInfo|string $file = '', string $name = ' , array $headers = [])
 * @method static bool isSuccessful()
 * @method static bool isInformational()
 * @method static bool isEmpty()
 * @method static \Swilen\Http\Response withHeaders(array $headers = [])
 * @method static \Swilen\Http\Response headers(array $headers = [])
 * @method static \Swilen\Http\Response withHeader($key, $value)
 * @method static \Swilen\Http\Response header($key, $value)
 *
 * @see \Swilen\Http\Response
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
        return 'response';
    }
}
