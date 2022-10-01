<?php

namespace Swilen\Routing\Exception;

class InvalidHttpHandlerException extends \Exception
{
    protected $message = '[Routing]: Invalid Http Handler Exception';

    protected $code = 500;
}
