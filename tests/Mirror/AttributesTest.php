<?php

/*
 * This file is part of the zenstruck/mirror package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Tests\Mirror;

use PHPUnit\Framework\TestCase;
use Zenstruck\Mirror\Attributes;
use Zenstruck\MirrorClass;
use Zenstruck\MirrorObject;
use Zenstruck\Tests\Fixture\Attribute1;
use Zenstruck\Tests\Fixture\Attribute2;
use Zenstruck\Tests\Fixture\Attribute3;
use Zenstruck\Tests\Fixture\Attribute4;
use Zenstruck\Tests\Fixture\Object1;
use Zenstruck\Tests\Fixture\Object6;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class AttributesTest extends TestCase
{
    /**
     * @test
     * @dataProvider forProvider
     */
    public function for_class(object|string|callable $reflector, string $expectedMirrorName, ?string $expectedMirrorString = null): void
    {
        $attributes = Attributes::for($reflector);

        $this->assertSame([Attribute2::class, Attribute1::class, Attribute1::class, Attribute3::class], $attributes->names());
        $this->assertTrue($attributes->has(Attribute1::class));
        $this->assertFalse($attributes->has(Attribute4::class));
        $this->assertSame([Attribute1::class, Attribute1::class], $attributes->of(Attribute1::class)->names());
        $this->assertSame([Attribute2::class, Attribute1::class, Attribute1::class], $attributes->of(Attribute1::class, instanceOf: true)->names());
        $this->assertSame([Attribute3::class], $attributes->of(Attribute3::class)->names());
        $this->assertEmpty($attributes->of(Attribute4::class));
        $this->assertSame($expectedMirrorName, $attributes->first()->target()->name());
        $this->assertSame('second', $attributes->firstOf(Attribute1::class)->arguments()[0]);
        $this->assertSame('first', $attributes->firstOf(Attribute1::class, instanceOf: true)->arguments()[0]);
        $this->assertSame('second', $attributes->firstInstantiatedOf(Attribute1::class)->value);
        $this->assertSame('first', $attributes->firstInstantiatedOf(Attribute1::class, instanceOf: true)->value);
        $this->assertSame([
                'first',
                'second',
                'third',
                'fourth',
            ],
            \array_map(fn($o) => $o->value, \iterator_to_array($attributes->instantiate()))
        );
        $this->assertSame([
            'second',
            'third',
        ],
            \array_map(fn($o) => $o->value, \iterator_to_array($attributes->of(Attribute1::class)->instantiate()))
        );
        $this->assertSame([
            'first',
            'second',
            'third',
        ],
            \array_map(fn($o) => $o->value, \iterator_to_array($attributes->of(Attribute1::class, instanceOf: true)->instantiate()))
        );

        if ($expectedMirrorString) {
            $this->assertSame($expectedMirrorString, (string) $attributes->first()->target());
        }
    }

    public static function forProvider(): iterable
    {
        yield [Object1::class, Object1::class, Object1::class];
        yield [new Object1(), Object1::class, Object1::class];
        yield [MirrorClass::for(Object1::class), Object1::class, Object1::class];
        yield [MirrorObject::for(new Object1()), Object1::class, Object1::class];
        yield [new \ReflectionClass(Object1::class), Object1::class, Object1::class];
        yield [new \ReflectionObject(new Object1()), Object1::class, Object1::class];
        yield [new \ReflectionClassConstant(Object1::class, 'CONST1'), 'CONST1', Object1::class.'::CONST1'];
        yield [new \ReflectionMethod(Object1::class, '__construct'), '__construct', Object1::class.'::__construct()'];
        yield [new \ReflectionProperty(Object1::class, 'instanceProp1'), 'instanceProp1', Object1::class.'::$instanceProp1'];
        yield [new \ReflectionParameter([Object1::class, '__construct'], 'param'), 'param', '$param (#0) <Zenstruck\Tests\Fixture\Object1::__construct()>'];
        yield [Object6::class.'::factory', 'factory', Object6::class.'::factory()'];
        yield [[Object6::class, 'factory'], 'factory', Object6::class.'::factory()'];
        yield [[new Object6(), 'method'], 'method', Object6::class.'::method()'];
        yield [new Object6(), '__invoke', Object6::class.'::__invoke()'];
        yield [
            #[Attribute2('first')]
            #[Attribute1('second')]
            #[Attribute1('third')]
            #[Attribute3('fourth')]
            fn() => null,
            'Zenstruck\Tests\Mirror\{closure}',
        ];
    }
}
