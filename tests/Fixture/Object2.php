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
class Object2 extends Object1 implements Interface1, Interface3
{
    use Trait1, Trait3;

    public const CONST4 = null;
    protected const CONST5 = null;
    private const CONST6 = null;
    private const CONST3 = null;

    public string $instanceProp4;
    protected string $instanceProp5;
    private string $instanceProp6;
    private string $instanceProp3;

    public function instanceMethod4(): void
    {
    }

    protected function instanceMethod5(): void
    {
    }

    private function instanceMethod6(): void
    {
    }

    private function instanceMethod3(): void
    {
    }
}
