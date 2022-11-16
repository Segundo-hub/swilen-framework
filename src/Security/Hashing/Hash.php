<?php

namespace Swilen\Security\Hashing;

final class Hash
{
    /**
     * Creates a password hash with bcrypt algorithm.
     *
     * @param string $password — The user's password
     * @param array  $options
     *
     * @return string
     */
    public static function make(string $password, $options = [])
    {
        return \password_hash($password, PASSWORD_BCRYPT, array_merge(['cost' => 10], $options));
    }

    /**
     * Checks if the given hash matches the given options.
     *
     * @param string $password — The user's password
     * @param string $hash     — The hash
     *
     * @return bool
     */
    public static function verify(string $password, string $hash)
    {
        return \password_verify($password, $hash);
    }
}
