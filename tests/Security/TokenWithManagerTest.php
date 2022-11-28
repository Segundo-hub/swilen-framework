<?php

use Swilen\Container\Container;
use Swilen\Petiole\Facade;
use Swilen\Security\Contract\JwtService;
use Swilen\Security\Exception\JwtDomainException;
use Swilen\Security\Token\Jwt;
use Swilen\Security\Token\JwtSignedExpression;
use Swilen\Security\Token\Payload;

uses()->group('Security', 'Token');

define('APP_MANAGER_SECRET', 'jwt6350d205f2b4385ngfuftg');

beforeAll(function () {
    $container = Container::getInstance();

    $container->singleton(JwtService::class, function ($app) {
        return Jwt::register(APP_MANAGER_SECRET, [
            'expires' => '60s',
            'algorithm' => 'HS512',
        ]);
    });

    Facade::setFacadeApplication($container);
});

beforeEach(function () {
    $this->container = Container::getInstance();
});

afterAll(function () {
    Container::getInstance()->flush();
    Facade::flushFacadeInstances();
});

it('Create new token instance from token manager as singleton', function () {
    $tokenManager = $this->container[JwtService::class];

    expect($tokenManager)->toBeInstanceOf(Jwt::class);
    expect($tokenManager)->toBeInstanceOf(JwtService::class);
});

it('Create token signature from token manager', function () {
    /** @var JwtService */
    $manager = $this->container[JwtService::class];

    $token = $manager->sign([
        'userId' => uniqid(),
        'username' => 'bar',
        'role' => 'admin',
    ]);

    expect($token)->toBeInstanceOf(JwtSignedExpression::class);
    expect($token->plainTextToken)->toBeString();
    expect($token->payload->expires())->toBeNumeric();

    $decoded = $manager->verify($token->__toString());

    expect($decoded)->toBeInstanceOf(Payload::class);
    expect($decoded->data())->toBeArray();
    expect($decoded->expires())->toBeNumeric();
});

it('Facade is correct resolved', function () {
    $token = JwtToken::sign([
        'userId' => uniqid(),
        'username' => 'bar',
        'role' => 'admin',
    ]);

    expect($token)->toBeInstanceOf(JwtSignedExpression::class);
    expect($token->plainTextToken)->toBeString();
    expect($token->payload->expires())->toBeNumeric();

    $decoded = JwtToken::verify($token->__toString());

    expect($decoded)->toBeInstanceOf(Payload::class);
    expect($decoded->data())->toBeArray()->toHaveKeys(['userId', 'username', 'role']);
    expect($decoded->expires())->toBeNumeric();
});

it('Throw error when alrgithm not is provide', function () {
    $manager = Jwt::register(APP_MANAGER_SECRET, [
        'expires' => '160s',
    ]);

    $manager->sign([
        'data' => '',
    ]);
})->throws(JwtDomainException::class, 'Missing algorithm.');

/**
 * @method static \Swilen\Security\Token\JwtSignedExpression sign(array $payload, string $secret = null, string $algo = 'HS256')
 * @method static \Swilen\Security\Token\Payload             verify(string $token, $secret = null, ?string $algo = null)
 */
class JwtToken extends Facade
{
    protected static function getFacadeName()
    {
        return JwtService::class;
    }
}
