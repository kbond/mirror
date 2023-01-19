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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Zenstruck\Mirror\Exception\FailedToTransformType;
use Zenstruck\Mirror\TypeTransformer;
use Zenstruck\MirrorType;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ArrayToDoctrineCollection implements TypeTransformer
{
    public function transform(MirrorType $type, mixed $value): mixed
    {
        if (!\interface_exists(Collection::class)) {
            throw new FailedToTransformType('doctrine/collection not available.');
        }

        if (\is_array($value) && $type->supports(ArrayCollection::class, MirrorType::DEFAULT | MirrorType::STRICT)) {
            // strict because ArrayCollection is stringable
            return new ArrayCollection($value);
        }

        throw new FailedToTransformType(\sprintf('Could not transform "%s" into doctrine collection.', \get_debug_type($value)));
    }
}
