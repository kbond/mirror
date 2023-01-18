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
use Zenstruck\Mirror\Exception\FailedToInstantiate;
use Zenstruck\Mirror\Instantiator;
use Zenstruck\Tests\Fixture\Interface1;
use Zenstruck\Tests\Fixture\Object1;
use Zenstruck\Tests\Fixture\Object3;
use Zenstruck\Tests\Fixture\Object6;
use Zenstruck\Tests\Fixture\Object8;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class InstantiatorTest extends TestCase
{
    /**
     * @test
     */
    public function instantiate_with_constructor(): void
    {
        $this->assertSame('constructor', Instantiator::withConstructor()(Object6::class)->prop);
    }

    /**
     * @test
     */
    public function instantiate_with_constructor_args(): void
    {
        $arguments = ['prop' => 'value', 'extra' => 'foo'];

        $this->assertSame('value', Instantiator::withConstructor()(Object6::class, $arguments)->prop);
        $this->assertSame(['extra' => 'foo'], $arguments);
    }

    /**
     * @test
     */
    public function instantiate_without_constructor(): void
    {
        $this->assertSame('original', Instantiator::withoutConstructor()(Object6::class)->prop);
    }

    /**
     * @test
     */
    public function instantiate_with_static_method(): void
    {
        $this->assertSame('factory', Instantiator::with('factory')(Object6::class)->prop);
    }

    /**
     * @test
     */
    public function instantiate_with_static_method_args(): void
    {
        $arguments = ['prop' => 'value', 'extra' => 'foo'];

        $this->assertSame('value', Instantiator::with('factory')(Object6::class, $arguments)->prop);
        $this->assertSame(['extra' => 'foo'], $arguments);
    }

    /**
     * @test
     */
    public function instantiate_with_callable(): void
    {
        $this->assertSame('closure', Instantiator::with(fn() => new Object6('closure'))(Object6::class)->prop);
    }

    /**
     * @test
     */
    public function instantiate_with_callable_args(): void
    {
        $arguments = ['prop' => 'value', 'extra' => 'foo'];

        $this->assertSame('value', Instantiator::with(fn($prop) => new Object6($prop))(Object6::class, $arguments)->prop);
        $this->assertSame(['extra' => 'foo'], $arguments);
    }

    /**
     * @test
     */
    public function cannot_instantiate_with_non_static_method(): void
    {
        $this->expectException(FailedToInstantiate::class);

        Instantiator::with('staticFactory')(Object8::class);
    }

    /**
     * @test
     */
    public function cannot_instantiate_with_non_public_method(): void
    {
        $this->expectException(FailedToInstantiate::class);

        Instantiator::with('instanceFactory')(Object8::class);
    }

    /**
     * @test
     */
    public function cannot_instantiate_with_non_callable_string(): void
    {
        $this->expectException(FailedToInstantiate::class);

        Instantiator::with('foo')(Object3::class);
    }

    /**
     * @test
     */
    public function cannot_instantiate_with_constructor_on_non_instantiable_class(): void
    {
        $this->expectException(FailedToInstantiate::class);

        Instantiator::withConstructor()(Object8::class);
    }

    /**
     * @test
     */
    public function cannot_instantiate_without_constructor_on_non_class(): void
    {
        $this->expectException(FailedToInstantiate::class);

        Instantiator::withoutConstructor()(Interface1::class);
    }

    /**
     * @test
     */
    public function can_instantate_class_without_constructor_defined(): void
    {
        $this->assertInstanceOf(Object3::class, Instantiator::withConstructor()(Object3::class));
    }

    /**
     * @test
     */
    public function return_type_must_be_instance_of_object(): void
    {
        $this->expectException(FailedToInstantiate::class);

        Instantiator::with(fn() => 'invalid')(Object1::class);
    }
}
