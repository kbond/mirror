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

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template T of object
 * @template V of AttributesMirror
 */
final class MirrorAttribute implements Mirror
{
    /**
     * @param \ReflectionAttribute<T> $reflector
     */
    public function __construct(private \ReflectionAttribute $reflector, private AttributesMirror $mirror)
    {
    }

    public function __toString(): string
    {
        return $this->name();
    }

    /**
     * @return T
     */
    public function instantiate(): object
    {
        return $this->reflector->newInstance();
    }

    public function name(): string
    {
        return $this->reflector->getName();
    }

    /**
     * @return mixed[]
     */
    public function arguments(): array
    {
        return $this->reflector->getArguments();
    }

    /**
     * @return \ReflectionAttribute<T>
     */
    public function reflector(): \ReflectionAttribute
    {
        return $this->reflector;
    }

    /**
     * @return V
     */
    public function target(): AttributesMirror
    {
        return $this->mirror; // @phpstan-ignore-line
    }
}
