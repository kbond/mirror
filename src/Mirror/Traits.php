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
 * @method MirrorClass[]    getIterator()
 * @method MirrorClass|null first()
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

            foreach ($trait->getTraits() as $nestedTrait1) {
                yield new MirrorClass($nestedTrait1);

                foreach ($this->allForClass($nestedTrait1) as $nestedTrait2) {
                    yield $nestedTrait2;
                }
            }
        }
    }
}
