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

use Zenstruck\Mirror\Argument\ValueFactory;
use Zenstruck\Mirror\Exception\UnresolveableArgument;
use Zenstruck\MirrorParameter;
use Zenstruck\MirrorType;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @immutable
 */
final class Argument
{
    /** @var mixed[] */
    private array $values;
    private bool $optional = false;

    private function __construct(mixed ...$values)
    {
        $v = [];

        foreach ($values as $value) {
            if (!$value instanceof self) {
                $v[] = [$value];

                continue;
            }

            $v[] = $value->values;
        }

        $this->values = \array_merge(...$v);
    }

    public static function new(mixed ...$values): self
    {
        return new self(...$values);
    }

    public static function factory(string $type, callable $factory): self
    {
        return new self(new ValueFactory($type, $factory instanceof \Closure ? $factory : \Closure::fromCallable($factory)));
    }

    public function optional(): self
    {
        $clone = clone $this;
        $clone->optional = true;

        return $clone;
    }

    public function isOptional(): bool
    {
        return $this->optional;
    }

    /**
     * @throws UnresolveableArgument
     */
    public function resolve(MirrorParameter $parameter): mixed
    {
        try {
            $value = $this->valueFor($type = $parameter->type());
        } catch (UnresolveableArgument $e) {
            if ($parameter->isOptional()) {
                return $parameter->defaultValue();
            }

            throw $e;
        }

        if (!$value instanceof ValueFactory) {
            return $value;
        }

        $value = $value($type);

        if (!$type->accepts($value)) {
            throw new UnresolveableArgument(\sprintf('Unable to resolve argument for "%s". Expected "%s", got "%s".', $parameter, $type, \get_debug_type($value)));
        }

        return $value;
    }

    private function valueFor(MirrorType $type): mixed
    {
        foreach ($this->values as $value) {
            if (self::supports($type, $value)) {
                return $value;
            }
        }

        throw new UnresolveableArgument(); // todo
    }

    /**
     * @throws UnresolveableArgument
     */
    private static function supports(MirrorType $type, mixed $value): bool
    {
        if ($value instanceof ValueFactory) {
            return $type->supports($value->type());
        }

        return $type->accepts($value);
    }
}
