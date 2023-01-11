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
    private const FOO = 1;

    /**
     * @test
     */
    public function stringable(): void
    {
        $this->assertSame(__CLASS__.'::FOO', (string) MirrorClassConstant::for($this, 'FOO'));
    }
}
