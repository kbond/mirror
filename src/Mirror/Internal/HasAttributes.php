<?php

/*
 * This file is part of the zenstruck/mirror package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Mirror\Internal;

use Zenstruck\Mirror\Attributes;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
trait HasAttributes
{
    /**
     * @return Attributes<\ReflectionAttribute<object>>
     */
    public function attributes(): Attributes
    {
        return new Attributes($this->reflector); // @phpstan-ignore-line
    }
}
