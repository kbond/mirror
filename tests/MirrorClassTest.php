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

use Zenstruck\MirrorClass;
use Zenstruck\Tests\Fixture\AbstractObject1;
use Zenstruck\Tests\Fixture\Object1;
use Zenstruck\Tests\Fixture\Object4;
use Zenstruck\Tests\Fixture\Object6;
use Zenstruck\Tests\Internal\MirrorObjectMethodsTest;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class MirrorClassTest extends MirrorObjectMethodsTest
{
    /**
     * @test
     */
    public function abstract_class(): void
    {
        $class = MirrorClass::for(AbstractObject1::class);

        $this->assertTrue($class->isAbstract());
        $this->assertTrue($class->isExtendable());
        $this->assertFalse($class->isInstantiable());
        $this->assertFalse($class->isTrait());
        $this->assertFalse($class->isInterface());

        $methods = $class->methods();

        $this->assertEmpty($methods->abstract()->static());
        $this->assertSame(['finalMethod'], $methods->concrete()->instance()->final()->names());
        $this->assertSame(['finalStaticMethod'], $methods->final()->static()->names());
        $this->assertSame(['extendableStaticMethod'], $methods->extendable()->static()->names());
        $this->assertSame(['finalMethod'], $methods->final()->instance()->names());
        $this->assertSame(['extendableMethod'], $methods->extendable()->concrete()->instance()->names());
        $this->assertSame(['abstractMethod'], $methods->extendable()->abstract()->instance()->names());
    }

    /**
     * @test
     */
    public function instantiate(): void
    {
        $object = MirrorClass::for(Object6::class)->instantiate();

        $this->assertInstanceOf(Object6::class, $object);
        $this->assertSame('constructor', $object->prop);

        $object = MirrorClass::for(Object6::class)->instantiate(['foo']);

        $this->assertInstanceOf(Object6::class, $object);
        $this->assertSame('foo', $object->prop);

        $this->assertInstanceOf(Object4::class, MirrorClass::for(Object4::class)->instantiate());
    }

    /**
     * @test
     */
    public function instantiate_without_constructor(): void
    {
        $object = MirrorClass::for(Object6::class)->instantiateWithoutConstructor();

        $this->assertInstanceOf(Object6::class, $object);
        $this->assertSame('original', $object->prop);
    }

    /**
     * @test
     */
    public function instantiate_with(): void
    {
        $object = MirrorClass::for(Object6::class)->instantiateWith('factory');

        $this->assertInstanceOf(Object6::class, $object);
        $this->assertSame('factory', $object->prop);

        $object = MirrorClass::for(Object6::class)->instantiateWith('factory', ['foo']);

        $this->assertInstanceOf(Object6::class, $object);
        $this->assertSame('foo', $object->prop);

        $object = MirrorClass::for(Object6::class)->instantiateWith(fn(string $prop = 'closure') => new Object6($prop));

        $this->assertInstanceOf(Object6::class, $object);
        $this->assertSame('closure', $object->prop);

        $object = MirrorClass::for(Object6::class)->instantiateWith(fn(string $prop = 'closure') => new Object6($prop), ['bar']);

        $this->assertInstanceOf(Object6::class, $object);
        $this->assertSame('bar', $object->prop);
    }

    /**
     * @test
     */
    public function stringable(): void
    {
        $this->assertSame(__CLASS__, (string) MirrorClass::for(__CLASS__));
    }

    /**
     * @test
     */
    public function reflector(): void
    {
        $this->assertSame(Object1::class, MirrorClass::for(new Object1())->reflector()->name);
    }

    /**
     * @test
     */
    public function wrap(): void
    {
        $mirror = MirrorClass::wrap(new \ReflectionClass(self::class));

        $this->assertSame($mirror, MirrorClass::wrap($mirror));
    }

    protected function createMirrorFor(object $object): MirrorClass
    {
        return MirrorClass::for($object);
    }
}
