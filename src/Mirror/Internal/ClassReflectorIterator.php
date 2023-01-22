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

use Zenstruck\Mirror\AttributesMirror;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template T of AttributesMirror
 * @extends RecursiveClassIterator<T>
 */
abstract class ClassReflectorIterator extends RecursiveClassIterator
{
    protected const PUBLIC = 1;
    protected const PROTECTED = 2;
    protected const PRIVATE = 4;

    private int $flags = 0;

    /**
     * @return $this<T>
     */
    final public function public(): static
    {
        $clone = clone $this;
        $clone->flags |= static::PUBLIC;

        return $clone;
    }

    /**
     * @return $this<T>
     */
    final public function protected(): static
    {
        $clone = clone $this;
        $clone->flags |= static::PROTECTED;

        return $clone;
    }

    /**
     * @return $this<T>
     */
    final public function private(): static
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
}
