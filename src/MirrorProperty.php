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

use Zenstruck\Mirror\Internal\HasAttributes;
use Zenstruck\Mirror\Internal\VisibilityMethods;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template T of object
 */
final class MirrorProperty implements Mirror
{
    use HasAttributes, VisibilityMethods;

    public function __construct(private \ReflectionProperty $reflector)
    {
        $this->reflector->setAccessible(true);
    }

    public function __toString(): string
    {
        return "{$this->reflector->class}::\${$this->reflector->name}";
    }

    /**
     * @param T|class-string<T> $class
     *
     * @return self<T>
     */
    public static function for(object|string $class, string $property): self
    {
        return new self(new \ReflectionProperty($class, $property)); // @phpstan-ignore-line
    }

    /**
     * @param \ReflectionProperty|self<T> $reflector
     *
     * @return self<T>
     */
    public static function wrap(\ReflectionProperty|self $reflector): self
    {
        return $reflector instanceof self ? $reflector : new self($reflector); // @phpstan-ignore-line
    }

    public function name(): string
    {
        return $this->reflector->name;
    }

    public function comment(): ?string
    {
        return $this->reflector->getDocComment() ?: null;
    }

    /**
     * @return MirrorClass<T>
     */
    public function class(): MirrorClass
    {
        return new MirrorClass($this->reflector->getDeclaringClass()); // @phpstan-ignore-line
    }

    public function isReadOnly(): bool
    {
        if (!\method_exists($this->reflector, 'isReadOnly')) {
            return false;
        }

        return $this->reflector->isReadOnly();
    }

    public function isModifiable(): bool
    {
        return !$this->isReadOnly();
    }

    public function isStatic(): bool
    {
        return $this->reflector->isStatic();
    }

    public function isInstance(): bool
    {
        return !$this->isStatic();
    }

    public function isPromoted(): bool
    {
        return $this->reflector->isPromoted();
    }

    public function type(): MirrorType
    {
        return new MirrorType($this->reflector->getType(), $this->reflector->class); // @phpstan-ignore-line
    }

    public function hasType(): bool
    {
        return $this->reflector->hasType();
    }

    /**
     * @param T|null $object
     */
    public function get(?object $object = null): mixed
    {
        return $this->reflector->getValue($object);
    }

    /**
     * @param T|null $object
     */
    public function set(mixed $value, ?object $object = null): void
    {
        $this->reflector->setValue($object ?? $value, $object ? $value : null);
    }

    public function reflector(): \ReflectionProperty
    {
        return $this->reflector;
    }

    public function hasDefault(): bool
    {
        return $this->reflector->hasDefaultValue();
    }

    public function default(): mixed
    {
        return $this->reflector->getDefaultValue();
    }

    public function isDefault(): bool
    {
        return $this->reflector->isDefault();
    }

    /**
     * @param T|null $object
     */
    public function isInitialized(?object $object = null): bool
    {
        return $this->reflector->isInitialized($object);
    }
}
