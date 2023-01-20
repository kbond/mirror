<?php

/*
 * This file is part of the zenstruck/mirror package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Mirror;

use Zenstruck\Mirror\Argument\TypedArgument;
use Zenstruck\Mirror\Argument\UnionArgument;
use Zenstruck\Mirror\Argument\UntypedArgument;
use Zenstruck\Mirror\Argument\ValueArgument;
use Zenstruck\Mirror\Argument\ValueFactory;
use Zenstruck\Mirror\Exception\UnresolveableArgument;
use Zenstruck\MirrorParameter;
use Zenstruck\MirrorType;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @immutable
 */
abstract class Argument
{
    private bool $optional = false;

    final public static function union(mixed ...$parameters): self
    {
        return new UnionArgument(...$parameters);
    }

    /**
     * @param int-mask<MirrorType::EXACT,MirrorType::COVARIANCE,MirrorType::CONTRAVARIANCE,MirrorType::STRICT,MirrorType::VERY_STRICT> $mode
     */
    final public static function typed(string $type, mixed $value, int $mode = MirrorType::DEFAULT): self
    {
        return new TypedArgument($type, $value, $mode);
    }

    /**
     * @param callable():mixed|callable(string[]):mixed|callable(MirrorType):mixed|callable(string):mixed                              $factory
     * @param int-mask<MirrorType::EXACT,MirrorType::COVARIANCE,MirrorType::CONTRAVARIANCE,MirrorType::STRICT,MirrorType::VERY_STRICT> $mode
     */
    final public static function typedFactory(string $type, callable $factory, int $mode = MirrorType::DEFAULT): self
    {
        return self::typed($type, new ValueFactory($factory), $mode);
    }

    final public static function untyped(mixed $value): self
    {
        return new UntypedArgument($value);
    }

    /**
     * @param callable():mixed|callable(string[]):mixed|callable(MirrorType):mixed|callable(string):mixed $factory
     */
    final public static function untypedFactory(callable $factory): self
    {
        return self::untyped(new ValueFactory($factory));
    }

    final public static function value(mixed $value): self
    {
        return new ValueArgument($value);
    }

    final public function optional(): self
    {
        $clone = clone $this;
        $clone->optional = true;

        return $clone;
    }

    final public function isOptional(): bool
    {
        return $this->optional;
    }

    /**
     * @internal
     *
     * @throws UnresolveableArgument
     */
    final public function resolve(MirrorParameter $parameter): mixed
    {
        try {
            $value = $this->valueFor($type = $parameter->type());
        } catch (UnresolveableArgument $e) {
            if ($parameter->isOptional() && $parameter->hasDefault()) {
                return $parameter->default();
            }

            throw new UnresolveableArgument(\sprintf('Parameter does not support "%s"', $this->type()), $e);
        }

        if ($value instanceof ValueFactory) {
            $value = $value($type);
        }

        if (!$type->accepts($value)) {
            throw new UnresolveableArgument(\sprintf('Expected "%s", got "%s"', $type, \get_debug_type($value)));
        }

        return $value;
    }

    abstract public function type(): string;

    /**
     * @throws UnresolveableArgument
     */
    abstract protected function valueFor(MirrorType $type): mixed;
}
