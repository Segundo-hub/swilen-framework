<?php

namespace Swilen\Validation\Exception;

class MissingRequiredParameterException extends \Exception
{
    public function __construct($param, $rule)
    {
        $this->message = sprintf('Missing required parameter "%s" on rule "%s".', $param, $rule);
    }
}
