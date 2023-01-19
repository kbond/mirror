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
use Zenstruck\Mirror\Hydrator\ChainHydrator;
use Zenstruck\Mirror\Hydrator\MirrorHydrator;
use Zenstruck\Mirror\Hydrator\PropertyAccessHydrator;
use Zenstruck\Tests\Fixture\Object9;
use Zenstruck\Tests\Mirror\HydratorTest;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ChainHydratorTest extends HydratorTest
{
    /**
     * @test
     */
    public function tries_all_hydrators(): void
    {
        $object = new Object9();

        $this->assertSame('original1', $object->getProp1());
        $this->assertSame('original2', $object->getProp2());

        $this->hydrator()($object, ['prop1' => 'value1', 'prop2' => 'value2']);

        $this->assertSame('value1', $object->getProp1());
        $this->assertSame('value2', $object->getProp2());
    }

    protected function hydrator(): Hydrator
    {
        return new ChainHydrator([new PropertyAccessHydrator(), new MirrorHydrator()]);
    }
}
