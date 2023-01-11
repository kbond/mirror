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
        $this->assertSame('(function) strlen()', (string) MirrorFunction::for('strlen'));
        $this->assertSame('(function) '.__NAMESPACE__.'\test_function()', (string) MirrorFunction::for(__NAMESPACE__.'\test_function'));
        $this->assertMatchesRegularExpression(\sprintf('#^\(closure\) %s:\d+$#', (new \ReflectionClass(MirrorFunction::class))->getFileName()), (string) MirrorFunction::for('Zenstruck\MirrorFunction::for'));
        $this->assertMatchesRegularExpression(\sprintf('#^\(closure\) %s:\d+$#', (new \ReflectionClass(MirrorFunction::class))->getFileName()), (string) MirrorFunction::for([MirrorFunction::class, 'for']));
    }
}

function test_function()
{
}
