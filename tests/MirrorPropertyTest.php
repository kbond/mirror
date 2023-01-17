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
use Zenstruck\MirrorProperty;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class MirrorPropertyTest extends TestCase
{
    private static string $static;

    /** @var string */
    private $instance = 'foo';

    /**
     * @test
     */
    public function stringable(): void
    {
        $this->assertSame(__CLASS__.'::$static', (string) MirrorProperty::for($this, 'static'));
        $this->assertSame(__CLASS__.'::$instance', (string) MirrorProperty::for($this, 'instance'));
    }

    /**
     * @test
     */
    public function information(): void
    {
        $instance = MirrorProperty::for($this, 'instance');
        $static = MirrorProperty::for($this, 'static');

        $this->assertSame('/** @var string */', $instance->comment());
        $this->assertNull($static->comment());
        $this->assertSame(__CLASS__, $instance->class()->name());
        $this->assertFalse($instance->isReadOnly());
        $this->assertTrue($instance->isModifiable());
        $this->assertTrue($instance->isInstance());
        $this->assertFalse($instance->isPromoted());
        $this->assertFalse($instance->isPublic());
        $this->assertFalse($instance->isProtected());
        $this->assertTrue($instance->isPrivate());
        $this->assertSame([], $instance->type()->types());
        $this->assertTrue($instance->supports('string'));
        $this->assertTrue($instance->accepts('string'));
        $this->assertFalse($instance->hasType());
        $this->assertTrue($instance->hasDefault());
        $this->assertFalse($static->hasDefault());
        $this->assertSame('foo', $instance->default());
    }

    /**
     * @test
     */
    public function wrap(): void
    {
        $mirror = MirrorProperty::wrap(new \ReflectionProperty(self::class, 'instance'));

        $this->assertSame($mirror, MirrorProperty::wrap($mirror));
    }

    /**
     * @test
     */
    public function reflector(): void
    {
        $this->assertSame('static', MirrorProperty::for($this, 'static')->reflector()->name);
    }
}
