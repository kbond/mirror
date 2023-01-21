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
use Zenstruck\Mirror\Exception\MirrorException;
use Zenstruck\Mirror\Exception\ObjectInstanceRequired;
use Zenstruck\Mirror\Exception\PropertyTypeMismatch;
use Zenstruck\Mirror\Exception\UninitializedProperty;
use Zenstruck\Mirror\Internal\HasAttributes;
use Zenstruck\Mirror\Internal\VisibilityMethods;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template T of object
 */
final class MirrorProperty implements AttributesMirror
{
    use HasAttributes, VisibilityMethods;

    /**
     * @param T|null $object
     */
    public function __construct(private \ReflectionProperty $reflector, private ?object $object = null)
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
     * @param int-mask<MirrorType::EXACT,MirrorType::COVARIANCE,MirrorType::CONTRAVARIANCE,MirrorType::STRICT,MirrorType::VERY_STRICT> $mode
     */
    public function supports(string $type, int $mode = MirrorType::DEFAULT): bool
    {
        return $this->type()->supports($type, $mode);
    }

    public function accepts(mixed $value, bool $strict = false): bool
    {
        return $this->type()->accepts($value, $strict);
    }

    /**
     * @param T|null $object
     *
     * @throws ObjectInstanceRequired If getting instance property without object
     * @throws UninitializedProperty  If the property hasn't yet been initialized
     * @throws MirrorException
     */
    public function get(?object $object = null): mixed
    {
        if (!$this->isInitialized($object)) {
            throw new UninitializedProperty(\sprintf('Property "%s" is not yet initialized.', $this));
        }

        try {
            return $this->reflector->getValue($object ?? $this->object);
        } catch (\ReflectionException $e) {
            throw new MirrorException($e->getMessage(), $e);
        }
    }

    /**
     * @param T|null $object
     *
     * @throws PropertyTypeMismatch   If the value type is not supported by the property
     * @throws ObjectInstanceRequired If setting instance property without object
     * @throws MirrorException
     */
    public function set(mixed $value, ?object $object = null): void
    {
        $object ??= $this->object;

        if (!$object && $this->isInstance()) {
            throw new ObjectInstanceRequired(\sprintf('Property "%s" is not static so an object instance is required to set.', $this));
        }

        try {
            $object ? $this->reflector->setValue($object, $value) : $this->reflector->setValue($value);

            return;
        } catch (\TypeError $e) {
            throw new PropertyTypeMismatch($value, $this, $e);
        } catch (\ReflectionException $e) {
            throw new MirrorException($e->getMessage(), $e);
        }
    }

    public function reflector(): \ReflectionProperty
    {
        return $this->reflector;
    }

    public function hasDefault(): bool
    {
        return $this->reflector->hasDefaultValue();
    }

    /**
     * @throws MirrorException If no default exsists
     */
    public function default(): mixed
    {
        if (!$this->hasDefault()) {
            throw new MirrorException(\sprintf('Property "%s" does not have a default value.', $this));
        }

        return $this->reflector->getDefaultValue();
    }

    /**
     * @param T|null $object
     *
     * @throws ObjectInstanceRequired If checking instance property without object
     */
    public function isInitialized(?object $object = null): bool
    {
        $object ??= $this->object;

        if (!$object && $this->isInstance()) {
            throw new ObjectInstanceRequired(\sprintf('Property "%s" is not static so an object instance is required to access.', $this));
        }

        return $this->reflector->isInitialized($object ?? $this->object);
    }
}
