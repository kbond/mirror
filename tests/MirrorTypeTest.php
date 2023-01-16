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
use Zenstruck\MirrorType;
use Zenstruck\Tests\Fixture\Countable;
use Zenstruck\Tests\Fixture\CountableIterator;
use Zenstruck\Tests\Fixture\Object1;
use Zenstruck\Tests\Fixture\Object2;
use Zenstruck\Tests\Fixture\Object3;
use Zenstruck\Tests\Fixture\Object5;
use Zenstruck\Tests\Fixture\Object6;
use Zenstruck\Tests\Fixture\Object7;

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

    /**
     * @test
     */
    public function union_type(): void
    {
        $type = MirrorFunction::for(fn(): int|string => null)->returnType();

        $this->assertSame(['string', 'int'], $type->types());
        $this->assertTrue($type->hasType());
        $this->assertFalse($type->isNamed());
        $this->assertTrue($type->isUnion());
        $this->assertFalse($type->isIntersection());
    }

    /**
     * @test
     */
    public function named_type(): void
    {
        $type = MirrorFunction::for(fn(): int => null)->returnType();

        $this->assertSame(['int'], $type->types());
        $this->assertTrue($type->hasType());
        $this->assertTrue($type->isNamed());
        $this->assertFalse($type->isUnion());
        $this->assertFalse($type->isIntersection());
    }

    /**
     * @test
     */
    public function no_type(): void
    {
        $type = MirrorFunction::for(fn() => null)->returnType();

        $this->assertSame(['mixed'], $type->types());
        $this->assertFalse($type->hasType());
        $this->assertFalse($type->isNamed());
        $this->assertFalse($type->isUnion());
        $this->assertFalse($type->isIntersection());
    }

    /**
     * @test
     * @requires PHP >= 8.1
     */
    public function intersection_type(): void
    {
        eval('$callback = fn():\Countable&\Iterator => null;');
        $type = MirrorFunction::for($callback)->returnType();

        $this->assertSame('Countable&Iterator', (string) $type);
        $this->assertSame([\Countable::class, \Iterator::class], $type->types());
        $this->assertTrue($type->hasType());
        $this->assertFalse($type->isNamed());
        $this->assertFalse($type->isUnion());
        $this->assertTrue($type->isIntersection());
    }

    /**
     * @test
     * @requires PHP >= 8.1
     */
    public function supports_accepts_intersection(): void
    {
        eval('$callback = fn(): \Countable&\IteratorAggregate => null;');
        $type = MirrorFunction::for($callback)->returnType();

        $c1 = new Countable();
        $c2 = new CountableIterator();

        $this->assertFalse($type->supports('string'));
        $this->assertFalse($type->supports($c1::class));
        $this->assertTrue($type->supports($c2::class));
        $this->assertFalse($type->accepts('foo'));
        $this->assertFalse($type->accepts($c1));
        $this->assertTrue($type->accepts($c2));
    }

    /**
     * @test
     */
    public function supports(): void
    {
        $fn1 = MirrorFunction::for(fn(?Object1 $object, string $string, int $int, $noType, float $float, bool $bool) => null);
        $fn2 = MirrorFunction::for(fn(Object2 $object, string $string, $noType) => null);

        $this->assertTrue($fn1->parameters()->get(0)->supports(Object1::class));
        $this->assertTrue($fn1->parameters()->get(0)->supports(Object2::class));
        $this->assertTrue($fn1->parameters()->get(0)->supports('null'));
        $this->assertTrue($fn1->parameters()->get(0)->supports('NULL'));
        $this->assertFalse($fn1->parameters()->get(0)->supports('string'));
        $this->assertFalse($fn1->parameters()->get(0)->supports(Object3::class));
        $this->assertFalse($fn1->parameters()->get(0)->supports(Object2::class, MirrorType::CONTRAVARIANCE));
        $this->assertFalse($fn1->parameters()->get(0)->supports(Object2::class, MirrorType::EXACT));
        $this->assertTrue($fn1->parameters()->get(0)->supports(Object1::class, MirrorType::EXACT));
        $this->assertTrue($fn1->parameters()->get(0)->supports('null', MirrorType::EXACT));

        $this->assertTrue($fn1->parameters()->get(1)->supports('string'));
        $this->assertTrue($fn1->parameters()->get(1)->supports('int'));
        $this->assertTrue($fn1->parameters()->get(1)->supports('float'));
        $this->assertTrue($fn1->parameters()->get(1)->supports('bool'));
        $this->assertTrue($fn1->parameters()->get(1)->supports(Object5::class));
        $this->assertFalse($fn1->parameters()->get(1)->supports('int', MirrorType::STRICT));
        $this->assertFalse($fn1->parameters()->get(1)->supports(Object5::class, MirrorType::STRICT));

        $this->assertTrue($fn1->parameters()->get(2)->supports('int'));
        $this->assertTrue($fn1->parameters()->get(2)->supports('integer'));
        $this->assertTrue($fn1->parameters()->get(2)->supports('float'));
        $this->assertFalse($fn1->parameters()->get(2)->supports('float', MirrorType::STRICT));
        $this->assertTrue($fn1->parameters()->get(2)->supports('bool'));
        $this->assertFalse($fn1->parameters()->get(2)->supports('bool', MirrorType::STRICT));
        $this->assertTrue($fn1->parameters()->get(2)->supports('string'));
        $this->assertFalse($fn1->parameters()->get(2)->supports('string', MirrorType::STRICT));

        $this->assertTrue($fn1->parameters()->get(3)->supports(Object1::class));
        $this->assertTrue($fn1->parameters()->get(3)->supports(Object2::class));
        $this->assertTrue($fn1->parameters()->get(3)->supports('string'));
        $this->assertTrue($fn1->parameters()->get(3)->supports('int'));

        $this->assertTrue($fn1->parameters()->get(4)->supports('float'));
        $this->assertTrue($fn1->parameters()->get(4)->supports('double'));
        $this->assertTrue($fn1->parameters()->get(4)->supports('int'));
        $this->assertTrue($fn1->parameters()->get(4)->supports('int', MirrorType::STRICT));
        $this->assertFalse($fn1->parameters()->get(4)->supports('int', MirrorType::VERY_STRICT));
        $this->assertTrue($fn1->parameters()->get(4)->supports('string'));
        $this->assertFalse($fn1->parameters()->get(4)->supports('string', MirrorType::STRICT));
        $this->assertTrue($fn1->parameters()->get(4)->supports('bool'));
        $this->assertFalse($fn1->parameters()->get(4)->supports('bool', MirrorType::STRICT));

        $this->assertTrue($fn1->parameters()->get(5)->supports('bool'));
        $this->assertTrue($fn1->parameters()->get(5)->supports('boolean'));
        $this->assertTrue($fn1->parameters()->get(5)->supports('float'));
        $this->assertFalse($fn1->parameters()->get(5)->supports('float', MirrorType::STRICT));
        $this->assertTrue($fn1->parameters()->get(5)->supports('int'));
        $this->assertFalse($fn1->parameters()->get(5)->supports('int', MirrorType::STRICT));
        $this->assertTrue($fn1->parameters()->get(5)->supports('string'));
        $this->assertFalse($fn1->parameters()->get(5)->supports('string', MirrorType::STRICT));

        $this->assertTrue($fn2->parameters()->get(0)->supports(Object1::class, MirrorType::COVARIANCE | MirrorType::CONTRAVARIANCE));
        $this->assertFalse($fn2->parameters()->get(0)->supports(Object3::class, MirrorType::COVARIANCE | MirrorType::CONTRAVARIANCE));
    }

    /**
     * @test
     */
    public function argument_allows(): void
    {
        $fn1 = MirrorFunction::for(function(Object1 $object, string $string, int $int, $noType, float $float) {});
        $fn2 = MirrorFunction::for(function(Object2 $object, string $string, $noType) {});

        $this->assertTrue($fn1->parameters()->get(0)->accepts(new Object1()));
        $this->assertTrue($fn1->parameters()->get(0)->accepts(new Object2()));
        $this->assertFalse($fn1->parameters()->get(0)->accepts('string'));
        $this->assertFalse($fn1->parameters()->get(0)->accepts(new Object3()));

        $this->assertTrue($fn1->parameters()->get(1)->accepts('string'));
        $this->assertTrue($fn1->parameters()->get(1)->accepts(16));
        $this->assertTrue($fn1->parameters()->get(1)->accepts(16.7));
        $this->assertTrue($fn1->parameters()->get(1)->accepts(true));
        $this->assertFalse($fn1->parameters()->get(1)->accepts(16, true));

        $this->assertTrue($fn1->parameters()->get(2)->accepts(16));
        $this->assertTrue($fn1->parameters()->get(2)->accepts('17'));
        $this->assertTrue($fn1->parameters()->get(2)->accepts(18.0));
        $this->assertFalse($fn1->parameters()->get(2)->accepts('string'), 'non-numeric strings are not allowed');

        $this->assertTrue($fn1->parameters()->get(3)->accepts(new Object1()));
        $this->assertTrue($fn1->parameters()->get(3)->accepts(new Object2()));
        $this->assertTrue($fn1->parameters()->get(3)->accepts('string'));
        $this->assertTrue($fn1->parameters()->get(3)->accepts(16));

        $this->assertTrue($fn1->parameters()->get(4)->accepts(16));
        $this->assertTrue($fn1->parameters()->get(4)->accepts('17'));
        $this->assertTrue($fn1->parameters()->get(4)->accepts('17.3'));
        $this->assertTrue($fn1->parameters()->get(4)->accepts(18.0));
        $this->assertFalse($fn1->parameters()->get(4)->accepts('string'), 'non-numeric strings are not allowed');

        $this->assertFalse($fn2->parameters()->get(0)->accepts(new Object1()));
        $this->assertFalse($fn2->parameters()->get(0)->accepts(new Object3()));
    }

    /**
     * @test
     */
    public function self_type(): void
    {
        $fn = MirrorFunction::for(Object6::closureSelf());

        $this->assertSame(Object6::class, (string) $fn->parameters()->get(0)->type());

        $this->assertTrue($fn->parameters()->get(0)->supports(Object6::class));
        $this->assertTrue($fn->parameters()->get(0)->supports(Object7::class));
        $this->assertFalse($fn->parameters()->get(0)->supports(Object5::class));
        $this->assertFalse($fn->parameters()->get(0)->supports('int'));

        $this->assertTrue($fn->parameters()->get(0)->accepts(new Object6()));
        $this->assertTrue($fn->parameters()->get(0)->accepts(new Object7()));
        $this->assertFalse($fn->parameters()->get(0)->accepts(new Object5()));
        $this->assertFalse($fn->parameters()->get(0)->accepts(6));
    }

    /**
     * @test
     */
    public function stringable_object(): void
    {
        $fn = MirrorFunction::for(function(Object1 $o, string $s) {});

        $this->assertFalse($fn->parameters()->get(0)->supports(Object5::class));
        $this->assertTrue($fn->parameters()->get(1)->supports(Object5::class));
        $this->assertFalse($fn->parameters()->get(1)->supports(Object5::class, MirrorType::STRICT));

        $this->assertFalse($fn->parameters()->get(0)->accepts(new Object5()));
        $this->assertTrue($fn->parameters()->get(1)->accepts(new Object5()));
        $this->assertFalse($fn->parameters()->get(1)->accepts(new Object5(), true));
    }

    /**
     * @test
     */
    public function is_built_in(): void
    {
        $this->assertFalse(MirrorFunction::for(fn(): Object1 => null)->returnType()->isBuiltin());
        $this->assertTrue(MirrorFunction::for(fn(): string => null)->returnType()->isBuiltin());
        $this->assertTrue(MirrorFunction::for(fn() => null)->returnType()->isBuiltin());
        $this->assertFalse(MirrorFunction::for(fn(): string|Object1 => null)->returnType()->isBuiltin());
        $this->assertTrue(MirrorFunction::for(fn(): string|int => null)->returnType()->isBuiltin());
    }
}
