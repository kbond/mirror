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

    /**
     * For some reason {@see \ReflectionAttribute} isn't detected as an
     * instance of {@see \Reflector} by PHP 8 (at least).
     *
     * @return \Reflector
     */
    public function reflector(): object;
}
