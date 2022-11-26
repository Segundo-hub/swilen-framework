<?php

use Psr\Container\ContainerInterface;
use Swilen\Container\Container;
use Swilen\Shared\Container\Container as ContainerContract;

uses()->group('Container');

beforeEach(function () {
    $this->container = new Container();
});

afterEach(function () {
    unset($this->constainer);
});

it('Wait for the container instance not to be broken', function () {
    expect($this->container)->toBeObject();
    expect($this->container)->toBeInstanceOf(Container::class);
    expect($this->container)->toBeInstanceOf(ContainerInterface::class);
    expect($this->container)->toBeInstanceOf(ContainerContract::class);
});

it('Bind given class into container', function () {
    $this->container->bind('abstract', function ($app) {
        return [];
    });

    expect($this->container->bound('abstract'))->toBeTrue();
    expect($this->container->make('abstract'))->toBeArray();

    // PSR Methods
    expect($this->container->has('abstract'))->toBeTrue();
    expect($this->container->get('abstract'))->toBeArray();
});

it('Bind concrete implementation into container', function () {
    $this->container->bind(RepositoryStub::class, ConcreteRepositoryStub::class);

    // PSR Methods
    expect($this->container->has(RepositoryStub::class))->toBeTrue();
    expect($this->container->get(RepositoryStub::class))->toBeInstanceOf(ConcreteRepositoryStub::class);
    expect($this->container->get(RepositoryStub::class)->get())->toBeString();
});

it('Bind same className as abstract into container', function () {
    $this->container->bind(ConcreteRepositoryStub::class);

    // PSR Methods
    expect($this->container->has(ConcreteRepositoryStub::class))->toBeTrue();
    expect($this->container->get(ConcreteRepositoryStub::class))->toBeInstanceOf(ConcreteRepositoryStub::class);

    expect($this->container->get(ConcreteRepositoryStub::class)->get())->toBeString();
});

it('Throw error if concrete in container is invalid type', function () {
    $this->container->bind('invalid', []);
})->throws(TypeError::class);

it('Bing given class as singleton', function () {
    $this->container->singleton('testing', function () {
        return new stdClass();
    });

    expect($this->container->isShared('testing'))->toBeTrue();
});

it('Register the class as singleton instance. Return only instance when done by container', function () {
    $this->container->singleton('singleton', function ($app) {
        return new TestingClassStub(18);
    });

    // Two calls for increment
    $this->container->make('singleton')->increment();
    $this->container->make('singleton')->increment();

    expect($this->container->isShared('singleton'))->toBeTrue();
    expect($this->container->make('singleton')->getProperty())->toBe(20);
});

it('Bind an alias to the container', function () {
    $this->container->alias(stdClass::class, 'testing');

    expect($this->container->isAlias('testing'))->toBeTrue();
    expect($this->container->getAlias('testing'))->toBe(stdClass::class);
    expect($this->container->make('testing'))->toBeInstanceOf(stdClass::class);
    expect($this->container->resolved('testing'))->toBeTrue();
});

it('Throw error when bind alias intself', function () {
    $this->container->alias(stdClass::class, stdClass::class);
})->throws(LogicException::class, '[stdClass] is aliased to itself.');

it('Bind existing instance into container', function () {
    $this->container->instance('bar', new stdClass());

    expect($this->container->has('bar'))->toBeTrue();
    expect($this->container->resolved('bar'))->toBeTrue();
    expect($this->container->isShared('bar'))->toBeTrue();
});

it('Bind one extender for given class', function () {
    $container = new Container();

    $container->bind('roose', function () {
        return new stdClass();
    });
    $container->extend('roose', function ($object, $app) {
        $object->property = true;

        return $object;
    });

    expect($container->make('roose'))->toHaveProperty('property');
    expect($container->make('roose')->property)->toBeTrue();

    $container->instance('instance', new stdClass());
    $container->extend('instance', function ($object, $app) {
        $object->static = true;

        return $object;
    });

    expect($container->make('instance'))->toHaveProperty('static');
    expect($container->make('instance')->static)->toBeTrue();

    $container->forgetExtenders('instance');
});

it('Forget bindings', function () {
    $this->container->bind('foo', function () {
        return 'Hola';
    });

    expect($this->container->has('foo'))->toBeTrue();
    $this->container->unbind('foo');
    expect($this->container->has('foo'))->toBeFalse();

    $this->container->instance('std', new stdClass());

    expect($this->container->has('std'))->toBeTrue();
    $this->container->forgetInstance('std');
    expect($this->container->has('std'))->toBeFalse();

    foreach (['bar' => new stdClass(), 'exe' => new Exception()] as $key => $value) {
        $this->container->instance($key, $value);
    }

    expect($this->container->has('bar'))->toBeTrue();
    expect($this->container->has('exe'))->toBeTrue();
    $this->container->forgetInstances();
    expect($this->container->has('bar'))->toBeFalse();
    expect($this->container->has('exe'))->toBeFalse();
});

it('Flush prev instances bindings', function () {
    $this->container->bind('foo', function () {
        return 'Hola';
    });

    $this->container->bind('bar', function () {
        return 'Hello';
    });

    $this->container->instance('std', new stdClass());
    $this->container->alias(stdClass::class, 'std-alias');

    expect($this->container->has('foo'))->toBeTrue();
    expect($this->container->has('bar'))->toBeTrue();
    expect($this->container->isAlias('std-alias'))->toBeTrue();
    expect($this->container->resolved('std'))->toBeTrue();

    $this->container->flush();

    expect($this->container->has('foo'))->toBeFalse();
    expect($this->container->has('bar'))->toBeFalse();
    expect($this->container->isAlias('std-alias'))->toBeFalse();
    expect($this->container->resolved('std'))->toBeFalse();
});

it('Binding method or closure into container', function () {
    $instance         = new stdClass();
    $instance->method = function () {
        return 'foo';
    };

    $this->container->bindMethod('stdMethod', $instance->method);

    expect($this->container->hasMethodBinding('stdMethod'))->toBeTrue();
    expect($this->container->callMethodBinding('stdMethod', $instance))->toBe('foo');

    $this->container->bindMethod([stdClass::class, 'method'], $instance->method);

    expect($this->container->hasMethodBinding(stdClass::class.'@method'))->toBeTrue();
    expect($this->container->callMethodBinding(stdClass::class.'@method', $instance))->toBe('foo');
});

interface RepositoryStub
{
    public function get();
}

class ConcreteRepositoryStub implements RepositoryStub
{
    public function get()
    {
        return '';
    }
}
