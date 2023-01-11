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

use Zenstruck\Mirror\ClassConstants;
use Zenstruck\Mirror\Methods;
use Zenstruck\Mirror\Properties;
use Zenstruck\MirrorMethod;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template T of object
 */
trait MirrorObjectMethods
{
    use HasAttributes;

    /**
     * @return class-string<T>
     */
    public function name(): string
    {
        return $this->reflector->name; // @phpstan-ignore-line
    }

    /**
     * @param class-string|object $class
     */
    public function isA(string|object $class): bool
    {
        return \is_a($this->name(), $class, true); // @phpstan-ignore-line
    }

    public function constants(): ClassConstants
    {
        return new ClassConstants($this->reflector);
    }

    public function properties(): Properties
    {
        return new Properties($this->reflector);
    }

    public function methods(): Methods
    {
        return new Methods($this->reflector);
    }

    /**
     * @return MirrorMethod<T|object>|null
     */
    public function constructor(): ?MirrorMethod
    {
        return $this->reflector->getConstructor() ? new MirrorMethod($this->reflector->getConstructor()) : null;
    }
}
