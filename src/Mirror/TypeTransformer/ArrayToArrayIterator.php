<?php

/*
 * This file is part of the zenstruck/mirror package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Mirror\TypeTransformer;

use Zenstruck\Mirror\Exception\FailedToTransformType;
use Zenstruck\Mirror\TypeTransformer;
use Zenstruck\MirrorType;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ArrayToArrayIterator implements TypeTransformer
{
    public function transform(MirrorType $type, mixed $value): mixed
    {
        if (\is_array($value) && $type->supports(\ArrayIterator::class)) {
            return new \ArrayIterator($value);
        }

        throw new FailedToTransformType(\sprintf('Could not transform "%s" into an \ArrayIterator.', \get_debug_type($value)));
    }
}
