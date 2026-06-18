<?php

namespace App\Core\Services;

use Closure;
use Exception;

/**
 * Service Container
 * 
 * Dependency Injection Container for managing application services.
 * Supports binding, resolving, and managing service instances.
 */
class Container
{
    /**
     * Service bindings
     * 
     * @var array
     */
    protected array $bindings = [];
    
    /**
     * Resolved instances (singletons)
     * 
     * @var array
     */
    protected array $instances = [];
    
    /**
     * Service aliases
     * 
     * @var array
     */
    protected array $aliases = [];
    
    /**
     * Bind a service to the container
     * 
     * @param string $abstract
     * @param Closure|string $concrete
     * @param bool $singleton
     * @return void
     */
    public function bind(string $abstract, $concrete = null, bool $singleton = false): void
    {
        if ($concrete === null) {
            $concrete = $abstract;
        }
        
        if (!($concrete instanceof Closure)) {
            $concrete = $this->makeClosure($concrete);
        }
        
        $this->bindings[$abstract] = [
            'concrete' => $concrete,
            'singleton' => $singleton,
        ];
    }
    
    /**
     * Register a singleton
     * 
     * @param string $abstract
     * @param Closure|string $concrete
     * @return void
     */
    public function singleton(string $abstract, $concrete = null): void
    {
        $this->bind($abstract, $concrete, true);
    }
    
    /**
     * Register an instance
     * 
     * @param string $abstract
     * @param mixed $instance
     * @return void
     */
    public function instance(string $abstract, $instance): void
    {
        $this->instances[$abstract] = $instance;
        
        if (!isset($this->bindings[$abstract])) {
            $this->bindings[$abstract] = [
                'concrete' => fn() => $instance,
                'singleton' => true,
            ];
        }
    }
    
    /**
     * Register an alias
     * 
     * @param string $abstract
     * @param string $alias
     * @return void
     */
    public function alias(string $abstract, string $alias): void
    {
        $this->aliases[$alias] = $abstract;
    }
    
    /**
     * Resolve a service from the container
     * 
     * @param string $abstract
     * @return mixed
     * @throws Exception
     */
    public function resolve(string $abstract)
    {
        // Check if it's an alias
        if (isset($this->aliases[$abstract])) {
            $abstract = $this->aliases[$abstract];
        }
        
        // Return existing instance if singleton
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }
        
        // Check if binding exists
        if (!isset($this->bindings[$abstract])) {
            // Try to auto-wire the class
            return $this->autoWire($abstract);
        }
        
        $binding = $this->bindings[$abstract];
        $concrete = $binding['concrete'];
        
        // Resolve the instance
        $instance = $concrete instanceof Closure ? $concrete($this) : $concrete;
        
        // Store if singleton
        if ($binding['singleton']) {
            $this->instances[$abstract] = $instance;
        }
        
        return $instance;
    }
    
    /**
     * Check if a service is bound
     * 
     * @param string $abstract
     * @return bool
     */
    public function bound(string $abstract): bool
    {
        return isset($this->bindings[$abstract]) || isset($this->instances[$abstract]);
    }
    
    /**
     * Make a Closure from a string class name
     * 
     * @param string $concrete
     * @return Closure
     */
    protected function makeClosure(string $concrete): Closure
    {
        return function (Container $container) use ($concrete) {
            return $container->autoWire($concrete);
        };
    }
    
    /**
     * Automatically wire a class (constructor injection)
     * 
     * @param string $class
     * @return object
     * @throws Exception
     */
    protected function autoWire(string $class): object
    {
        try {
            $reflection = new \ReflectionClass($class);
            
            if (!$reflection->isInstantiable()) {
                throw new Exception("Class {$class} is not instantiable");
            }
            
            $constructor = $reflection->getConstructor();
            
            if ($constructor === null) {
                return new $class();
            }
            
            $dependencies = [];
            foreach ($constructor->getParameters() as $parameter) {
                $type = $parameter->getType();
                
                if ($type === null) {
                    if ($parameter->isDefaultValueAvailable()) {
                        $dependencies[] = $parameter->getDefaultValue();
                    } else {
                        throw new Exception(
                            "Cannot resolve dependency {$parameter->getName()} for class {$class}"
                        );
                    }
                } else {
                    $dependencyClass = $type->getName();
                    $dependencies[] = $this->resolve($dependencyClass);
                }
            }
            
            return new $class(...$dependencies);
        } catch (Exception $e) {
            throw new Exception("Cannot auto-wire class {$class}: " . $e->getMessage());
        }
    }
}
