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
    private static $static;
    private $instance;

    /**
     * @test
     */
    public function stringable(): void
    {
        $this->assertSame(__CLASS__.'::$static', (string) MirrorProperty::for($this, 'static'));
        $this->assertSame(__CLASS__.'::$instance', (string) MirrorProperty::for($this, 'instance'));
    }
}
