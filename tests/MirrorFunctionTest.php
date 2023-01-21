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
use Zenstruck\Mirror\Exception\ParameterTypeMismatch;
use Zenstruck\MirrorFunction;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class MirrorFunctionTest extends TestCase
{
    /**
     * @test
     */
    public function stringable(): void
    {
        $this->assertMatchesRegularExpression(\sprintf('#^\(closure\) %s:\d+$#', __FILE__), (string) MirrorFunction::for(fn() => null));
        $this->assertSame('strlen()', (string) MirrorFunction::for('strlen'));
        $this->assertSame(__NAMESPACE__.'\test_function()', (string) MirrorFunction::for(__NAMESPACE__.'\test_function'));
        $this->assertMatchesRegularExpression(\sprintf('#^\(closure\) %s:\d+$#', (new \ReflectionClass(MirrorFunction::class))->getFileName()), (string) MirrorFunction::for('Zenstruck\MirrorFunction::for'));
        $this->assertMatchesRegularExpression(\sprintf('#^\(closure\) %s:\d+$#', (new \ReflectionClass(MirrorFunction::class))->getFileName()), (string) MirrorFunction::for([MirrorFunction::class, 'for']));
    }

    /**
     * @test
     */
    public function wrap(): void
    {
        $mirror = MirrorFunction::wrap(new \ReflectionFunction(fn() => null));

        $this->assertSame($mirror, MirrorFunction::wrap($mirror));
    }

    /**
     * @test
     */
    public function information(): void
    {
        $this->assertSame('strlen', MirrorFunction::for('strlen')->reflector()->name);
        $this->assertNull(MirrorFunction::for('strlen')->this());
        $this->assertNull(MirrorFunction::for(static fn() => null)->this());
        $this->assertSame($this, MirrorFunction::for(fn() => null)->this());
    }

    /**
     * @test
     */
    public function invoke_type_error(): void
    {
        $this->expectException(ParameterTypeMismatch::class);

        MirrorFunction::for('strlen')([['array']]);
    }
}

function test_function()
{
}
