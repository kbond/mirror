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
     * @param int-mask<MirrorType::EXACT,MirrorType::COVARIANCE,MirrorType::CONTRAVARIANCE,MirrorType::STRICT,MirrorType::VERY_STRICT> $mode
     */
    public function __construct(private string $type, private mixed $value, private int $mode = MirrorType::DEFAULT)
    {
    }

    public function type(): string
    {
        return $this->type;
    }

    protected function valueFor(MirrorType $type): mixed
    {
        if (!$type->hasType()) {
            throw new UnresolveableArgument('Parameter has no type.');
        }

        if ($type->supports($this->type, $this->mode)) {
            return $this->value;
        }

        throw new UnresolveableArgument('Unsupported type.');
    }
}
