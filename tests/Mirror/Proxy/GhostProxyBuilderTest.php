<?php

/*
 * This file is part of the zenstruck/mirror package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Tests\Mirror\Proxy;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\VarExporter\LazyGhostTrait;
use Symfony\Component\VarExporter\LazyObjectInterface;
use Symfony\Component\VarExporter\ProxyHelper;
use Zenstruck\Mirror\Proxy\GhostProxyBuilder;
use Zenstruck\MirrorClass;
use Zenstruck\Tests\Fixture\Interface1;
use Zenstruck\Tests\Fixture\Interface2;
use Zenstruck\Tests\Fixture\Interface3;
use Zenstruck\Tests\Fixture\Object1;
use Zenstruck\Tests\Fixture\Trait1;
use Zenstruck\Tests\Fixture\Trait2;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class GhostProxyBuilderTest extends TestCase
{
    private const DIR = __DIR__.'/../../../var';

    protected function setUp(): void
    {
        if (!\class_exists(ProxyHelper::class)) {
            $this->markTestSkipped('symfony/var-exporter not available.');
        }

        (new Filesystem())->remove(self::DIR);
    }

    /**
     * @test
     */
    public function generates_valid_class_contents(): void
    {
        $builder = new GhostProxyBuilder(Object1::class);

        $content = $builder
            ->implementing(Interface1::class, Interface2::class)
            ->using(Trait1::class, Trait2::class)
            ->contents()
        ;

        eval($content);

        $this->assertTrue(\class_exists(\Proxy4f529fcd38225daf422b1fd264655c717b1a0abc::class));

        $class = MirrorClass::for(\Proxy4f529fcd38225daf422b1fd264655c717b1a0abc::class);
        $this->assertTrue($class->isA(Object1::class));
        $this->assertTrue($class->isA(LazyObjectInterface::class));
        $this->assertTrue($class->isA(Interface1::class));
        $this->assertTrue($class->isA(Interface2::class));
        $this->assertTrue($class->uses(LazyGhostTrait::class));
        $this->assertTrue($class->uses(Trait1::class));
        $this->assertTrue($class->uses(Trait2::class));
    }

    /**
     * @test
     */
    public function generates_valid_class(): void
    {
        $builder = new GhostProxyBuilder(Object1::class);

        $this->assertFalse(\class_exists(\Proxy1234e6c38d17024dc06ee11e90bb590a9037b68b::class));
        $this->assertFileDoesNotExist(self::DIR.'/Proxy1234e6c38d17024dc06ee11e90bb590a9037b68b.php');

        $class = $builder
            ->in(self::DIR)
            ->implementing(Interface1::class, Interface2::class, Interface3::class)
            ->using(Trait1::class, Trait2::class)
            ->class()
        ;

        $this->assertTrue(\class_exists(\Proxy1234e6c38d17024dc06ee11e90bb590a9037b68b::class));
        $this->assertFileExists(self::DIR.'/Proxy1234e6c38d17024dc06ee11e90bb590a9037b68b.php');

        $class = MirrorClass::for($class);
        $this->assertSame(\Proxy1234e6c38d17024dc06ee11e90bb590a9037b68b::class, $class->name());
        $this->assertTrue($class->isA(Object1::class));
        $this->assertTrue($class->isA(LazyObjectInterface::class));
        $this->assertTrue($class->isA(Interface1::class));
        $this->assertTrue($class->isA(Interface2::class));
        $this->assertTrue($class->uses(LazyGhostTrait::class));
        $this->assertTrue($class->uses(Trait1::class));
        $this->assertTrue($class->uses(Trait2::class));
    }

    /**
     * @test
     */
    public function generates_valid_class_custom_name(): void
    {
        $builder = new GhostProxyBuilder(Object1::class);

        $this->assertFalse(\class_exists(\TestProxy3::class));
        $this->assertFileDoesNotExist(self::DIR.'/TestProxy3.php');

        $class = $builder
            ->named('TestProxy3')
            ->in(self::DIR)
            ->implementing(Interface1::class, Interface2::class)
            ->using(Trait1::class, Trait2::class)
            ->class()
        ;

        $this->assertTrue(\class_exists(\TestProxy3::class));
        $this->assertFileExists(self::DIR.'/TestProxy3.php');

        $class = MirrorClass::for($class);
        $this->assertSame(\TestProxy3::class, $class->name());
        $this->assertTrue($class->isA(Object1::class));
        $this->assertTrue($class->isA(LazyObjectInterface::class));
        $this->assertTrue($class->isA(Interface1::class));
        $this->assertTrue($class->isA(Interface2::class));
        $this->assertTrue($class->uses(LazyGhostTrait::class));
        $this->assertTrue($class->uses(Trait1::class));
        $this->assertTrue($class->uses(Trait2::class));
    }

    /**
     * @test
     */
    public function generates_valid_object(): void
    {
        $builder = new GhostProxyBuilder(Object1::class);

        $this->assertFalse(\class_exists(\TestProxy4::class));
        $this->assertFileDoesNotExist(self::DIR.'/TestProxy4.php');

        $mirror = $builder
            ->named('TestProxy4')
            ->in(self::DIR)
            ->implementing(Interface1::class, Interface2::class)
            ->using(Trait1::class, Trait2::class)
            ->create(function(Object1 $object) {
                $object->instanceProp1 = 'foo';
            })
        ;

        $this->assertTrue(\class_exists(\TestProxy4::class));
        $this->assertFileExists(self::DIR.'/TestProxy4.php');
        $this->assertSame('foo', $mirror->instanceProp1);

        $class = MirrorClass::for($mirror);
        $this->assertSame(\TestProxy4::class, $class->name());
        $this->assertTrue($class->isA(Object1::class));
        $this->assertTrue($class->isA(LazyObjectInterface::class));
        $this->assertTrue($class->isA(Interface1::class));
        $this->assertTrue($class->isA(Interface2::class));
        $this->assertTrue($class->uses(LazyGhostTrait::class));
        $this->assertTrue($class->uses(Trait1::class));
        $this->assertTrue($class->uses(Trait2::class));
    }
}
