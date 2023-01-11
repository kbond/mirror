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

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
trait VisibilityMethods
{
    public function isPublic(): bool
    {
        return $this->reflector->isPublic();
    }

    public function isProtected(): bool
    {
        return $this->reflector->isProtected();
    }

    public function isPrivate(): bool
    {
        return $this->reflector->isPrivate();
    }
}
