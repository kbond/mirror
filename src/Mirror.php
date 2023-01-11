<?php

/*
 * This file is part of the zenstruck/mirror package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
interface Mirror extends \Stringable
{
    public function name(): string;

    public function reflector(): \Reflector;
}
