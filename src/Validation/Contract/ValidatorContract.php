<?php

namespace Swilen\Validation\Contract;

interface ValidatorContract
{
    public function validate(array $rules);

    public function fails();
}
