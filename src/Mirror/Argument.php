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

    protected function __construct()
    {
    }

    final public static function union(self ...$arguments): UnionArgument
    {
        return new UnionArgument(...$arguments);
    }

    /**
     * @param mixed|ValueFactory $value
     */
    final public static function typed(string $type, mixed $value): TypedArgument
    {
        return new TypedArgument($type, $value);
    }

    /**
     * @param mixed|ValueFactory $value
     */
    final public static function untyped(mixed $value): TypedArgument
    {
        return self::typed('mixed', $value);
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
     * @throws UnresolveableArgument
     */
    final public function resolve(MirrorParameter $parameter): mixed
    {
        try {
            $value = $this->valueFor($type = $parameter->type());
        } catch (UnresolveableArgument $e) {
            if ($parameter->isOptional()) {
                return $parameter->defaultValue();
            }

            throw $e;
        }

        if ($value instanceof ValueFactory) {
            $value = $value($type);
        }

        if (!$type->accepts($value)) {
            throw new UnresolveableArgument(\sprintf('Unable to resolve argument for "%s". Expected "%s", got "%s".', $parameter, $type, \get_debug_type($value)));
        }

        return $value;
    }

    abstract public function type(): string;

    /**
     * @return mixed|ValueFactory
     *
     * @throws UnresolveableArgument
     */
    abstract protected function valueFor(MirrorType $type): mixed;
}
