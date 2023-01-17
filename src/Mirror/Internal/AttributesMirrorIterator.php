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
 * @extends MirrorIterator<T>
 */
abstract class AttributesMirrorIterator extends MirrorIterator
{
    /**
     * @template V of object
     *
     * @param class-string<V> $name
     */
    final public function withAttribute(string $name, bool $instanceOf = false): static
    {
        return $this->filter(fn(AttributesMirror $m) => $m->attributes()->has($name, $instanceOf));
    }
}
