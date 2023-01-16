<?php

/*
 * This file is part of the zenstruck/mirror package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Mirror\Internal;

use Zenstruck\Mirror\Argument;
use Zenstruck\Mirror\ClassConstants;
use Zenstruck\Mirror\Classes;
use Zenstruck\Mirror\Methods;
use Zenstruck\Mirror\Properties;
use Zenstruck\Mirror\Traits;
use Zenstruck\MirrorClass;
use Zenstruck\MirrorClassConstant;
use Zenstruck\MirrorMethod;
use Zenstruck\MirrorProperty;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template T of object
 */
trait MirrorObjectMethods
{
    use HasAttributes;

    public function __toString(): string
    {
        return $this->reflector->name;
    }

    /**
     * @return class-string<T>
     */
    public function name(): string
    {
        return $this->reflector->name; // @phpstan-ignore-line
    }

    public function shortName(): string
    {
        return $this->reflector->getShortName();
    }

    public function comment(): ?string
    {
        return $this->reflector->getDocComment() ?: null;
    }

    public function namespace(): ?string
    {
        return $this->reflector->getNamespaceName() ?: null;
    }

    /**
     * @param class-string|object $class
     */
    public function isA(string|object $class): bool
    {
        return \is_a($this->name(), \is_object($class) ? $class::class : $class, true); // @phpstan-ignore-line
    }

    public function constants(): ClassConstants
    {
        return new ClassConstants($this->reflector);
    }

    /**
     * @return MirrorClassConstant<T>|null
     */
    public function constant(string $name): ?MirrorClassConstant
    {
        try {
            return $this->constantOrFail($name);
        } catch (\ReflectionException) {
            return null;
        }
    }

    /**
     * @return MirrorClassConstant<T>
     */
    public function constantOrFail(string $name): MirrorClassConstant
    {
        foreach ($this->reflectorHierarchy() as $class) {
            try {
                return MirrorClassConstant::wrap($class->getReflectionConstant($name) ?: throw new \ReflectionException()); // @phpstan-ignore-line
            } catch (\ReflectionException) {
                continue;
            }
        }

        throw new \ReflectionException(); // todo
    }

    public function hasConstant(string $name): bool
    {
        foreach ($this->reflectorHierarchy() as $class) {
            if ($class->hasConstant($name)) {
                return true;
            }
        }

        return false;
    }

    public function properties(): Properties
    {
        return new Properties($this->reflector);
    }

    /**
     * @return MirrorProperty<T>|null
     */
    public function property(string $name): ?MirrorProperty
    {
        try {
            return $this->propertyOrFail($name);
        } catch (\ReflectionException) {
            return null;
        }
    }

    /**
     * @return MirrorProperty<T>
     */
    public function propertyOrFail(string $name): MirrorProperty
    {
        foreach ($this->reflectorHierarchy() as $class) {
            try {
                return MirrorProperty::wrap($class->getProperty($name)); // @phpstan-ignore-line
            } catch (\ReflectionException) {
                continue;
            }
        }

        throw new \ReflectionException(); // todo
    }

    public function hasProperty(string $name): bool
    {
        foreach ($this->reflectorHierarchy() as $class) {
            if ($class->hasProperty($name)) {
                return true;
            }
        }

        return false;
    }

    public function methods(): Methods
    {
        return new Methods($this->reflector);
    }

    /**
     * @return MirrorMethod<T>|null
     */
    public function method(string $name): ?MirrorMethod
    {
        try {
            return $this->methodOrFail($name);
        } catch (\ReflectionException) {
            return null;
        }
    }

    /**
     * @return MirrorMethod<T>
     */
    public function methodOrFail(string $name): MirrorMethod
    {
        return MirrorMethod::wrap($this->reflector->getMethod($name)); // @phpstan-ignore-line
    }

    public function hasMethod(string $name): bool
    {
        return $this->reflector->hasMethod($name);
    }

    /**
     * @param mixed[]|array<string,mixed>|Argument[]|Argument $arguments
     */
    public function call(string $method, array|Argument $arguments = []): mixed
    {
        return $this->methodOrFail($method)->invoke($arguments, $this->object ?? null);
    }

    /**
     * @return MirrorMethod<T|object>|null
     */
    public function constructor(): ?MirrorMethod
    {
        return $this->reflector->getConstructor() ? new MirrorMethod($this->reflector->getConstructor()) : null;
    }

    public function isFinal(): bool
    {
        return $this->reflector->isFinal();
    }

    public function isExtendable(): bool
    {
        return !$this->isFinal();
    }

    public function isCloneable(): bool
    {
        return $this->reflector->isCloneable();
    }

    public function isInternal(): bool
    {
        return $this->reflector->isInternal();
    }

    public function isUserDefined(): bool
    {
        return $this->reflector->isCloneable();
    }

    public function isAnonymous(): bool
    {
        return $this->reflector->isAnonymous();
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

    public function file(): ?string
    {
        return $this->reflector->getFileName() ?: null;
    }

    /**
     * @return MirrorClass<object>
     */
    public function parent(): ?MirrorClass
    {
        $parent = $this->reflector->getParentClass();

        return $parent ? new MirrorClass($parent) : null;
    }

    public function parents(): Classes
    {
        return new Classes(function() {
            $class = $this;

            while ($class = $class->parent()) {
                yield $class;
            }
        });
    }

    public function interfaces(): Classes
    {
        return new Classes(\array_map(
            static fn(\ReflectionClass $c) => new MirrorClass($c),
            \array_values($this->reflector->getInterfaces()),
        ));
    }

    /**
     * @return Traits|MirrorClass<object>[]
     */
    public function traits(): Traits
    {
        return new Traits($this->reflector);
    }

    /**
     * @return \ReflectionClass<object>[]
     */
    private function reflectorHierarchy(): iterable
    {
        yield $this->reflector;

        $class = $this->reflector;

        while ($class = $class->getParentClass()) {
            yield $class;
        }
    }
}
