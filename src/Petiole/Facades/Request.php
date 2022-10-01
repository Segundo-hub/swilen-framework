<?php

namespace Swilen\Petiole\Facades;

/**
 * @method static array all()
 * @method static object user()
 * @method static \Swilen\Validation\Validator validate(array $rules)
 * @method static \Swilen\Http\Component\UploadedFile|null file(string $filename)
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
        return "request";
    }
}
