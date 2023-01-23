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
#[Attribute2('first')]
#[Attribute1('second')]
#[Attribute1('third')]
#[Attribute3('fourth')]
class Object1 implements Interface2
{
    use Trait2;

    #[Attribute2('first')]
    #[Attribute1('second')]
    #[Attribute1('third')]
    #[Attribute3('fourth')]
    public const CONST1 = null;

    #[Attribute2('first')]
    protected const CONST2 = null;

    private const CONST3 = null;

    #[Attribute2('second')]
    private const CONST10 = null;

    private static string $staticProp1;
    private string $instanceProp3;

    #[Attribute2('second')]
    private string $instanceProp10;

    #[Attribute2('first')]
    #[Attribute1('second')]
    #[Attribute1('third')]
    #[Attribute3('fourth')]
    public function __construct(
        #[Attribute2('first')]
        #[Attribute1('second')]
        #[Attribute1('third')]
        #[Attribute3('fourth')]
        string $param = '',

        #[Attribute2('first')]
        string $param2 = '',
    ) {
    }

    private static function staticMethod1(string $arg = 'foo'): string
    {
        return $arg;
    }

    private function instanceMethod3(): void
    {
    }

    #[Attribute2('second')]
    private function instanceMethod10($arg = 'foo'): string
    {
        return $arg;
    }
}
