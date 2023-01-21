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
use Zenstruck\Mirror\Argument;
use Zenstruck\Mirror\Exception\UnresolveableArgument;
use Zenstruck\MirrorFunction;
use Zenstruck\MirrorType;
use Zenstruck\Tests\Fixture\Interface1;
use Zenstruck\Tests\Fixture\Object1;
use Zenstruck\Tests\Fixture\Object2;
use Zenstruck\Tests\Fixture\Object3;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class InvokeWithArgumentTest extends TestCase
{
    /**
     * @test
     */
    public function invoke_all_with_no_arguments(): void
    {
        $actual = MirrorFunction::for(fn() => 'ret')
            ->invoke(Argument::value('foo'))
        ;

        $this->assertSame('ret', $actual);
    }

    /**
     * @test
     */
    public function invoke_all_with_string_callable(): void
    {
        $actual = MirrorFunction::for('strtoupper')
            ->invoke(Argument::value('foobar'))
        ;

        $this->assertSame('FOOBAR', $actual);
    }

    /**
     * @test
     */
    public function invoke_all_untyped_argument(): void
    {
        $actual = MirrorFunction::for(fn($string) => \mb_strtoupper($string))
            ->invoke(Argument::untyped('foobar'))
        ;

        $this->assertSame('FOOBAR', $actual);
    }

    /**
     * @test
     */
    public function invoke_all_class_arguments(): void
    {
        $object = new Object2();
        $function = static function(Object1 $object1, Object2 $object2, $object3) {
            return [
                'object1' => $object1,
                'object2' => $object2,
                'object3' => $object3,
            ];
        };

        $actual = MirrorFunction::for($function)
            ->invoke(Argument::value($object))
        ;

        $this->assertSame(
            [
                'object1' => $object,
                'object2' => $object,
                'object3' => $object,
            ],
            $actual
        );
    }

    /**
     * @test
     */
    public function invoke_all_class_arguments_value_factories(): void
    {
        $function = static function(Object1 $object1, Object2 $object2, $object3, Interface1 $object4) {
            return [
                'object1' => $object1,
                'object2' => $object2,
                'object3' => $object3,
                'object4' => $object4,
            ];
        };
        $factoryArgs = [];
        $factory = static function($arg) use (&$factoryArgs) {
            $factoryArgs[] = $arg;

            if ($arg && \class_exists($arg)) {
                return new $arg();
            }

            return new Object1();
        };

        $ret = MirrorFunction::for($function)
            ->invoke(Argument::union(
                Argument::untypedFactory($factory),
                Argument::typedFactory(Object1::class, $factory, MirrorType::DEFAULT | MirrorType::CONTRAVARIANCE),
            ))
        ;

        $this->assertSame(['object1', 'object2', 'object3', 'object4'], \array_keys($ret));
        $this->assertInstanceOf(Object1::class, $ret['object1']);
        $this->assertInstanceOf(Object2::class, $ret['object2']);
        $this->assertInstanceOf(Object1::class, $ret['object3']);
        $this->assertInstanceOf(Object1::class, $ret['object4']);
        $this->assertSame(
            [Object1::class, Object2::class, null, Interface1::class],
            $factoryArgs
        );
    }

    /**
     * @test
     */
    public function invoke_all_unresolvable_argument(): void
    {
        $callback = MirrorFunction::for(static function(Object1 $object1, Object2 $object2, Object3 $object3) {});

        $this->expectException(UnresolveableArgument::class);
        $this->expectExceptionMessage('Unable to resolve argument for "Zenstruck\Tests\Fixture\Object2 $object2 (#1)');

        $callback->invoke(Argument::union(
            Argument::untyped(new Object1()),
            Argument::typed(Object1::class, new Object1())
        ));
    }

    /**
     * @test
     */
    public function invoke_with_too_few_arguments(): void
    {
        $this->expectException(\ArgumentCountError::class);
        $this->expectExceptionMessage('Too few arguments passed to "(closure) ');

        MirrorFunction::for(fn(string $string, float $float, ?int $int = null) => 'ret')->invoke(['2']);
    }

    /**
     * @test
     */
    public function invoke_with_standard_arguments(): void
    {
        $callback = MirrorFunction::for(
            fn(string $string, float $float, ?int $int = null) => [$string, $float, $int]
        );

        $this->assertSame(['value', 3.4, null], $callback->invoke(['value', 3.4]));
    }

    /**
     * @test
     */
    public function invoke_with_resolvable_arguments(): void
    {
        $object = new Object2();
        $function = static function(Object1 $object1, Object2 $object2, $object3, $extra) {
            return [
                'object1' => $object1,
                'object2' => $object2,
                'object3' => $object3,
                'extra' => $extra,
            ];
        };

        $actual = MirrorFunction::for($function)
            ->invoke([
                Argument::typed(Object2::class, $object),
                Argument::typed(Object2::class, $object),
                Argument::untyped($object),
                'value',
            ])
        ;

        $this->assertSame(
            [
                'object1' => $object,
                'object2' => $object,
                'object3' => $object,
                'extra' => 'value',
            ],
            $actual
        );
    }

    /**
     * @test
     */
    public function invoke_with_unresolvable_argument(): void
    {
        $object = new Object2();
        $function = static function(Object1 $object1, $object2, $object3, $extra) {};

        $this->expectException(UnresolveableArgument::class);
        $this->expectExceptionMessage('Unable to resolve argument for "(none) $object2 (#1)');

        MirrorFunction::for($function)
            ->invoke([
                Argument::typed(Object1::class, $object),
                Argument::typed(Object2::class, $object),
                Argument::untyped($object),
                'value',
            ])
        ;
    }

    /**
     * @test
     */
    public function invoke_with_not_enough_required_arguments(): void
    {
        $object = new Object2();
        $function = static function(Object1 $object1) {};

        $this->expectException(\ArgumentCountError::class);
        $this->expectExceptionMessage('Missing argument #1 for "(closure)');

        MirrorFunction::for($function)
            ->invoke([
                Argument::typed(Object1::class, $object),
                Argument::typed(Object2::class, $object),
                Argument::untyped($object),
                'value',
            ])
        ;
    }

    /**
     * @test
     */
    public function can_mark_invoke_parameter_arguments_as_optional(): void
    {
        $actual = MirrorFunction::for(static fn() => 'ret')
            ->invoke([Argument::typed('string', 'foobar')->optional()])
        ;

        $this->assertSame('ret', $actual);

        $actual = MirrorFunction::for(static fn(string $v) => $v)
            ->invoke([Argument::typed('string', 'foobar')->optional()])
        ;

        $this->assertSame('foobar', $actual);
    }

    /**
     * @test
     */
    public function invoke_can_support_union_typehints(): void
    {
        $callback = fn(Object1|string $arg) => 'ret';

        $this->assertSame('ret', MirrorFunction::for($callback)->invoke(Argument::typed(Object1::class, new Object1())));
        $this->assertSame('ret', MirrorFunction::for($callback)->invoke(Argument::typed('string', 'value')));
    }

    /**
     * @test
     */
    public function value_factory_injects_argument_if_type_hinted(): void
    {
        $factory = function(MirrorType $type) {
            if ($type->supports('string', MirrorType::STRICT)) {
                return 'string';
            }

            if ($type->supports('int')) {
                return 17;
            }

            return 'invalid';
        };

        $ret = MirrorFunction::for(fn(string $a, int $b, $c) => [$a, $b, $c])->invoke(
            Argument::union(
                Argument::typedFactory('string', $factory),
                Argument::typedFactory('int', $factory),
                Argument::untypedFactory($factory)
            )
        );

        $this->assertSame(['string', 17, 'string'], $ret);
    }

    /**
     * @test
     */
    public function can_use_value_factory_with_no_argument(): void
    {
        $ret = MirrorFunction::for(fn($value) => $value)
            ->invoke(Argument::untypedFactory(fn() => 'value'))
        ;

        $this->assertSame('value', $ret);
    }

    /**
     * @test
     */
    public function value_factory_can_be_used_with_union_arguments_if_no_value_factory_argument(): void
    {
        $ret = MirrorFunction::for(fn(Object1|string $a) => $a)
            ->invoke(Argument::typedFactory('string', fn() => 'value'))
        ;

        $this->assertSame('value', $ret);
    }

    /**
     * @test
     */
    public function value_factory_can_be_used_with_union_arguments_as_array(): void
    {
        $array = [];
        $factory = function(array $types) use (&$array) {
            $array = $types;

            return 'value';
        };

        $ret = MirrorFunction::for(fn(Object1|string $a) => $a)
            ->invoke(Argument::typedFactory('string', $factory))
        ;

        $this->assertSame('value', $ret);
        $this->assertSame([Object1::class, 'string'], $array);
    }

    /**
     * @test
     */
    public function invoke_all_union_parameter_with_defaults(): void
    {
        $fn = MirrorFunction::for(fn(string $a, ?\DateTimeInterface $b = null, $c = null) => [$a, $b, $c]);

        $ret = $fn->invoke(Argument::union(
            Argument::typed('string', 'a')
        ));

        $this->assertSame(['a', null, null], $ret);

        $ret = $fn->invoke(Argument::union(
            Argument::typed('string', 'a'),
            Argument::typed(\DateTime::class, $b = new \DateTime())
        ));

        $this->assertSame(['a', $b, null], $ret);

        $ret = $fn->invoke(Argument::union(
            Argument::typed('string', 'a'),
            Argument::untyped('c')
        ));

        $this->assertSame(['a', null, 'c'], $ret);

        $ret = $fn->invoke(Argument::union(
            Argument::untyped('c'),
            Argument::typed('string', 'a'),
            Argument::typed(\DateTime::class, $b = new \DateTime())
        ));

        $this->assertSame(['a', $b, 'c'], $ret);

        $ret = $fn->invoke(Argument::union(
            Argument::typed('string', 'a'),
            Argument::typedFactory(\DateTime::class, fn() => $b)
        ));

        $this->assertSame(['a', $b, null], $ret);
    }
}
