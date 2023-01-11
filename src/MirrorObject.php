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

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template T of object
 */
final class MirrorObject implements Mirror
{
    /** @use MirrorObjectMethods<T> */
    use MirrorObjectMethods;

    private \ReflectionObject $reflector;

    /**
     * @param T $object
     */
    public function __construct(private object $object)
    {
        $this->reflector = new \ReflectionObject($object);
    }

    public function __toString(): string
    {
        return $this->reflector->name;
    }

    /**
     * @param T $object
     *
     * @return self<T>
     */
    public static function for(object $object): self
    {
        return new self($object);
    }

    public function reflector(): \ReflectionObject
    {
        return $this->reflector;
    }

    /**
     * @return T
     */
    public function object(): object
    {
        return $this->object;
    }

    /**
     * @return MirrorClass<T>
     */
    public function class(): MirrorClass
    {
        return MirrorClass::for($this->object()); // @phpstan-ignore-line
    }

    /**
     * @param mixed[]|array<string,mixed>|Argument[]|Argument $arguments
     */
    public function invoke(string $method, array|Argument $arguments = []): mixed
    {
        return $this->methods()->recursive()->getOrFail($method)($arguments, $this->object);
    }

    public function get(string $property): mixed
    {
        return $this->properties()->recursive()->getOrFail($property)->get($this->object);
    }

    public function set(string $property, mixed $value): void
    {
        $this->properties()->recursive()->getOrFail($property)->set($value, $this->object);
    }
}
