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
    public static function make(string $password, $algo = null)
    {
        return \password_hash($password, $algo ?? PASSWORD_BCRYPT, ['cost' => 10]);
    }

    /**
     * Checks if the given hash matches the given options.
     *
     * @param string $password — The user's password.
     * @param string $hash — The hash
     *
     * @return bool
     */
    public static function verify(string $password, string $hash)
    {
        return \password_verify($password, $hash);
    }
}
