<?php

namespace Swilen\Petiole\Facades;

use Swilen\Petiole\Facade;

/**
 * @method static \Swilen\Http\Component\UploadedFile|null file(string $filename)
 * @method static mixed                                    input(string $key, mixed $default = null)
 * @method static mixed                                    query(string $key, mixed $default = null)
 * @method static mixed                                    server(string $key, mixed $default = null)
 * @method static array                                    all()
 * @method static \Swilen\Http\Request                     withUser(object|array $user)
 * @method static object|array|null                        user()
 * @method static \Swilen\Http\Request                     withMethod(string $method)
 * @method static string                                   getMethod()
 * @method static bool                                     isMethod(string $method)
 * @method static string                                   getPathInfo()
 * @method static string|null                              bearerToken()
 * @method static bool                                     isJsonRequest()
 * @method static bool                                     isFormRequest()
 *
 * @see \Swilen\Http\Request
 */
class Request extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeName()
    {
        return 'request';
    }
}
