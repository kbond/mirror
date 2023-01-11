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
use Zenstruck\MirrorObject;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class MirrorObjectTest extends TestCase
{
    /**
     * @test
     */
    public function stringable(): void
    {
        $this->assertSame(__CLASS__, (string) MirrorObject::for($this));
    }
}
