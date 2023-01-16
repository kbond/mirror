<?php

/*
 * This file is part of the zenstruck/mirror package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Tests\Fixture;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
abstract class AbstractObject1
{
    public static function extendableStaticMethod()
    {
    }

    final public static function finalStaticMethod()
    {
    }

    public function extendableMethod()
    {
    }

    final public function finalMethod()
    {
    }

    abstract public function abstractMethod();
}
