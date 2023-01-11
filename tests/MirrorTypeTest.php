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
final class MirrorTypeTest extends TestCase
{
    /**
     * @test
     */
    public function stringable(): void
    {
        $this->assertSame('mixed', (string) MirrorFunction::for(fn() => null)->returnType());
        $this->assertSame('mixed', (string) MirrorFunction::for(fn(): mixed => null)->returnType());
        $this->assertSame('int', (string) MirrorFunction::for(fn(): int => null)->returnType());
        $this->assertSame('int|null', (string) MirrorFunction::for(fn(): ?int => null)->returnType());
        $this->assertSame('int|null', (string) MirrorFunction::for(fn(): int|null => null)->returnType());
        $this->assertSame('string|int', (string) MirrorFunction::for(fn(): int|string => null)->returnType());
        $this->assertSame('string|int|null', (string) MirrorFunction::for(fn(): int|string|null => null)->returnType());
        $this->assertSame(__CLASS__.'|string|int', (string) MirrorFunction::for(fn(): int|string|self => null)->returnType());
        $this->assertSame(__CLASS__.'|static', (string) MirrorFunction::for(fn(): self|static => null)->returnType());
        $this->assertSame('void', (string) MirrorFunction::for([$this, __FUNCTION__])->returnType());
        $this->assertSame(__CLASS__.'|string|int', (string) MirrorFunction::for(fn(int|string|self $foo) => null)->parameters()->first()->type());
    }
}
