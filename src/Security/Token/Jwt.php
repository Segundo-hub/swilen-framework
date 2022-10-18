<?php

namespace Swilen\Security\Token;

use Swilen\Security\Exception\JwtInvalidSignatureException;
use Swilen\Security\Exception\JwtTokenExpiredException;

final class Jwt
{
    /**
     * @var \Swilen\Security\Token\JwtHeader
     */
    protected $headers;

    /**
     * The shared secret key
     *
     * @var string
     */
    protected $secretKey;

    /**
     * The shared singed options
     *
     * @var array<string,mixed>
     */
    protected $signOptions = [];

    /**
     * The current algorithm for hashing
     *
     * @var string
     */
    protected $algorithm = 'HS256';

    /**
     * Collection of supported algorithms
     *
     * @var array<string,string[]>
     */
    protected $supportedAlgorithms = [
        'HS256' => ['hash_hmac', 'SHA256'],
        'HS384' => ['hash_hmac', 'SHA384'],
        'HS512' => ['hash_hmac', 'SHA512'],
    ];

    /**
     * Indicated if token manager previusly confired with default values
     *
     * @var boolean
     */
    protected $configured = false;

    public function register(string $secret, array $signOptions = [])
    {
        $this->secretKey   = $secret;
        $this->signOptions = $signOptions;
        $this->configured  = true;

        return $this;
    }

    /**
     * @param string $algo
     *
     * @return \Swilen\Security\Token\JwtHeader
     * @throws \InvalidArgumentException
     */
    protected function withAlgorithmHeader(string $algo)
    {
        if (isset($this->supportedAlgorithms[$algo])) {
            $this->algorithm = $algo;

            return $this->header = new JwtHeader(['alg' => $algo, 'typ' => 'JWT']);
        }

        throw new \InvalidArgumentException(sprintf('"%s" this algorithm is not supported', $algo), 500);
    }

    /**
     * Sing Json Web Token from client
     *
     * @param array<string,string> $payload
     * @param string|null $secret
     * @param string $algo
     *
     * @return \Swilen\Security\Token\JwtSignedExpression
     */
    public function sign(array $payload, $secret = null, $algo = 'HS256')
    {
        $this->handleSecretIfConfigured($secret);

        $jwtPayload = new JwtPayload(array_merge($payload, $this->signOptions));
        $jwtHeaders = $this->withAlgorithmHeader($algo);

        $headers = $jwtHeaders->serialize();
        $payload = $jwtPayload->serialize();

        $signature = $this->buildSignature($this->join($headers, $payload));

        return new JwtSignedExpression($this->join($headers, $payload, $signature), $jwtPayload);
    }

    /**
     * Verify if token is valid
     *
     * @param string $token
     * @param string|null $secret The secret key
     *
     * @return \Swilen\Security\Token\JwtPayload
     */
    public function verify(string $token, $secret = null)
    {
        $this->handleSecretIfConfigured($secret);

        $decoded = new JwtDecoder($token);
        $headers = JwtUtil::url_encode($decoded->header);
        $payload = JwtUtil::url_encode($decoded->payload);

        $signature = $this->buildSignature($this->join($headers, $payload));

        $payload = (new JwtPayload())->fromJson($decoded->payload);

        return $this->validationGuard($decoded, $payload, $signature);
    }

    /**
     * Create new hash_mac signature
     *
     * @param string $message Message for hashing
     * @param string $secret
     *
     * @return string
     */
    protected function buildSignature(string $message)
    {
        [$function, $algorithm] = $this->supportedAlgorithms[$this->algorithm];

        return JwtUtil::url_encode(hash_hmac($algorithm, $message, $this->secretKey, true));
    }

    /**
     * Validate rules for incoming token
     *
     * @param \Swilen\Security\Token\JwtDecoder $decoded
     * @param \Swilen\Security\Token\JwtPayload $payload
     * @param string $signature
     *
     * @return string
     */
    protected function validationGuard(JwtDecoder $decoded, JwtPayload $payload, $signature)
    {
        if (!is_null($payload->expires()) && $payload->expires() < time()) {
            throw new JwtTokenExpiredException();
        }

        if (!static::isValidHashSignature($decoded->signature, $signature)) {
            throw new JwtInvalidSignatureException();
        }

        return $payload;
    }

    /**
     * Verify if hash signature is valid
     *
     * @param string $left
     * @param string $right
     *
     * @return bool
     */
    protected static function isValidHashSignature(string $left, string $right)
    {
        if (function_exists('hash_equals')) {
            return hash_equals($left, $right);
        }

        $len = min(strlen($left), strlen($right));

        $status = 0;
        for ($i = 0; $i < $len; $i++) {
            $status |= (ord($left[$i]) ^ ord($right[$i]));
        }

        $status |= (strlen($left) ^ strlen($right));

        return $status === 0;
    }

    /**
     * Join string with dot delimiter
     *
     * @param string|string[] ...$args
     *
     * @return string
     */
    protected function join(...$args)
    {
        return implode('.', $args);
    }

    protected function handleSecretIfConfigured($secret)
    {
        if ($this->configured === false) {
            if (!$secret) {
                throw new \InvalidArgumentException('Missing secret key', 500);
            }
        }

        if ($this->configured === false && !$secret) {
        } elseif ($this->configured === false) {
            $this->secretKey = $secret;
        }
    }
}
