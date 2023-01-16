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
#[Attribute1('first')]
#[Attribute1('second')]
#[Attribute2('third')]
#[Attribute3('forth')]
class Object1 implements Interface2
{
    use Trait2;

    public const CONST1 = null;
    protected const CONST2 = null;
    private const CONST3 = null;
    private const CONST10 = null;

    public string $instanceProp1;
    protected string $instanceProp2;

    private static string $staticProp1;
    private string $instanceProp3;
    private string $instanceProp10;

    public function __construct()
    {
    }

    public function instanceMethod1(): void
    {
    }

    protected function instanceMethod2(): void
    {
    }

    private static function staticMethod1($arg = 'foo'): string
    {
        return $arg;
    }

    private function instanceMethod3(): void
    {
    }

    private function instanceMethod10($arg = 'foo'): string
    {
        return $arg;
    }
}
