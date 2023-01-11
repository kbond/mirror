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
final class TypedArgument extends Argument
{
    /**
     * @param mixed|ValueFactory $value
     */
    public function __construct(private string $type, private mixed $value)
    {
    }

    public function type(): string
    {
        return $this->type;
    }

    protected function valueFor(MirrorType $type): mixed
    {
        if ($type->supports($this->type)) {
            return $this->value;
        }

        throw new UnresolveableArgument('Unable to resolve.');
    }
}
