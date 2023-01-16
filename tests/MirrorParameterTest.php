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
use Zenstruck\MirrorParameter;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class MirrorParameterTest extends TestCase
{
    private static $static;
    private $instance;

    /**
     * @test
     */
    public function stringable($param1 = null, $param2 = null): void
    {
        $this->assertSame(\sprintf('$param2 (#1) <%s::%s()>', __CLASS__, __FUNCTION__), (string) MirrorParameter::for([$this, 'stringable'], 'param2'));
    }

    /**
     * @test
     */
    public function information(): void
    {
        $parameter = MirrorParameter::for([$this, 'stringable'], 'param2');

        $this->assertTrue($parameter->isOptional());
        $this->assertFalse($parameter->isRequired());
        $this->assertFalse($parameter->isVariadic());
        $this->assertFalse($parameter->hasType());
        $this->assertNull($parameter->default());
    }

    /**
     * @test
     */
    public function wrap(): void
    {
        $mirror = MirrorParameter::wrap(new \ReflectionParameter([$this, 'stringable'], 'param1'));

        $this->assertSame($mirror, MirrorParameter::wrap($mirror));
    }

    /**
     * @test
     */
    public function reflector(): void
    {
        $this->assertSame('param1', MirrorParameter::for([$this, 'stringable'], 'param1')->reflector()->name);
    }
}
