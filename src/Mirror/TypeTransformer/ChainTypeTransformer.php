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
final class ChainTypeTransformer implements TypeTransformer
{
    private static ArrayToDoctrineCollection $arrayToDoctrineCollection;
    private static ArrayToArrayIterator $arrayToArrayIterator;

    /**
     * @param TypeTransformer[] $transformers
     */
    public function __construct(private iterable $transformers = [])
    {
    }

    public function transform(MirrorType $type, mixed $value): mixed
    {
        foreach ($this->transformers() as $transformer) {
            try {
                return $transformer->transform($type, $value);
            } catch (FailedToTransformType) {
                continue;
            }
        }

        throw new FailedToTransformType(\sprintf('No transformer can transform "%s" to "%s".', \get_debug_type($value), $type));
    }

    /**
     * @return TypeTransformer[]
     */
    private function transformers(): iterable
    {
        yield from $this->transformers;

        yield self::$arrayToArrayIterator ??= new ArrayToArrayIterator();
        yield self::$arrayToDoctrineCollection ??= new ArrayToDoctrineCollection();
    }
}
