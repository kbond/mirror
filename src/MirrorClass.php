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
use Zenstruck\Mirror\Exception\MirrorException;
use Zenstruck\Mirror\Exception\NoSuchMethod;
use Zenstruck\Mirror\Exception\ParameterTypeMismatch;
use Zenstruck\Mirror\Exception\UnresolveableArgument;
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
     *
     * @throws UnresolveableArgument
     * @throws ParameterTypeMismatch
     */
    public function instantiate(array|Argument $arguments = []): object
    {
        if (!$constructor = $this->constructor()) {
            return $this->instantiateWithoutConstructor();
        }

        $arguments = $constructor->normalizeArguments($arguments);

        try {
            return $this->reflector->newInstanceArgs($arguments);
        } catch (\TypeError $e) {
            throw ParameterTypeMismatch::for($e, $arguments, $constructor->parameters());
        }
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
     *
     * @throws NoSuchMethod
     * @throws UnresolveableArgument
     * @throws ParameterTypeMismatch
     * @throws MirrorException
     */
    public function instantiateWith(string $method, array|Argument $arguments = []): object
    {
        $method = $this->methodOrFail($method);
        $object = $method->invoke($arguments);

        return $object instanceof $this->reflector->name ? $object : throw new MirrorException(\sprintf('Method "%s" must return an instance of "%s".', $method, $this));
    }
}
