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

use Zenstruck\Mirror;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template T of Mirror
 * @extends RecursiveClassIterator<T>
 */
abstract class ClassReflectorIterator extends RecursiveClassIterator
{
    protected const PUBLIC = 1;
    protected const PROTECTED = 2;
    protected const PRIVATE = 4;

    private int $flags = 0;

    final public function has(string $name): bool
    {
        foreach ($this->classes() as $class) {
            if ($this->hasForClass($class, $name)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return T
     */
    final public function get(string $name): ?object
    {
        foreach ($this->classes() as $class) {
            try {
                return $this->oneForClass($class, $name);
            } catch (\ReflectionException) {
            }
        }

        return null;
    }

    /**
     * @return T
     */
    final public function getOrFail(string $name): object
    {
        return $this->get($name) ?? throw new \ReflectionException();
    }

    /**
     * @return $this<T>
     */
    final public function public(): self
    {
        $clone = clone $this;
        $clone->flags |= static::PUBLIC;

        return $clone;
    }

    /**
     * @return $this<T>
     */
    final public function protected(): self
    {
        $clone = clone $this;
        $clone->flags |= static::PROTECTED;

        return $clone;
    }

    /**
     * @return $this<T>
     */
    final public function private(): self
    {
        $clone = clone $this;
        $clone->flags |= static::PRIVATE;

        return $clone;
    }

    /**
     * @return array{0?:int}
     */
    final protected function flags(): array
    {
        return \array_filter([$this->flags]);
    }

    /**
     * @param \ReflectionClass<object> $class
     *
     * @return T
     */
    abstract protected function oneForClass(\ReflectionClass $class, string $name): object;

    /**
     * @param \ReflectionClass<object> $class
     */
    abstract protected function hasForClass(\ReflectionClass $class, string $name): bool;
}
