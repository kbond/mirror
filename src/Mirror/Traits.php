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

use Zenstruck\Mirror\Internal\RecursiveClassIterator;
use Zenstruck\MirrorClass;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @extends RecursiveClassIterator<MirrorClass>
 *
 * @method MirrorClass[] getIterator()
 */
final class Traits extends RecursiveClassIterator
{
    /**
     * @return MirrorClass<object>[]
     */
    protected function allForClass(\ReflectionClass $class): iterable
    {
        foreach ($class->getTraits() as $trait) {
            yield new MirrorClass($trait);

            foreach ($trait->getTraits() as $nestedTrait) {
                yield new MirrorClass($nestedTrait);

                yield from $this->allForClass($nestedTrait);
            }
        }
    }
}
