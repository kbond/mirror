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
        $this->assertSame(DummyMirrorCallableObject::class.'::foo()', (string) MirrorMethod::for(new DummyMirrorCallableObject(), 'foo'));
        $this->assertSame(DummyMirrorCallableObject::class.'::__invoke()', (string) MirrorMethod::for(new DummyMirrorCallableObject()));
    }
}

class DummyMirrorCallableObject
{
    public function __invoke()
    {
    }

    public function foo()
    {
    }
}
