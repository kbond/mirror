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

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Zenstruck\Mirror\Exception\NoSuchProperty;
use Zenstruck\Mirror\Exception\TypeMismatch;
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
    public function can_hydrate_with_type_coercion(): void
    {
        $object = new Object9();

        $this->hydrator()->set($object, 'prop1', 6.2);

        $this->assertSame('6.2', $object->getProp1());
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
    public function set_array_as_doctrine_collection(): void
    {
        $object = new Object9();

        $this->assertSame([], $object->getCollectionItems());

        $this->hydrator()->set($object, 'collectionItems', ['foo', 'bar']);

        $this->assertSame(['foo', 'bar'], $object->getCollectionItems());
    }

    /**
     * @test
     */
    public function set_doctrine_collection(): void
    {
        $object = new Object9();

        $this->assertSame([], $object->getCollectionItems());

        $this->hydrator()->set($object, 'collectionItems', new ArrayCollection(['foo', 'bar']));

        $this->assertSame(['foo', 'bar'], $object->getCollectionItems());
    }

    /**
     * @test
     */
    public function set_array_as_traversable(): void
    {
        $object = new Object9();

        $this->assertSame([], $object->getTraversableItems());

        $this->hydrator()->set($object, 'traversableItems', ['foo', 'bar']);

        $this->assertSame(['foo', 'bar'], $object->getTraversableItems());
        $this->assertInstanceOf(\ArrayIterator::class, $object->traversableItems());
    }

    /**
     * @test
     */
    public function set_traversable(): void
    {
        $object = new Object9();

        $this->assertSame([], $object->getTraversableItems());

        $this->hydrator()->set($object, 'traversableItems', new \ArrayIterator(['foo', 'bar']));

        $this->assertSame(['foo', 'bar'], $object->getTraversableItems());
    }

    /**
     * @test
     */
    public function property_not_found(): void
    {
        $this->expectException(NoSuchProperty::class);

        $this->hydrator()(new Object9(), ['invalid' => 'value']);
    }

    /**
     * @test
     */
    public function property_not_compatible(): void
    {
        $this->expectException(TypeMismatch::class);

        $this->hydrator()($object = new Object9(), ['prop1' => ['value']]);

        dd($object);
    }

    abstract protected function hydrator(): Hydrator;
}
