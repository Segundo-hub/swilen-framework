<?php

namespace Swilen\Routing\Exception;

class InvalidHttpHandlerException extends \Exception
{
    protected $message = 'Invalid Route Handler Exception';

    protected $code = 500;
}
