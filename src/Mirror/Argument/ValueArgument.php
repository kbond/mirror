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
final class ValueArgument extends Argument
{
    public function __construct(private mixed $value)
    {
    }

    public function type(): string
    {
        return \get_debug_type($this->value);
    }

    protected function valueFor(MirrorType $type): mixed
    {
        if ($type->accepts($this->value)) {
            return $this->value;
        }

        throw new UnresolveableArgument('Unacceptable type.');
    }
}
