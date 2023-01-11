<?php

/*
 * This file is part of the zenstruck/mirror package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck;

use Zenstruck\Mirror\Argument;
use Zenstruck\Mirror\Internal\MirrorObjectMethods;
use Zenstruck\Mirror\Iterator;
use Zenstruck\Mirror\Traits;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template T of object
 */
final class MirrorClass implements Mirror
{
    /** @use MirrorObjectMethods<T> */
    use MirrorObjectMethods;

    /**
     * @param \ReflectionClass<T> $reflector
     */
    public function __construct(private \ReflectionClass $reflector)
    {
    }

    public function __toString(): string
    {
        return $this->reflector->name;
    }

    /**
     * @param class-string<T>|T $class
     *
     * @return self<T>
     */
    public static function for(string|object $class): self
    {
        return new self(new \ReflectionClass($class));
    }

    /**
     * @param \ReflectionClass<T>|self<T> $reflector
     *
     * @return self<T>
     */
    public static function wrap(\ReflectionClass|self $reflector): self
    {
        return $reflector instanceof self ? $reflector : new self($reflector);
    }

    /**
     * @return \ReflectionClass<T>
     */
    public function reflector(): \ReflectionClass
    {
        return $this->reflector;
    }

    /**
     * @return MirrorMethod<T>|null
     */
    public function constructor(): ?MirrorMethod
    {
        return $this->reflector->getConstructor() ? new MirrorMethod($this->reflector->getConstructor()) : null; // @phpstan-ignore-line
    }

    public function isClass(): bool
    {
        return $this->reflector->isInstantiable();
    }

    public function isInterface(): bool
    {
        return $this->reflector->isInterface();
    }

    public function isTrait(): bool
    {
        return $this->reflector->isTrait();
    }

    public function isAbstract(): bool
    {
        return $this->reflector->isAbstract();
    }

    public function isAnonymous(): bool
    {
        return $this->reflector->isAnonymous();
    }

    /**
     * @param mixed[]|array<string,mixed>|Argument[]|Argument $arguments
     *
     * @return T
     */
    public function instantiate(array|Argument $arguments = []): object
    {
        if (!$constructor = $this->constructor()) {
            return $this->instantiateWithoutConstructor();
        }

        return $this->reflector->newInstanceArgs($constructor->normalizeArguments($arguments));
    }

    /**
     * @return T
     */
    public function instantiateWithoutConstructor(): object
    {
        return $this->reflector->newInstanceWithoutConstructor();
    }

    /**
     * @param callable():T|string                             $callable
     * @param mixed[]|array<string,mixed>|Argument[]|Argument $arguments
     *
     * @return T
     */
    public function instantiateWith(callable|string $callable, array|Argument $arguments = []): object
    {
        if (\is_string($callable) && !\is_callable($callable)) {
            // is method on this object
            $callable = [$this->reflector->name, $callable];
        }

        if (!\is_callable($callable)) {
            throw new \ReflectionException(); // todo
        }

        $object = MirrorFunction::for($callable)($arguments);

        if (!$object instanceof $this->reflector->name) {
            throw new \ReflectionException(); // todo
        }

        return $object;
    }

    /**
     * @return self<object>
     */
    public function parent(): ?self
    {
        $parent = $this->reflector->getParentClass();

        return $parent ? new self($parent) : null;
    }

    /**
     * @return Iterator<self<object>>|self<object>[]
     */
    public function parents(): Iterator
    {
        return new Iterator(function() {
            while ($parent = $this->parent()) {
                yield $parent;
            }
        });
    }

    /**
     * @return Iterator<self<object>>|self<object>[]
     */
    public function interfaces(): Iterator
    {
        return new Iterator(\array_map(static fn(\ReflectionClass $c) => new self($c), $this->reflector->getInterfaces()));
    }

    /**
     * @return Traits|self<object>[]
     */
    public function traits(): Traits
    {
        return new Traits($this->reflector);
    }

    public function get(string $property): mixed
    {
        $property = $this->properties()->recursive()->getOrFail($property);

        if (!$property->isStatic()) {
            throw new \ReflectionException(); // todo
        }

        return $property->get();
    }

    public function set(string $property, mixed $value): void
    {
        $property = $this->properties()->recursive()->getOrFail($property);

        if (!$property->isStatic()) {
            throw new \ReflectionException(); // todo
        }

        $property->set($value);
    }
}
