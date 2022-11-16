<?php

use Swilen\Container\Helper;

uses()->group('Container');

it('Unwrap if Array Container herlper', function () {
    expect(Helper::arrayWrap(null))->toBe([]);
    expect(Helper::arrayWrap('null'))->toBe(['null']);
    expect(Helper::arrayWrap(['foo', 'bar']))->toBe(['foo', 'bar']);
});

it('Unwrap if Closure Container herlper', function () {
    expect(Helper::unwrapIfClosure(null))->toBeNull();
    expect(Helper::unwrapIfClosure(function () {
        return 25;
    }))->toBe(25);
});

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
