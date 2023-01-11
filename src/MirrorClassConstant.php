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
final class MirrorClassConstant implements Mirror
{
    use HasAttributes, VisibilityMethods;

    public function __construct(private \ReflectionClassConstant $reflector)
    {
    }

    public function __toString(): string
    {
        return "{$this->reflector->class}::{$this->reflector->name}";
    }

    /**
     * @param T|class-string<T> $class
     *
     * @return self<T>
     */
    public static function for(object|string $class, string $method): self
    {
        return new self(new \ReflectionClassConstant($class, $method)); // @phpstan-ignore-line
    }

    /**
     * @param \ReflectionClassConstant|self<T> $reflector
     *
     * @return self<T>
     */
    public static function wrap(\ReflectionClassConstant|self $reflector): self
    {
        return $reflector instanceof self ? $reflector : new self($reflector); // @phpstan-ignore-line
    }

    public function name(): string
    {
        return $this->reflector->name;
    }

    public function isFinal(): bool
    {
        if (!\method_exists($this->reflector, 'isFinal')) {
            return false;
        }

        return $this->reflector->isFinal();
    }

    public function isExtendable(): bool
    {
        return !$this->isFinal();
    }

    public function reflector(): \ReflectionClassConstant
    {
        return $this->reflector;
    }
}
