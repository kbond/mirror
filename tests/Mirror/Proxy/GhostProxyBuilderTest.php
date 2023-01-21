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

        $this->assertTrue(\class_exists(\Proxyaffe0c7fc6a33f37739421b849510cf6d7e81a6d::class));

        $class = MirrorClass::for(\Proxyaffe0c7fc6a33f37739421b849510cf6d7e81a6d::class);
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

        $this->assertFalse(\class_exists(\Proxy0201528f7dd1b4cd1c57f5689c18c7cd5f4af305::class));
        $this->assertFileDoesNotExist(self::DIR.'/Proxy0201528f7dd1b4cd1c57f5689c18c7cd5f4af305.php');

        $class = $builder
            ->in(self::DIR)
            ->implementing(Interface1::class, Interface2::class, Interface3::class)
            ->using(Trait1::class, Trait2::class)
            ->class()
        ;

        $this->assertTrue(\class_exists(\Proxy0201528f7dd1b4cd1c57f5689c18c7cd5f4af305::class));
        $this->assertFileExists(self::DIR.'/Proxy0201528f7dd1b4cd1c57f5689c18c7cd5f4af305.php');

        $class = MirrorClass::for($class);
        $this->assertSame(\Proxy0201528f7dd1b4cd1c57f5689c18c7cd5f4af305::class, $class->name());
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

        $this->assertFalse(\class_exists(\TestProxy1::class));
        $this->assertFileDoesNotExist(self::DIR.'/TestProxy1.php');

        $class = $builder
            ->named('TestProxy1')
            ->in(self::DIR)
            ->implementing(Interface1::class, Interface2::class)
            ->using(Trait1::class, Trait2::class)
            ->class()
        ;

        $this->assertTrue(\class_exists(\TestProxy1::class));
        $this->assertFileExists(self::DIR.'/TestProxy1.php');

        $class = MirrorClass::for($class);
        $this->assertSame(\TestProxy1::class, $class->name());
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

        $this->assertFalse(\class_exists(\TestProxy2::class));
        $this->assertFileDoesNotExist(self::DIR.'/TestProxy2.php');

        $mirror = $builder
            ->named('TestProxy2')
            ->in(self::DIR)
            ->implementing(Interface1::class, Interface2::class)
            ->using(Trait1::class, Trait2::class)
            ->create(function(Object1 $object) {
                $object->instanceProp1 = 'foo';
            })
        ;

        $this->assertTrue(\class_exists(\TestProxy2::class));
        $this->assertFileExists(self::DIR.'/TestProxy2.php');
        $this->assertSame('foo', $mirror->instanceProp1);

        $class = MirrorClass::for($mirror);
        $this->assertSame(\TestProxy2::class, $class->name());
        $this->assertTrue($class->isA(Object1::class));
        $this->assertTrue($class->isA(LazyObjectInterface::class));
        $this->assertTrue($class->isA(Interface1::class));
        $this->assertTrue($class->isA(Interface2::class));
        $this->assertTrue($class->uses(LazyGhostTrait::class));
        $this->assertTrue($class->uses(Trait1::class));
        $this->assertTrue($class->uses(Trait2::class));
    }
}
