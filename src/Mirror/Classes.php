<?php

/*
 * This file is part of the zenstruck/mirror package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Mirror;

use Zenstruck\Mirror\Internal\MirrorIterator;
use Zenstruck\MirrorClass;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @extends MirrorIterator<MirrorClass>
 *
 * @method MirrorClass[] getIterator()
 */
final class Classes extends MirrorIterator
{
    /**
     * @param iterable<MirrorClass<object>>|\Closure():MirrorClass<object> $iterator
     */
    public function __construct(private \Closure|iterable $iterator)
    {
    }

    /**
     * @return \Traversable<MirrorClass<object>>
     */
    protected function iterator(): \Traversable
    {
        yield from \is_iterable($this->iterator) ? $this->iterator : ($this->iterator)(); // @phpstan-ignore-line
    }
}
