<?php

/*
 * This file is part of the zenstruck/mirror package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Mirror\Hydrator;

use Zenstruck\Mirror\Exception\FailedToTransformType;
use Zenstruck\MirrorType;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
interface TypeTransformer
{
    /**
     * @throws FailedToTransformType
     */
    public function transform(MirrorType $type, mixed $value): mixed;
}
