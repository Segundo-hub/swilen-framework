<?php

namespace Swilen\Petiole\Facades;

use Swilen\Petiole\Facade;

/**
 * @method static \Swilen\Validation\Validator             validate(array $rules)
 * @method static \Swilen\Http\Component\UploadedFile|null file(string $filename)
 * @method static mixed                                    query(string|int $key, mixed $default = null)
 * @method static mixed                                    input(string|int $key, mixed $default = null)
 * @method static array                                    all()
 * @method static object|array|null                        user()
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
