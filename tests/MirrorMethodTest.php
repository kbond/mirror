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

use PHPUnit\Framework\TestCase;
use Zenstruck\MirrorMethod;
use Zenstruck\Tests\Fixture\Object4;
use Zenstruck\Tests\Fixture\Object6;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class MirrorMethodTest extends TestCase
{
    /**
     * @test
     */
    public function stringable(): void
    {
        $this->assertSame(MirrorMethod::class.'::for()', (string) MirrorMethod::for(MirrorMethod::class, 'for'));
        $this->assertSame(Object4::class.'::staticMethod()', (string) MirrorMethod::for(new Object4(), 'staticMethod'));
        $this->assertSame(Object4::class.'::__invoke()', (string) MirrorMethod::for(new Object4()));
    }

    /**
     * @test
     */
    public function information(): void
    {
        $this->assertTrue(MirrorMethod::for(Object6::class, '__construct')->isConstructor());
        $this->assertFalse(MirrorMethod::for(Object6::class, '__construct')->isDestructor());
        $this->assertTrue(MirrorMethod::for(Object6::class, '__destruct')->isDestructor());
        $this->assertFalse(MirrorMethod::for(Object6::class, '__destruct')->isConstructor());
        $this->assertCount(0, MirrorMethod::for(Object6::class, '__destruct'));
        $this->assertCount(1, MirrorMethod::for(Object6::class, '__construct'));

        $this->assertSame('staticMethod', MirrorMethod::for(new Object4(), 'staticMethod')->reflector()->name);

        $this->assertSame(['void'], MirrorMethod::for(__METHOD__)->returnType()->types());
        $this->assertSame("/**\n     * @test\n     */", MirrorMethod::for(__METHOD__)->comment());
    }

    /**
     * @test
     */
    public function wrap(): void
    {
        $mirror = MirrorMethod::wrap(new \ReflectionMethod(new Object4(), 'staticMethod'));

        $this->assertSame($mirror, MirrorMethod::wrap($mirror));
    }
}
