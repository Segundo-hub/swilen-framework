<?php

namespace Swilen\Security\Token;

use Swilen\Security\Exception\JwtDomainException;

class ValidateSignOptions
{
    /**
     * Valid time suffixes in string.
     *
     * @var string
     */
    protected const SECOND_SUFFIX = 's';
    protected const MINUTE_SUFFIX = 'm';
    protected const HOUR_SUFFIX = 'h';
    protected const DAY_SUFFIX = 'd';

    /**
     * Time hash table with time valued in seconds.
     *
     * @var array<string, int>
     */
    protected $timesSuffix = [
        self::SECOND_SUFFIX => 1,
        self::MINUTE_SUFFIX => 60,
        self::HOUR_SUFFIX => 3600,
        self::DAY_SUFFIX => 86400,
    ];

    /**
     * Validate sign options for configure initial Jwt Instance.
     *
     * @param array $signOptions
     *
     * @return array
     */
    public function validate(array $signOptions)
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

        $suffixs = array_keys($this->timesSuffix);

        if (!in_array($suffix = substr($expires, -1), $suffixs, true)) {
            throw new JwtDomainException(sprintf('The "%s" is not valid time suffix. Valid options: %s', $suffix, implode(', ', $suffixs)));
        }

        if (!is_numeric($value = substr($expires, 0, -1))) {
            throw new JwtDomainException('Expires options expect to int value with time prefix like "60s"');
        }

        $signOptions['expires'] = intval($value) * $this->timesSuffix[$suffix];

        return $signOptions;
    }
}
