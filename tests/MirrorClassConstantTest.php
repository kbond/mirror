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
use Zenstruck\MirrorClassConstant;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class MirrorClassConstantTest extends TestCase
{
    /** @var int */
    private const FOO = 1;
    private const BAR = 2;

    /**
     * @test
     */
    public function stringable(): void
    {
        $this->assertSame(__CLASS__.'::FOO', (string) MirrorClassConstant::for($this, 'FOO'));
    }

    /**
     * @test
     */
    public function information(): void
    {
        $const = MirrorClassConstant::for($this, 'FOO');

        $this->assertFalse($const->isFinal());
        $this->assertTrue($const->isExtendable());
        $this->assertTrue($const->isPrivate());
        $this->assertFalse($const->isProtected());
        $this->assertFalse($const->isPublic());
        $this->assertSame(1, $const->value());
        $this->assertSame(__CLASS__, $const->class()->name());
        $this->assertSame('FOO', $const->reflector()->name);
        $this->assertSame('/** @var int */', $const->comment());
        $this->assertNull(MirrorClassConstant::for($this, 'BAR')->comment());
    }
}
