<?php

namespace Swilen\Validation;

final class Regex
{
    public const ALPHA = '/^[a-zA-Z]+$/';
    public const ALPHA_NUMERIC = '/[a-zA-Z0-9]+/';
    public const URL = '/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i';
}
