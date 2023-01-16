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

use Zenstruck\Mirror\ClassConstants;
use Zenstruck\Mirror\Classes;
use Zenstruck\Mirror\Methods;
use Zenstruck\Mirror\Properties;
use Zenstruck\Mirror\Traits;
use Zenstruck\MirrorClass;
use Zenstruck\MirrorMethod;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template T of object
 */
trait MirrorObjectMethods
{
    use HasAttributes;

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
        return \is_a($this->name(), $class, true); // @phpstan-ignore-line
    }

    public function constants(): ClassConstants
    {
        return new ClassConstants($this->reflector);
    }

    public function properties(): Properties
    {
        return new Properties($this->reflector);
    }

    public function methods(): Methods
    {
        return new Methods($this->reflector);
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

    public function isReadonly(): bool
    {
        if (!\method_exists($this->reflector, 'isReadOnly')) {
            return false;
        }

        return $this->reflector->isReadOnly();
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
            while ($parent = $this->parent()) {
                yield $parent;
            }
        });
    }

    public function interfaces(): Classes
    {
        return new Classes(\array_map(
            static fn(\ReflectionClass $c) => new MirrorClass($c),
            $this->reflector->getInterfaces()
        ));
    }

    /**
     * @return Traits|MirrorClass<object>[]
     */
    public function traits(): Traits
    {
        return new Traits($this->reflector);
    }
}
