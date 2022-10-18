<?php

namespace Swilen\Routing\Exception;

class InvalidHttpHandlerException extends \Exception
{
    protected $message = 'Invalid Http Router Handler Exception';

    protected $code = 500;
}
