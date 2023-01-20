<?php

/*
 * This file is part of the zenstruck/mirror package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Mirror\Exception;

use Zenstruck\MirrorProperty;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class PropertyTypeMismatch extends TypeMismatch
{
    /**
     * @param MirrorProperty<object> $property
     */
    public function __construct(private mixed $value, private MirrorProperty $property, ?\Throwable $previous = null)
    {
        parent::__construct(
            \sprintf('Property "%s" does not support type "%s".', $this->property, \get_debug_type($this->value)),
            $previous
        );
    }

    public function value(): mixed
    {
        return $this->value;
    }

    /**
     * @return MirrorProperty<object>
     */
    public function property(): MirrorProperty
    {
        return $this->property;
    }
}
