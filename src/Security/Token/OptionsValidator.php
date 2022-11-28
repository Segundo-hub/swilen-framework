<?php

namespace Swilen\Security\Token;

use Swilen\Security\Exception\JwtDomainException;

class OptionsValidator
{
    /**
     * Time hash table with time valued in seconds.
     *
     * @var array<string, int>
     */
    protected const TIME_SUFFIXES = [
        's' => 1,
        'm' => 60,
        'h' => 3600,
        'd' => 86400,
    ];

    /**
     * Validate sign options for configure initial Jwt Instance.
     *
     * @param array $signOptions
     *
     * @return array
     */
    public static function validate(array $signOptions)
    {
        $allowedOptions = ['expires', 'algorithm', 'issued'];

        foreach ($signOptions as $key => $value) {
            if (!in_array($key, $allowedOptions, true)) {
                throw new JwtDomainException(sprintf('The "%s" is not valid options. Valid options: %s', $key, implode(', ', $allowedOptions)));
            }
        }

        if (!$expires = $signOptions['expires'] ?? false) {
            throw new JwtDomainException('Missing expires option');
        }

        $suffixs = array_keys(static::TIME_SUFFIXES);

        if (!in_array($suffix = substr($expires, -1), $suffixs, true)) {
            throw new JwtDomainException(sprintf('The "%s" is not valid time suffix. Valid options: %s', $suffix, implode(', ', $suffixs)));
        }

        if (!is_numeric($value = substr($expires, 0, -1))) {
            throw new JwtDomainException('Expires options expect to int value with time prefix like "60s"');
        }

        $signOptions['expires'] = intval($value) * static::TIME_SUFFIXES[$suffix];

        return $signOptions;
    }
}
