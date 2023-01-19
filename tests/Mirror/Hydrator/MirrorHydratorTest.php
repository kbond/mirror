<?php

/*
 * This file is part of the zenstruck/mirror package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Tests\Mirror\Hydrator;

use Zenstruck\Mirror\Hydrator;
use Zenstruck\Mirror\Hydrator\MirrorHydrator;
use Zenstruck\Tests\Mirror\HydratorTest;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class MirrorHydratorTest extends HydratorTest
{
    protected function hydrator(): Hydrator
    {
        return new MirrorHydrator();
    }
}
