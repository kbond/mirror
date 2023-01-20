<?php

/*
 * This file is part of the zenstruck/mirror package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Mirror\Argument;

use Zenstruck\Mirror\Argument;
use Zenstruck\Mirror\Exception\UnresolveableArgument;
use Zenstruck\MirrorType;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class UnionArgument extends Argument
{
    /** @var Argument[] */
    private array $arguments;

    public function __construct(mixed ...$arguments)
    {
        if (!$arguments) {
            throw new \InvalidArgumentException('At least one argument is required.');
        }

        $this->arguments = \array_map(
            static fn(mixed $v): Argument => $v instanceof Argument ? $v : new ValueArgument($v),
            $arguments
        );
    }

    public function type(): string
    {
        return \implode('|', \array_map(static fn(Argument $a) => $a->type(), $this->arguments));
    }

    protected function valueFor(MirrorType $type): mixed
    {
        foreach ($this->arguments as $argument) {
            try {
                return $argument->valueFor($type);
            } catch (UnresolveableArgument $e) {
                continue;
            }
        }

        throw new UnresolveableArgument('Unsupported union type.', $e ?? null);
    }
}
