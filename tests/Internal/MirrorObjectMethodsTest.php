<?php

/*
 * This file is part of the zenstruck/mirror package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Tests\Internal;

use PHPUnit\Framework\TestCase;
use Zenstruck\MirrorClass;
use Zenstruck\MirrorObject;
use Zenstruck\Tests\Fixture\Attribute1;
use Zenstruck\Tests\Fixture\Interface1;
use Zenstruck\Tests\Fixture\Interface2;
use Zenstruck\Tests\Fixture\Interface3;
use Zenstruck\Tests\Fixture\Object1;
use Zenstruck\Tests\Fixture\Object2;
use Zenstruck\Tests\Fixture\Object3;
use Zenstruck\Tests\Fixture\Php81Object;
use Zenstruck\Tests\Fixture\Php82Object;
use Zenstruck\Tests\Fixture\Trait1;
use Zenstruck\Tests\Fixture\Trait2;
use Zenstruck\Tests\Fixture\Trait3;
use Zenstruck\Tests\Fixture\Trait4;
use Zenstruck\Tests\Fixture\Trait5;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
abstract class MirrorObjectMethodsTest extends TestCase
{
    /**
     * @test
     */
    public function info(): void
    {
        $mirror = $this->createMirrorFor(new Object2());

        $this->assertFalse($mirror->isFinal());
        $this->assertTrue($mirror->isExtendable());
        $this->assertFalse($mirror->isInternal());
        $this->assertTrue($mirror->isUserDefined());
        $this->assertFalse($mirror->isReadOnly());
        $this->assertTrue($mirror->isModifiable());
        $this->assertFalse($mirror->isAnonymous());
        $this->assertTrue($mirror->isCloneable());
        $this->assertTrue($mirror->isA(Object1::class));
        $this->assertTrue($mirror->isA(new Object2()));
        $this->assertFalse($mirror->isA(new Object3()));
        $this->assertSame('Object2', $mirror->shortName());
        $this->assertSame(\str_replace('\\Object2', '', Object2::class), $mirror->namespace());
        $this->assertSame("/**\n * @author Kevin Bond <kevinbond@gmail.com>\n */", $mirror->comment());
        $this->assertSame((new \ReflectionClass(Object2::class))->getFileName(), $mirror->file());
        $this->assertNull($this->createMirrorFor(new \SplFileInfo('foo'))->file());
        $this->assertNull($this->createMirrorFor(new \SplFileInfo('foo'))->comment());
        $this->assertNull($this->createMirrorFor(new \SplFileInfo('foo'))->namespace());
    }

    /**
     * @test
     */
    public function set_get_static_properties(): void
    {
        $mirror = $this->createMirrorFor(new Object2());

        $mirror->set('staticProp1', 'foo');

        $this->assertSame('foo', $mirror->get('staticProp1'));
    }

    /**
     * @test
     */
    public function call_static_methods(): void
    {
        $mirror = $this->createMirrorFor(new Object2());

        $this->assertSame('foo', $mirror->call('staticMethod1'));
        $this->assertSame('bar', $mirror->call('staticMethod1', ['bar']));
    }

    /**
     * @test
     */
    public function get_methods(): void
    {
        $mirror = $this->createMirrorFor(new Object2());
        $methods = $mirror->methods();

        $this->assertSame([
            'instanceMethod4',
            'instanceMethod5',
            'instanceMethod6',
            'instanceMethod3',
            '__construct',
            'instanceMethod1',
            'instanceMethod2',
        ], $methods->names());

        $this->assertSame([
            'instanceMethod4',
            'instanceMethod5',
            'instanceMethod6',
            'instanceMethod3',
            '__construct',
            'instanceMethod1',
            'instanceMethod2',
            'staticMethod1',
            'instanceMethod10',
        ], $methods->recursive()->names());

        $this->assertSame([
            'instanceMethod4',
            'instanceMethod5',
            'instanceMethod6',
            'instanceMethod3',
            '__construct',
            'instanceMethod1',
            'instanceMethod2',
            '__construct',
            'instanceMethod1',
            'instanceMethod2',
            'staticMethod1',
            'instanceMethod3',
            'instanceMethod10',
        ], $methods->recursive(includeDuplicates: true)->names());

        $this->assertSame([
            'instanceMethod6',
            'instanceMethod3',
        ], $methods->private()->names());

        $this->assertSame([
            'instanceMethod5',
            'instanceMethod2',
        ], $methods->protected()->names());

        $this->assertSame([
            'instanceMethod5',
            'instanceMethod6',
            'instanceMethod3',
            'instanceMethod2',
        ], $methods->protected()->private()->names());

        $this->assertSame([
            'staticMethod1',
        ], $methods->recursive()->static()->names());

        $this->assertCount(1, $methods->withAttribute(Attribute1::class));
        $this->assertCount(2, $methods->withAttribute(Attribute1::class, instanceOf: true));
        $this->assertCount(3, $methods->recursive()->withAttribute(Attribute1::class, instanceOf: true));

        $this->assertNull($mirror->method('invalid'));
        $this->assertSame('staticMethod1', $mirror->methodOrFail('staticMethod1')->name());
        $this->assertFalse($mirror->hasMethod('invalid'));
        $this->assertTrue($mirror->hasMethod('staticMethod1'));
    }

    /**
     * @test
     */
    public function get_properties(): void
    {
        $mirror = $this->createMirrorFor(new Object2());
        $properties = $mirror->properties();

        $this->assertSame([
            'instanceProp4',
            'instanceProp5',
            'instanceProp6',
            'instanceProp3',
            'instanceProp1',
            'instanceProp2',
        ], $properties->names());

        $this->assertSame([
            'instanceProp4',
            'instanceProp5',
            'instanceProp6',
            'instanceProp3',
            'instanceProp1',
            'instanceProp2',
            'staticProp1',
            'instanceProp10',
        ], $properties->recursive()->names());

        $this->assertSame([
            'instanceProp4',
            'instanceProp5',
            'instanceProp6',
            'instanceProp3',
            'instanceProp1',
            'instanceProp2',
            'instanceProp1',
            'instanceProp2',
            'staticProp1',
            'instanceProp3',
            'instanceProp10',
        ], $properties->recursive(includeDuplicates: true)->names());

        $this->assertSame([
            'instanceProp4',
            'instanceProp1',
        ], $properties->public()->names());

        $this->assertSame([
            'instanceProp6',
            'instanceProp3',
        ], $properties->private()->names());

        $this->assertSame([
            'instanceProp5',
            'instanceProp2',
        ], $properties->protected()->names());

        $this->assertSame([
            'instanceProp5',
            'instanceProp6',
            'instanceProp3',
            'instanceProp2',
        ], $properties->protected()->private()->names());

        $this->assertSame([
            'staticProp1',
        ], $properties->recursive()->static()->names());

        $this->assertSame([
            'instanceProp5',
            'instanceProp2',
        ], $properties->recursive()->instance()->protected()->names());

        $this->assertCount(1, $properties->withAttribute(Attribute1::class));
        $this->assertCount(2, $properties->withAttribute(Attribute1::class, instanceOf: true));
        $this->assertCount(3, $properties->recursive()->withAttribute(Attribute1::class, instanceOf: true));

        $this->assertNull($mirror->property('invalid'));
        $this->assertSame('staticProp1', $mirror->propertyOrFail('staticProp1')->name());
        $this->assertFalse($mirror->hasProperty('invalid'));
        $this->assertTrue($mirror->hasProperty('staticProp1'));
    }

    /**
     * @test
     */
    public function get_constants(): void
    {
        $mirror = $this->createMirrorFor(new Object2());
        $constants = $mirror->constants();

        $this->assertSame([
            'CONST4',
            'CONST5',
            'CONST6',
            'CONST3',
            'CONST1',
            'CONST2',
        ], $constants->names());

        $this->assertSame([
            'CONST4',
            'CONST5',
            'CONST6',
            'CONST3',
            'CONST1',
            'CONST2',
            'CONST10',
        ], $constants->recursive()->names());

        $this->assertSame([
            'CONST4',
            'CONST5',
            'CONST6',
            'CONST3',
            'CONST1',
            'CONST2',
            'CONST1',
            'CONST2',
            'CONST3',
            'CONST10',
        ], $constants->recursive(includeDuplicates: true)->names());

        $this->assertSame([
            'CONST6',
            'CONST3',
        ], $constants->private()->names());

        $this->assertSame([
            'CONST5',
            'CONST2',
        ], $constants->protected()->names());

        $this->assertSame([
            'CONST5',
            'CONST6',
            'CONST3',
            'CONST2',
        ], $constants->protected()->private()->names());

        $this->assertCount(1, $constants->withAttribute(Attribute1::class));
        $this->assertCount(2, $constants->withAttribute(Attribute1::class, instanceOf: true));
        $this->assertCount(3, $constants->recursive()->withAttribute(Attribute1::class, instanceOf: true));

        $this->assertNull($mirror->constant('invalid'));
        $this->assertSame('CONST10', $mirror->constantOrFail('CONST10')->name());
        $this->assertFalse($mirror->hasConstant('invalid'));
        $this->assertTrue($mirror->hasConstant('CONST10'));
    }

    /**
     * @test
     */
    public function get_parents(): void
    {
        $mirror = $this->createMirrorFor(new Object2());

        $this->assertSame(Object1::class, $mirror->parent()->name());
        $this->assertSame([Object1::class], $mirror->parents()->names());
    }

    /**
     * @test
     */
    public function get_interfaces(): void
    {
        $mirror = $this->createMirrorFor(new Object2());

        $this->assertSame([Interface2::class, Interface1::class, Interface3::class], $mirror->interfaces()->names());
    }

    /**
     * @test
     */
    public function get_traits(): void
    {
        $traits = $this->createMirrorFor(new Object2())->traits();

        $this->assertSame([Trait1::class, Trait3::class], $traits->names());
        $this->assertSame([Trait1::class, Trait3::class, Trait2::class, Trait4::class, Trait5::class], $traits->recursive()->names());
        $this->assertSame(
            [Trait2::class, Trait1::class, Trait4::class, Trait5::class],
            $this->createMirrorFor(new Object1())->traits()->names()
        );
    }

    /**
     * @test
     */
    public function get_constructor(): void
    {
        $this->assertSame(Object1::class, $this->createMirrorFor(new Object2())->constructor()->class()->name());
        $this->assertNull($this->createMirrorFor(new Object3())->constructor());
    }

    /**
     * @test
     * @requires PHP >= 8.1
     */
    public function php81(): void
    {
        $mirror = $this->createMirrorFor(new Php81Object());

        $this->assertSame(['prop1'], $mirror->properties()->readOnly()->names());
        $this->assertSame(['prop2'], $mirror->properties()->modifiable()->names());
        $this->assertSame(['CONST1'], $mirror->constants()->final()->names());
        $this->assertSame(['CONST2'], $mirror->constants()->extendable()->names());
    }

    /**
     * @test
     * @requires PHP >= 8.2
     */
    public function php82(): void
    {
        $mirror = $this->createMirrorFor(new Php82Object());

        $this->assertTrue($mirror->isReadOnly());
        $this->assertFalse($mirror->isModifiable());
    }

    /**
     * @test
     */
    public function attributes(): void
    {
        $attributes = $this->createMirrorFor(new Object1())->attributes();

        $this->assertCount(4, $attributes);
        $this->assertCount(2, $attributes->of(Attribute1::class));
        $this->assertCount(3, $attributes->of(Attribute1::class, instanceOf: true));
    }

    abstract protected function createMirrorFor(object $object): MirrorClass|MirrorObject;
}
