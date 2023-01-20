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
use Zenstruck\Mirror\AttributesMirror;
use Zenstruck\Mirror\Internal\MirrorObjectMethods;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template T of object
 */
final class MirrorClass implements AttributesMirror
{
    /** @use MirrorObjectMethods<T> */
    use MirrorObjectMethods;

    /**
     * @param \ReflectionClass<T> $reflector
     */
    public function __construct(private \ReflectionClass $reflector)
    {
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

    public function isInterface(): bool
    {
        return $this->reflector->isInterface();
    }

    public function isTrait(): bool
    {
        return $this->reflector->isTrait();
    }

    public function isInstantiable(): bool
    {
        return $this->reflector->isInstantiable();
    }

    public function isAbstract(): bool
    {
        return $this->reflector->isAbstract();
    }

    public function isConcrete(): bool
    {
        return \class_exists($this->name()) && !$this->isAbstract();
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
     * @param string                                          $method    Must be public static method on this class
     * @param mixed[]|array<string,mixed>|Argument[]|Argument $arguments
     *
     * @return T
     */
    public function instantiateWith(string $method, array|Argument $arguments = []): object
    {
        $object = $this->call($method, $arguments);

        return $object instanceof $this->reflector->name ? $object : throw new \ReflectionException(); // todo
    }

    public function get(string $property): mixed
    {
        $property = $this->propertyOrFail($property);

        if (!$property->isStatic()) {
            throw new \ReflectionException(); // todo
        }

        return $property->get();
    }

    public function set(string $property, mixed $value): void
    {
        $property = $this->propertyOrFail($property);

        if (!$property->isStatic()) {
            throw new \ReflectionException(); // todo
        }

        $property->set($value);
    }
}
