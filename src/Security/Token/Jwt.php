<?php

namespace Swilen\Security\Token;

use Swilen\Security\Contract\TokenContract;
use Swilen\Security\Exception\JwtDomainException;
use Swilen\Security\Exception\JwtInvalidSignatureException;
use Swilen\Security\Exception\JwtTokenExpiredException;

final class Jwt implements TokenContract
{
    /**
     * The token header.
     *
     * @var \Swilen\Security\Token\Header
     */
    private $header;

    /**
     * The shared secret key.
     *
     * @var string
     */
    private $secretKey;

    /**
     * The shared singed options.
     *
     * @var array<string,mixed>
     */
    private $signOptions = [];

    /**
     * The current algorithm for hashing.
     *
     * @var string
     */
    private $algorithm = 'HS256';

    /**
     * Collection of supported algorithms.
     *
     * @var array<string,string[]>
     */
    public const SUPPORTED_ALGORITHMS = [
        'HS256' => ['hash_hmac', 'SHA256'],
        'HS384' => ['hash_hmac', 'SHA384'],
        'HS512' => ['hash_hmac', 'SHA512'],
    ];

    /**
     * Indicated if token manager previusly confired with default values.
     *
     * @var bool
     */
    private $configured = false;

    /**
     * Configure initial values to Jwt Manager.
     *
     * @param string $secret     The secret key
     * @param array  $ignOptions Initial ign options
     *
     * ```php
     * <?php
     *  $signOptions = ['expires' => '60s', 'algorithm' => 'HS512'];
     * ````
     *
     * @return static
     */
    public static function register(string $secret, array $signOptions = [])
    {
        return (new static())->useConfig($secret, $signOptions);
    }

    /**
     * Configure initial values to Jwt Manager.
     *
     * @param string $secret     The secret key
     * @param array  $ignOptions Initial ign options
     *
     * @return $this
     */
    private function useConfig(string $secret, array $signOptions)
    {
        $this->secretKey   = $secret;
        $this->signOptions = (new ValidateSignOptions())->validate($signOptions);
        $this->configured  = true;

        return $this;
    }

    /**
     * Sign new token with claims in payload {@inheritdoc}
     *
     * @param key-of<\Swilen\Security\Token\Jwt::SUPPORTED_ALGORITHMS> $algo
     *
     * @return \Swilen\Security\Token\JwtSignedExpression
     */
    public function sign(array $payload, $secret = null, $algo = null)
    {
        $this->ensureIfPreviouslyConfigured($secret, $algo);

        $payload = $this->makePayload($payload);

        $headersEncoded = $this->header->encode();
        $payloadEncoded = $payload->encode();

        $signature = $this->signMessage(Util::dotted($headersEncoded, $payloadEncoded));

        return new JwtSignedExpression(Util::dotted(
            $headersEncoded, $payloadEncoded, $signature,
        ), $payload);
    }

    /**
     * Manage token verification {@inheritdoc}
     *
     * @param key-of<\Swilen\Security\Token\Jwt::SUPPORTED_ALGORITHMS> $algo
     *
     * @throws \Swilen\Security\Exception\JwtTokenExpiredException
     * @throws \Swilen\Security\Exception\JwtInvalidSignatureException
     */
    public function verify(string $token, $secret = null, string $algo = null)
    {
        $this->ensureIfPreviouslyConfigured($secret, $algo);

        $decoded = (new Decoder($token))->decode();

        $signature = $this->signMessage(Util::dotted(
            $decoded->header->encode(), $decoded->payload->encode(),
        ));

        return $this->validateWithErrorHandling($decoded, $signature);
    }

    /**
     * Create new hash_mac signature.
     *
     * @param string $message Message for hashing
     * @param string $secret
     *
     * @return string
     */
    private function signMessage(string $message)
    {
        [$function, $algorithm] = self::SUPPORTED_ALGORITHMS[$this->algorithm];

        return Util::url_encode(hash_hmac($algorithm, $message, $this->secretKey, true));
    }

    /**
     * Validate rules for incoming decoded token.
     *
     * @param \Swilen\Security\Token\Decoder $decoded
     * @param string                         $signature
     *
     * @return \Swilen\Security\Token\Payload
     *
     * @throws \Swilen\Security\Exception\JwtTokenExpiredException
     * @throws \Swilen\Security\Exception\JwtInvalidSignatureException
     */
    private function validateWithErrorHandling(Decoder $decoded, $signature)
    {
        if (!is_null($decoded->payload->expires()) && $decoded->payload->expires() < time()) {
            throw new JwtTokenExpiredException();
        }

        if (!$this->isValidHashSignature($decoded->signature, $signature)) {
            throw new JwtInvalidSignatureException();
        }

        return $decoded->payload;
    }

    /**
     * Verify if hash signature is valid.
     *
     * @param string $left
     * @param string $right
     *
     * @return bool
     */
    private function isValidHashSignature(string $left, string $right)
    {
        return Util::hash_equals($left, $right);
    }

    /**
     * Create new jwt header with algorithm.
     *
     * @param string|null $algo
     *
     * @return void
     *
     * @throws \Swilen\Security\Exception\JwtDomainException
     */
    private function makeHeaderWithAlgorithm($algo)
    {
        if (!isset(self::SUPPORTED_ALGORITHMS[$algo])) {
            throw new JwtDomainException(sprintf('The algorithm "%s" is not supported.', $algo), 500);
        }

        $this->algorithm = $algo;

        $this->header = new Header(['alg' => $algo, 'typ' => 'JWT']);
    }

    /**
     * Verify if payload contains valid options and create Payload instance.
     *
     * @param array $payload
     *
     * @return \Swilen\Security\Token\Payload
     */
    private function makePayload(array $payload)
    {
        if ($this->hasPreviouslyConfigured()) {
            if (!isset($payload['data'])) {
                $payload['data'] = $payload;
            }

            $payload['exp'] = time() + $this->signOptions['expires'];
        }

        if (!isset($payload['iat'])) {
            $payload['iat'] = time();
        }

        // Add default expiration time (60 seconds) if not passed
        if (!isset($payload['exp'])) {
            $payload['exp'] = time() + 60;
        }

        return new Payload($payload);
    }

    /**
     * Handle if token manager previusly configured.
     *
     * @param string $secret
     * @param string $algo
     *
     * @return void
     */
    private function ensureIfPreviouslyConfigured($secret, $algo)
    {
        // Manage if not configured
        if (!$this->hasPreviouslyConfigured()) {
            if (!$secret) {
                throw new JwtDomainException('Missing secret key.');
            }

            $this->secretKey = $secret;

            if (!$algo) {
                throw new JwtDomainException('Missing algorithm.');
            }

            return $this->makeHeaderWithAlgorithm($algo);
        }

        if ($this->header === null && $this->hasPreviouslyConfigured()) {
            if (!$algorithm = $this->signOptions['algorithm'] ?? $algo) {
                throw new JwtDomainException('Missing algorithm.');
            }

            $this->makeHeaderWithAlgorithm($algorithm);
        }
    }

    /**
     * Veify token manager if previusly configured.
     *
     * @return bool
     */
    private function hasPreviouslyConfigured()
    {
        return $this->configured === true;
    }
}
