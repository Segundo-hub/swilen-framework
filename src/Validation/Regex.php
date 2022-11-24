<?php

namespace Swilen\Validation;

final class Regex
{
    public const ALPHA         = '/^[a-zA-Z]+$/';
    public const ALPHA_NUMERIC = '/[a-zA-Z0-9]+/';
    public const URL           = '/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i';
    public const UUID_V4       = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/';
    public const UUID          = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/';
}
