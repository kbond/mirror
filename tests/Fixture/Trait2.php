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
trait Trait2
{
    use Trait1, Trait4;

    #[Attribute2('first')]
    #[Attribute1('second')]
    #[Attribute1('third')]
    #[Attribute3('fourth')]
    public string $instanceProp1;

    #[Attribute2('first')]
    protected string $instanceProp2;

    #[Attribute2('first')]
    public function instanceMethod1(): void
    {
    }

    protected function instanceMethod2(): void
    {
    }
}
