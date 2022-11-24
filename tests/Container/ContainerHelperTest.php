<?php

use Swilen\Container\Helper;

uses()->group('Container');

it('Get parameters from Reflection class', function () {
    $class = new ReflectionClass(GetParameterClassNameStub::class);

    $parameters = $class->getConstructor()->getParameters();

    expect(Helper::getParameterClassName($parameters[0]))->toBe('Closure');
    expect(Helper::getParameterClassName($parameters[1]))->toBeNull();

    $declaringParameters = new ReflectionParameter([ExtendingParameterNamedStub::class, 'method'], 0);

    expect(Helper::getParameterClassName($declaringParameters))->toBe(ReflectionObject::class);
});

class GetParameterClassNameStub
{
    public function __construct(Closure $callback, int $age)
    {
    }

    public function method(ReflectionObject $callback)
    {
    }
}

class ExtendingParameterNamedStub extends GetParameterClassNameStub
{
}
