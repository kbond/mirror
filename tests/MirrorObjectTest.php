<?php

/*
 * This file is part of the zenstruck/mirror package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Tests;

use Zenstruck\MirrorObject;
use Zenstruck\Tests\Fixture\Object1;
use Zenstruck\Tests\Fixture\Object2;
use Zenstruck\Tests\Internal\MirrorObjectMethodsTest;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class MirrorObjectTest extends MirrorObjectMethodsTest
{
    /**
     * @test
     */
    public function can_get_set_instance_property(): void
    {
        $object = MirrorObject::for(new Object2());

        $object->set('instanceProp1', 'foo');

        $this->assertSame('foo', $object->get('instanceProp1'));
    }

    /**
     * @test
     */
    public function can_call_instance_methods(): void
    {
        $object = MirrorObject::for(new Object2());

        $this->assertSame('foo', $object->call('instanceMethod10'));
        $this->assertSame('bar', $object->call('instanceMethod10', ['bar']));
    }

    /**
     * @test
     */
    public function get_class(): void
    {
        $object = MirrorObject::for(new Object1());

        $this->assertSame(Object1::class, $object->class()->name());
    }

    /**
     * @test
     */
    public function get_object(): void
    {
        $object = MirrorObject::for($obj = new Object1());

        $this->assertSame($obj, $object->object());
    }

    /**
     * @test
     */
    public function can_clone(): void
    {
        $object = MirrorObject::for($obj = new Object2());

        $this->assertFalse($object->isInitialized('instanceProp1'));
        $this->assertFalse($object->isInitialized('instanceProp2'));

        $clone = MirrorObject::for($object->clone(['instanceProp1' => 'foo', 'instanceProp2' => 'bar']));

        $this->assertSame('foo', $clone->get('instanceProp1'));
        $this->assertSame('bar', $clone->get('instanceProp2'));
        $this->assertNotSame($obj, $clone->object());
    }

    /**
     * @test
     */
    public function reflector(): void
    {
        $this->assertSame(Object1::class, MirrorObject::for(new Object1())->reflector()->name);
    }

    /**
     * @test
     */
    public function stringable(): void
    {
        $this->assertSame(__CLASS__, (string) MirrorObject::for($this));
    }

    protected function createMirrorFor(object $object): MirrorObject
    {
        return MirrorObject::for($object);
    }
}
