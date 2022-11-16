<?php

namespace Swilen\Validation\Exception;

class RuleNotFoundException extends \Exception
{
    public function __construct($rule)
    {
        $this->message = sprintf('This rule "%s" is not registered.', $rule);
    }
}
