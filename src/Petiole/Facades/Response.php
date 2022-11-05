<?php

namespace Swilen\Petiole\Facades;

use Swilen\Petiole\Facade;

/**
 * @method static \Swilen\Http\Response                    make(mixed $content = '', int $status = 200, array $headers = [])
 * @method static \Swilen\Http\Response\JsonResponse       send(mixed $content = null, int $status = 200, array $headers = [])
 * @method static \Swilen\Http\Response\JsonResponse       json(mixed $content = null, int $status = 200, array $headers = [])
 * @method static \Swilen\Http\Response\BinaryFileResponse file(\SplFileInfo|string $file = '', array $headers = [])
 * @method static \Swilen\Http\Response\BinaryFileResponse download(\SplFileInfo|string $file = '', string $name = ' , array $headers = [])
 * @method static \Swilen\Http\Response\StreamedResponse   stream(\Closure $callback, int $status = 200, array $headers = [])
 * @method static \Swilen\Http\Response                    withHeaders(array $headers = [])
 * @method static \Swilen\Http\Response                    headers(array $headers = [])
 * @method static \Swilen\Http\Response                    withHeader($key, $value)
 * @method static \Swilen\Http\Response                    header($key, $value)
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
        return ResponseFactory::class;
    }
}
