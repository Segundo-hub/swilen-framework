<?php

namespace Swilen\Security;

final class Hash
{
    /**
     * Creates a password hash.
     *
     * @param string $password — The user's password.
     * @param string|int|null $algo
     *
     * @return string
     */
    public static function make(string $password, $algo = PASSWORD_BCRYPT)
    {
        return \password_hash($password, $algo, ['cost' => 10]);
    }

    /**
     * Checks if the given hash matches the given options.
     *
     * @param string $password — The user's password.
     * @param string $hash — The hash
     *
     * @return bool
     */
    public static function check(string $password, string $hash)
    {
        return \password_verify($password, $hash);
    }
}
