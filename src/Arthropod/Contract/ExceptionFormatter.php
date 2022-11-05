<?php

namespace Swilen\Arthropod\Contract;

interface ExceptionFormatter
{
    /**
     * Format and transform exception.
     *
     * @return string
     */
    public function format();
}
