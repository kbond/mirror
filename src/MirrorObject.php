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

use Zenstruck\Mirror\AttributesMirror;
use Zenstruck\Mirror\Internal\MirrorObjectMethods;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template T of object
 */
final class MirrorObject implements AttributesMirror
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
     * @param array<string,mixed> $properties
     */
    public function clone(array $properties = []): object
    {
        $clone = new self(clone $this->object);

        foreach ($properties as $name => $value) {
            $clone->set($name, $value);
        }

        return $clone->object;
    }

    /**
     * @return MirrorClass<T>
     */
    public function class(): MirrorClass
    {
        return MirrorClass::for($this->object()); // @phpstan-ignore-line
    }

    public function get(string $property): mixed
    {
        return $this->propertyOrFail($property)->get($this->object);
    }

    public function set(string $property, mixed $value): void
    {
        $this->propertyOrFail($property)->set($value, $this->object);
    }

    public function isInitialized(string $property): bool
    {
        return $this->propertyOrFail($property)->isInitialized($this->object);
    }
}
