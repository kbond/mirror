<?php

declare(strict_types=1);

/*
 * This file is part of the zenstruck/mirror package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Tests\Mirror;

use PHPUnit\Framework\TestCase;
use Zenstruck\Mirror\Exception\FailedToHydrateValue;
use Zenstruck\Mirror\Hydrator;
use Zenstruck\Tests\Fixture\Object9;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
abstract class HydratorTest extends TestCase
{
    /**
     * @test
     */
    public function can_hydrate(): void
    {
        $object = new Object9();

        $this->assertSame('original1', $object->getProp1());
        $this->assertSame($object, $this->hydrator()($object, ['prop1' => 'new value']));
        $this->assertSame('new value', $object->getProp1());

        $object = new Object9();

        $this->assertSame('original1', $object->getProp1());

        $this->hydrator()($object);

        $this->assertSame('original1', $object->getProp1());
    }

    /**
     * @test
     */
    public function hydrate_coerces_scalar_types(): void
    {
        $object = new Object9();

        $this->assertSame('original1', $object->getProp1());
        $this->assertSame($object, $this->hydrator()($object, ['prop1' => 6.2]));
        $this->assertSame('6.2', $object->getProp1());
    }

    /**
     * @test
     */
    public function property_not_found(): void
    {
        $this->expectException(FailedToHydrateValue::class);

        $this->hydrator()(new Object9(), ['invalid' => 'value']);
    }

    /**
     * @test
     */
    public function property_not_compatible(): void
    {
        $this->expectException(FailedToHydrateValue::class);

        $this->hydrator()(new Object9(), ['prop' => ['value']]);
    }

    abstract protected function hydrator(): Hydrator;
}
