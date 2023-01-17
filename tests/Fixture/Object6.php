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
class Object6
{
    public string $prop = 'original';

    public function __construct(string $prop = 'constructor')
    {
        $this->prop = $prop;
    }

    public function __destruct()
    {
    }

    #[Attribute2('first')]
    #[Attribute1('second')]
    #[Attribute1('third')]
    #[Attribute3('fourth')]
    public function __invoke()
    {
    }

    #[Attribute2('first')]
    #[Attribute1('second')]
    #[Attribute1('third')]
    #[Attribute3('fourth')]
    public static function factory(string $prop = 'factory'): self
    {
        return new self($prop);
    }

    public static function closureSelf(): \Closure
    {
        return fn(self $object) => $object;
    }

    #[Attribute2('first')]
    #[Attribute1('second')]
    #[Attribute1('third')]
    #[Attribute3('fourth')]
    public function method()
    {
    }
}
