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
final class Object9
{
    private string $prop1 = 'original1';
    private string $prop2 = 'original2';

    public function getProp1()
    {
        return $this->prop1;
    }

    public function setProp1(string $value)
    {
        $this->prop1 = $value;
    }

    public function getProp2()
    {
        return $this->prop2;
    }
}
