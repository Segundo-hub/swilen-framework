<?php

use Swilen\Container\Container;
use Swilen\Container\Exception\BindingResolutionException;

uses()->group('Container');

beforeEach(function () {
    $this->container = new Container();
});

afterEach(function () {
    unset($this->constainer);
});

it('Resolve given class target with empty constructor', function () {
    $object = $this->container->make(EmptyConstructorStub::class);
    expect($object)->toBeObject();
    expect($object->get())->toBeInt();
    expect($this->container->bindings())->toBeEmpty();
});

it('Throw exception if service not found', function () {
    (new Container())->make('Swilen\Container\EntryNotFound');
})->throws(BindingResolutionException::class, 'Target class [Swilen\Container\EntryNotFound] does not exist.');

it('Resolve given class target with constructor default value', function () {
    $object = $this->container->make(WithConstructorStub::class);
    expect($object)->toBeObject();
    expect($object->age())->toBe(20);
});

it('Resolve given class target with constructor parameters', function () {
    $object = $this->container->make(WithConstructorStub::class, ['age' => 25]);
    expect($object)->toBeObject();
    expect($object->age())->toBe(25);
});

it('Resolve resolve given class target with constructor required parameters', function () {
    $object = $this->container->make(WithConstructorRequiredStub::class, ['age' => 20]);
    expect($object)->toBeObject();
    expect($object->age())->toBe(20);
});

it('Resolve variadic class', function () {
    $instance = $this->container->make(VariadicClassStub::class, ['args' => 50]);
    expect($instance->args)->toBeArray();
});

it('Throw error in resolve given class target with constructor required parameters', function () {
    $this->container->make(WithConstructorRequiredStub::class);
})->throws(RuntimeException::class, 'Unresolvable dependency resolving [Parameter #0 [ <required> int $age ]] in class WithConstructorRequiredStub');

it('Throw error in resolve given class target is not instantiable', function () {
    $this->container->make(NotInstantiableStub::class);
})->throws(RuntimeException::class, 'Target [NotInstantiableStub] is not instantiable.');

class EmptyConstructorStub
{
    public function __construct()
    {
        // empty
    }

    public function get()
    {
        return 20;
    }
}

class WithConstructorStub
{
    protected $age;

    public function __construct(int $age = 20)
    {
        $this->age = $age;
    }

    public function age()
    {
        return $this->age;
    }
}

class WithConstructorRequiredStub
{
    protected $age;

    public function __construct(int $age)
    {
        $this->age = $age;
    }

    public function age()
    {
        return $this->age;
    }
}

class VariadicClassStub
{
    public $args;

    public function __construct(int ...$args)
    {
        $this->args = $args;
    }
}

interface NotInstantiableStub
{
}
