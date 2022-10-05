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
     * @var string
     */
    protected $signature;

    /**
     * @var string
     */
    protected $algorithm;

    /**
     * Create new Jwt instance with default values
     *
     * @param array $headers
     * @param string $algorithm
     */
    public function __construct(array $headers = [], string $algorithm = 'SHA256')
    {
        $this->headers   = $this->withHeaders($headers);
        $this->algorithm = $algorithm;
    }

    /**
     * Create new Json Web Token headers
     *
     * @param array $headers
     *
     * @return \Swilen\Security\Token\JwtHeader
     */
    private function withHeaders(array $headers)
    {
        return new JwtHeader(array_merge($headers, [
            'alg' => 'HS256',
            'typ' => 'JWT'
        ]));
    }

    /**
     * Sing Json Web Token from client
     *
     * @param array<string,string> $payload
     * @param string $secret
     *
     * @return \Swilen\Security\Token\JwtSignedExpression
     */
    public function sign(array $payload, string $secret)
    {
        $jwtPayload = new JwtPayload($payload);

        $headers = $this->headers->serialize();
        $payload = $jwtPayload->serialize();

        $signature = $this->makeSignature($headers.'.'.$payload, $secret);

        return new JwtSignedExpression($headers.'.'.$payload.'.'.$signature, $jwtPayload);
    }

    /**
     * Verify if token is valid
     *
     * @param string $token
     * @param mixed $secret The secret key
     *
     * @return \Swilen\Security\Token\JwtPayload
     */
    public function verify(string $token, $secret)
    {
        $decoded = new JwtDecode($token);
        $headers = JwtUtil::url_encode($decoded->header);
        $payload = JwtUtil::url_encode($decoded->payload);

        $signature = $this->makeSignature($headers.'.'.$payload, $secret);

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
    protected function makeSignature(string $message, string $secret)
    {
        return JwtUtil::url_encode(hash_hmac($this->algorithm, $message, $secret, true));
    }

    /**
     * Validate rules for incoming token
     *
     * @param \Swilen\Security\Token\JwtDecode $decoded
     * @param \Swilen\Security\Token\JwtPayload $payload
     * @param string $signature
     *
     * @return string
     */
    protected function validationGuard(JwtDecode $decoded, JwtPayload $payload, $signature)
    {
        if (!is_null($payload->expires()) && $payload->expires() < time()) {
            throw new JwtTokenExpiredException();
        }

        if (!$this->isValidHashSignature($decoded->signature, $signature)) {
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
    protected function isValidHashSignature(string $left, string $right)
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
}
