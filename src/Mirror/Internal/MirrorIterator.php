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
use Zenstruck\Mirror\Iterator;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template T of Mirror
 * @extends Iterator<T>
 */
abstract class MirrorIterator extends Iterator
{
    /**
     * @return string[]
     */
    final public function names(): array
    {
        return \array_map(static fn(Mirror $p) => $p->name(), $this->all());
    }
}
