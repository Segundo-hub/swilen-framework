<?php

namespace Swilen\Container;

use Swilen\Container\Exception\BindingResolutionException;
use Swilen\Container\Exception\EntryNotFoundException;
use Swilen\Shared\Container\Container as ContainerContract;

class Container implements \ArrayAccess, ContainerContract
{
    /**
     * The current globally available container (if any).
     *
     * @var static
     */
    protected static $instance;

    /**
     * An array of the types that have been resolved.
     *
     * @var bool[]
     */
    protected $resolved = [];

    /**
     * The container's bindings.
     *
     * @var array[]
     */
    protected $bindings = [];

    /**
     * The container's method bindings.
     *
     * @var \Closure[]
     */
    protected $methodBindings = [];

    /**
     * The container's shared instances.
     *
     * @var object[]
     */
    protected $instances = [];

    /**
     * The registered type aliases.
     *
     * @var string[]
     */
    protected $aliases = [];

    /**
     * The registered aliases keyed by the abstract name.
     *
     * @var array[]
     */
    protected $abstractAliases = [];

    /**
     * The extension closures for services.
     *
     * @var array[]
     */
    protected $extenders = [];

    /**
     * All of the registered tags.
     *
     * @var array[]
     */
    protected $tags = [];

    /**
     * The stack of concretions currently being built.
     *
     * @var array[]
     */
    protected $buildStack = [];

    /**
     * The parameter override stack.
     *
     * @var array[]
     */
    protected $with = [];

    /**
     * The contextual binding map.
     *
     * @var array[]
     */
    public $contextual = [];

    /**
     * {@inheritdoc}
     */
    public function has(string $id): bool
    {
        return $this->bound($id);
    }

    /**
     * Determine if the given abstract type has been bound. alias for `$this->has(string $id)`.
     *
     * @param string $abstract
     *
     * @return bool
     */
    public function bound($abstract)
    {
        return isset($this->bindings[$abstract]) ||
            isset($this->instances[$abstract]) ||
            $this->isAlias($abstract);
    }

    /**
     * Determine if the given abstract type has been resolved.
     *
     * @param string $abstract
     *
     * @return bool
     */
    public function resolved($abstract)
    {
        if ($this->isAlias($abstract)) {
            $abstract = $this->getAlias($abstract);
        }

        return isset($this->resolved[$abstract]) ||
            isset($this->instances[$abstract]);
    }

    /**
     * Determine if a given type is shared.
     *
     * @param string $abstract
     *
     * @return bool
     */
    public function isShared($abstract)
    {
        return isset($this->instances[$abstract]) ||
            (isset($this->bindings[$abstract]['shared']) &&
                $this->bindings[$abstract]['shared'] === true);
    }

    /**
     * Register a binding with the container.
     *
     * @param string               $abstract
     * @param \Closure|string|null $concrete
     * @param bool                 $shared
     *
     * @return void
     *
     * @throws \TypeError
     */
    public function bind(string $abstract, $concrete = null, $shared = false): void
    {
        $this->dropStaleInstances($abstract);

        if (is_null($concrete)) {
            $concrete = $abstract;
        }

        if (!$concrete instanceof \Closure) {
            if (!is_string($concrete)) {
                throw new \TypeError(self::class.'::bind(): Argument #2 ($concrete) must be of type Closure|string|null');
            }

            $concrete = $this->getClosure($abstract, $concrete);
        }

        $this->bindings[$abstract] = compact('concrete', 'shared');
    }

    /**
     * Remove abstract from container.
     *
     * @param string $abstract
     *
     * @return void
     */
    public function unbind(string $abstract): void
    {
        unset($this->bindings[$abstract], $this->instances[$abstract], $this->resolved[$abstract]);
    }

    /**
     * Resolve closure.
     *
     * @param string $abstract
     * @param string $concrete
     *
     * @return \Closure
     */
    protected function getClosure($abstract, $concrete)
    {
        return function ($container, $parameters = []) use ($abstract, $concrete) {
            if ($abstract == $concrete) {
                return $container->build($concrete);
            }

            return $container->resolve(
                $concrete,
                $parameters
            );
        };
    }

    /**
     * Determine if the container has a method binding.
     *
     * @param string $method
     *
     * @return bool
     */
    public function hasMethodBinding($method)
    {
        return isset($this->methodBindings[$method]);
    }

    /**
     * Bind a callback to resolve with Container::call.
     *
     * @param array|string $method
     * @param \Closure     $callback
     *
     * @return void
     */
    public function bindMethod($method, $callback)
    {
        $this->methodBindings[$this->parseBindMethod($method)] = $callback;
    }

    /**
     * Get the method to be bound in class@method format.
     *
     * @param array|string $method
     *
     * @return string
     */
    protected function parseBindMethod($method)
    {
        if (is_array($method)) {
            return $method[0].'@'.$method[1];
        }

        return $method;
    }

    /**
     * Get the method binding for the given method.
     *
     * @param string $method
     * @param mixed  $instance
     *
     * @return mixed
     */
    public function callMethodBinding($method, $instance)
    {
        return call_user_func($this->methodBindings[$method], $instance, $this);
    }

    /**
     * Add a contextual binding to the container.
     *
     * @param string          $concrete
     * @param string          $abstract
     * @param \Closure|string $implementation
     *
     * @return void
     */
    public function addContextualBinding($concrete, $abstract, $implementation)
    {
        $this->contextual[$concrete][$this->getAlias($abstract)] = $implementation;
    }

    /**
     * Register a shared binding in the container.
     *
     * @param string               $abstract
     * @param \Closure|string|null $concrete
     *
     * @return void
     */
    public function singleton(string $abstract, $concrete = null): void
    {
        $this->bind($abstract, $concrete, true);
    }

    /**
     * "Extend" an abstract type in the container.
     *
     * @param string   $abstract
     * @param \Closure $closure
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public function extend($abstract, \Closure $closure)
    {
        $abstract = $this->getAlias($abstract);

        if (isset($this->instances[$abstract])) {
            $this->instances[$abstract] = $closure($this->instances[$abstract], $this);
        } else {
            $this->extenders[$abstract][] = $closure;
        }
    }

    /**
     * Register an existing instance as shared in the container.
     *
     * @param string $abstract
     * @param mixed  $instance
     *
     * @return mixed
     */
    public function instance(string $abstract, $instance)
    {
        $this->removeAbstractAlias($abstract);

        unset($this->aliases[$abstract]);

        $this->instances[$abstract] = $instance;

        return $instance;
    }

    /**
     * Remove an alias from the contextual binding alias cache.
     *
     * @param string $searched
     *
     * @return void
     */
    protected function removeAbstractAlias($searched)
    {
        if (!isset($this->aliases[$searched])) {
            return;
        }

        foreach ($this->abstractAliases as $abstract => $aliases) {
            foreach ($aliases as $index => $alias) {
                if ($alias == $searched) {
                    unset($this->abstractAliases[$abstract][$index]);
                }
            }
        }
    }

    /**
     * Assign a set of tags to a given binding.
     *
     * @param array|string $abstracts
     * @param array|mixed  ...$tags
     *
     * @return void
     */
    public function tag($abstracts, $tags)
    {
        $tags = is_array($tags) ? $tags : array_slice(func_get_args(), 1);

        foreach ($tags as $tag) {
            if (!isset($this->tags[$tag])) {
                $this->tags[$tag] = [];
            }

            foreach ((array) $abstracts as $abstract) {
                $this->tags[$tag][] = $abstract;
            }
        }
    }

    /**
     * Return iterator for given tag.
     *
     * @param string $tag
     *
     * @return \iterator|\ArrayIterator<object>
     */
    public function tagged($tag)
    {
        if (!$this->isTag($tag)) {
            return [];
        }

        return new IterableGenerator(function () use ($tag) {
            foreach ($this->tags[$tag] as $abstract) {
                yield $this->make($abstract);
            }
        }, count($this->tags[$tag]));
    }

    /**
     * Determine given value is tag.
     *
     * @param string $tag
     *
     * @return bool
     */
    public function isTag($tag)
    {
        return isset($this->tags[$tag]);
    }

    /**
     * Alias a type to a different name.
     *
     * @param string $abstract
     * @param string $alias
     *
     * @return void
     *
     * @throws \LogicException
     */
    public function alias($abstract, $alias)
    {
        if ($alias === $abstract) {
            throw new \LogicException('['.$abstract.'] is aliased to itself.');
        }

        $this->aliases[$alias] = $abstract;

        $this->abstractAliases[$abstract][] = $alias;
    }

    /**
     * Determine if a given string is an alias.
     *
     * @param string $name
     *
     * @return bool
     */
    public function isAlias($name)
    {
        return isset($this->aliases[$name]);
    }

    /**
     * Get the alias for an abstract if available.
     *
     * @param string $abstract
     *
     * @return string
     */
    public function getAlias($abstract)
    {
        return isset($this->aliases[$abstract])
            ? $this->getAlias($this->aliases[$abstract])
            : $abstract;
    }

    /**
     * Call the given Closure / class@method and inject its dependencies.
     *
     * @param callable|string      $callback
     * @param array<string, mixed> $parameters
     * @param string|null          $defaultMethod
     *
     * @return mixed
     *
     * @throws \InvalidArgumentException
     */
    public function call($callback, array $parameters = [], $defaultMethod = null)
    {
        return Method::call($this, $callback, $parameters, $defaultMethod);
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $id)
    {
        try {
            return $this->resolve($id);
        } catch (\Throwable $e) {
            if ($this->has($id) || $e instanceof \RuntimeException) {
                throw $e;
            }

            throw new EntryNotFoundException($id, is_numeric($e->getCode()) ? (int) $e->getCode() : 0, $e);
        }
    }

    /**
     * Resolve the given type from the container.
     *
     * @param string|callable $abstract
     * @param array           $parameters
     *
     * @return mixed
     */
    public function make($abstract, array $parameters = [])
    {
        return $this->resolve($abstract, $parameters);
    }

    /**
     * Resolve the given type from the container.
     *
     * @param string|callable $abstract
     * @param array           $parameters
     *
     * @return mixed
     */
    protected function resolve($abstract, $parameters = [])
    {
        $concrete = $this->getContextualConcrete($abstract = $this->getAlias($abstract));

        $needsContextualBuild = !empty($parameters) || !is_null($concrete);

        if (isset($this->instances[$abstract]) && !$needsContextualBuild) {
            return $this->instances[$abstract];
        }

        $this->with[] = $parameters;

        if (is_null($concrete)) {
            $concrete = $this->getConcrete($abstract);
        }

        $object = $this->isBuildable($concrete, $abstract)
            ? $this->build($concrete)
            : $this->make($concrete);

        // Extend current object to given extenders
        foreach ($this->getExtenders($abstract) as $extender) {
            $object = $extender($object, $this);
        }

        // Make is shared and use as singleton
        if ($this->isShared($abstract) && !$needsContextualBuild) {
            $this->instances[$abstract] = $object;
        }

        // Mark current abstract object as resolved.
        $this->resolved[$abstract] = true;

        array_pop($this->with);

        return $object;
    }

    /**
     * Get the concrete type for a given abstract.
     *
     * @param string|callable $abstract
     *
     * @return mixed
     */
    protected function getConcrete($abstract)
    {
        if (isset($this->bindings[$abstract])) {
            return $this->bindings[$abstract]['concrete'];
        }

        return $abstract;
    }

    /**
     * Get the contextual concrete binding for the given abstract.
     *
     * @param string|callable $abstract
     *
     * @return \Closure|string|array|null
     */
    protected function getContextualConcrete($abstract)
    {
        if (!is_null($binding = $this->findInContextualBindings($abstract))) {
            return $binding;
        }

        if (empty($this->abstractAliases[$abstract])) {
            return;
        }

        foreach ($this->abstractAliases[$abstract] as $alias) {
            if (!is_null($binding = $this->findInContextualBindings($alias))) {
                return $binding;
            }
        }
    }

    /**
     * Find the concrete binding for the given abstract in the contextual binding array.
     *
     * @param string|callable $abstract
     *
     * @return \Closure|string|null
     */
    protected function findInContextualBindings($abstract)
    {
        return $this->contextual[end($this->buildStack)][$abstract] ?? null;
    }

    /**
     * Determine if the given concrete is buildable.
     *
     * @param mixed  $concrete
     * @param string $abstract
     *
     * @return bool
     */
    protected function isBuildable($concrete, $abstract)
    {
        return $concrete === $abstract || $concrete instanceof \Closure;
    }

    /**
     * Instantiate a concrete instance of the given type.
     *
     * @param \Closure|string $concrete
     *
     * @return mixed
     *
     * @throws \Swilen\Container\Exception\BindingResolutionException
     */
    public function build($concrete)
    {
        if ($concrete instanceof \Closure) {
            return $concrete($this, $this->getLastParameterOverride());
        }

        try {
            $reflector = new \ReflectionClass($concrete);
        } catch (\ReflectionException $e) {
            throw new BindingResolutionException('Target class ['.$concrete.'] does not exist.', 0, $e);
        }

        // Verify if current abstract object is not instantiable.
        // Throw error if abstract is not instantiable.
        if (!$reflector->isInstantiable()) {
            return $this->rejectIfNotInstantiable($concrete);
        }

        $this->buildStack[] = $concrete;

        // Create new instance when constructor not contains dependencies.
        if (is_null($constructor = $reflector->getConstructor())) {
            array_pop($this->buildStack);

            return $reflector->newInstance();
        }

        $dependencies = $constructor->getParameters();

        try {
            $instances = $this->resolveDependencies($dependencies);
        } catch (\RuntimeException $e) {
            array_pop($this->buildStack);

            throw $e;
        }

        array_pop($this->buildStack);

        return $reflector->newInstanceArgs($instances);
    }

    /**
     * Resolve all of the dependencies from the ReflectionParameters.
     *
     * @param \ReflectionParameter[] $dependencies
     *
     * @return array
     */
    protected function resolveDependencies(array $dependencies)
    {
        $results = [];

        foreach ($dependencies as $dependency) {
            if ($this->hasParameterOverride($dependency)) {
                $results[] = $this->getParameterOverride($dependency);
                continue;
            }

            $result = is_null(Helper::getParameterClassName($dependency))
                ? $this->resolvePrimitive($dependency)
                : $this->resolveClass($dependency);

            if ($dependency->isVariadic()) {
                $results = array_merge($results, $result);
            } else {
                $results[] = $result;
            }
        }

        return $results;
    }

    /**
     * Determine if the given dependency has a parameter override.
     *
     * @param \ReflectionParameter $dependency
     *
     * @return bool
     */
    protected function hasParameterOverride($dependency)
    {
        return array_key_exists(
            $dependency->name, $this->getLastParameterOverride()
        );
    }

    /**
     * Get a parameter override for a dependency.
     *
     * @param \ReflectionParameter $dependency
     *
     * @return mixed
     */
    protected function getParameterOverride($dependency)
    {
        return $this->getLastParameterOverride()[$dependency->name];
    }

    /**
     * Get the last parameter override.
     *
     * @return array
     */
    protected function getLastParameterOverride()
    {
        return count($this->with) ? end($this->with) : [];
    }

    /**
     * Resolve a non-class hinted primitive dependency.
     *
     * @param \ReflectionParameter $parameter
     *
     * @return mixed
     */
    protected function resolvePrimitive(\ReflectionParameter $parameter)
    {
        if (!is_null($concrete = $this->getContextualConcrete('$'.$parameter->getName()))) {
            return $concrete instanceof \Closure ? $concrete($this) : $concrete;
        }

        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        $this->unresolvablePrimitive($parameter);
    }

    /**
     * Resolve a class based dependency from the container.
     *
     * @param \ReflectionParameter $parameter
     *
     * @return mixed
     */
    protected function resolveClass(\ReflectionParameter $parameter)
    {
        try {
            return $parameter->isVariadic()
                ? $this->resolveVariadicClass($parameter)
                : $this->make(Helper::getParameterClassName($parameter));
        }

        // If we can not resolve the class instance, we will check to see if the value
        // is optional, and if it is we will return the optional parameter value as
        // the value of the dependency.
        catch (BindingResolutionException $e) {
            if ($parameter->isDefaultValueAvailable()) {
                array_pop($this->with);

                return $parameter->getDefaultValue();
            }

            if ($parameter->isVariadic()) {
                array_pop($this->with);

                return [];
            }

            throw $e;
        }
    }

    /**
     * Resolve a class based variadic dependency from the container.
     *
     * @param \ReflectionParameter $parameter
     *
     * @return mixed
     */
    protected function resolveVariadicClass(\ReflectionParameter $parameter)
    {
        $abstract = $this->getAlias($className = Helper::getParameterClassName($parameter));

        if (!is_array($concrete = $this->getContextualConcrete($abstract))) {
            return $this->make($className);
        }

        return array_map(function ($abstract) {
            return $this->resolve($abstract);
        }, $concrete);
    }

    /**
     * Throw an exception that the concrete is not instantiable.
     *
     * @param string $concrete
     *
     * @return void
     */
    protected function rejectIfNotInstantiable($concrete)
    {
        $message = !empty($this->buildStack)
            ? 'Target ['.$concrete.'] is not instantiable while building ['.implode(', ', $this->buildStack).'].'
            : 'Target ['.$concrete.'] is not instantiable.';

        throw new \RuntimeException($message, 100);
    }

    /**
     * Throw an exception for an unresolvable primitive.
     *
     * @param \ReflectionParameter $parameter
     *
     * @return void
     */
    protected function unresolvablePrimitive(\ReflectionParameter $parameter)
    {
        $message = 'Unresolvable dependency resolving ['.$parameter.'] in class '.$parameter->getDeclaringClass()->getName();

        throw new \RuntimeException($message);
    }

    /**
     * Get the container's bindings.
     *
     * @return array
     */
    public function bindings()
    {
        return $this->bindings;
    }

    /**
     * Get the extender callbacks for a given type.
     *
     * @param string $abstract
     *
     * @return array
     */
    protected function getExtenders($abstract)
    {
        return $this->extenders[$this->getAlias($abstract)] ?? [];
    }

    /**
     * Remove all of the extender callbacks for a given type.
     *
     * @param string $abstract
     *
     * @return void
     */
    public function forgetExtenders($abstract)
    {
        unset($this->extenders[$this->getAlias($abstract)]);
    }

    /**
     * Drop all of the stale instances and aliases.
     *
     * @param string $abstract
     *
     * @return void
     */
    protected function dropStaleInstances($abstract)
    {
        unset($this->instances[$abstract], $this->aliases[$abstract]);
    }

    /**
     * Remove a resolved instance from the instance cache.
     *
     * @param string $abstract
     *
     * @return void
     */
    public function forgetInstance($abstract)
    {
        unset($this->instances[$abstract]);
    }

    /**
     * Clear all of the instances from the container.
     *
     * @return void
     */
    public function forgetInstances()
    {
        $this->instances = [];
    }

    /**
     * Flush the container of all bindings and resolved instances.
     *
     * @return void
     */
    public function flush()
    {
        $this->abstractAliases = [];
        $this->aliases         = [];
        $this->resolved        = [];
        $this->bindings        = [];
        $this->instances       = [];
    }

    /**
     * Add instance to container singleton.
     *
     * @param \Swilen\Shared\Container\Container $instance
     *
     * @return \Swilen\Shared\Container\Container
     */
    public static function setInstance(ContainerContract $instance = null)
    {
        return static::$instance = $instance;
    }

    /**
     * Get the globally available instance of the container.
     *
     * @return static
     */
    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            return static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * Determine if a given offset exists.
     *
     * @param string $key
     *
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function offsetExists($key)
    {
        return $this->has($key);
    }

    /**
     * Get the value at a given offset.
     *
     * @param string $key
     *
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($key)
    {
        return $this->get($key);
    }

    /**
     * Set the value at a given offset.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($key, $value)
    {
        $this->bind($key, $value instanceof \Closure ? $value : function () use ($value) {
            return $value;
        });
    }

    /**
     * Unset the value at a given offset.
     *
     * @param string $key
     *
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset($key)
    {
        $this->unbind($key);
    }
}
