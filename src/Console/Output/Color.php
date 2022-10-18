<?php

namespace Swilen\Console\Output;

class Color
{
    public const FOREGROUND_COLORS = [
        'black' => '0;30',
        'dark_gray' => '1;30',
        'blue' => '0;34',
        'light_blue' => '1;34',
        'green' => '0;32',
        'light_green' => '1;32',
        'cyan' => '0;36',
        'light_cyan' => '1;36',
        'red' => '0;31',
        'light_red' => '1;31',
        'purple' => '0;35',
        'light_purple' => '1;35',
        'brown' => '0;33',
        'yellow' => '1;33',
        'light_gray' => '0;37',
        'white' => '1;37'
    ];

    public const BACKGROUND_COLORS = [
        'black' => '40',
        'red' => '41',
        'green' => '42',
        'yellow' => '43',
        'blue' => '44',
        'magenta' => '45',
        'cyan' => '46',
        'light_gray' => '47'
    ];

    /**
     * @var string
     */
    protected $currentForeground;

    /**
     * @var string
     */
    protected $currentBackground;

    public function __construct(string $foreground = 'cyan', string $background = null)
    {
        $this->currentForeground = static::FOREGROUND_COLORS[$foreground] ?: null;
        $this->currentBackground = static::BACKGROUND_COLORS[$background] ?: null;
    }

    /**
     * @return string
     */
    public function background()
    {
        return $this->currentBackground;
    }

    /**
     * @return string
     */
    public function foreground()
    {
        return $this->currentForeground;
    }

    public function setForeground(string $color)
    {
        $this->currentForeground = $color;
    }

    public function setBackground(string $color)
    {
        $this->currentBackground = $color;
    }
}
