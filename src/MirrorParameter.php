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

use Zenstruck\Mirror\Internal\HasAttributes;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class MirrorParameter implements Mirror
{
    use HasAttributes;

    public function __construct(private \ReflectionParameter $reflector)
    {
    }

    public function __toString(): string
    {
        return \sprintf('$%s (#%s) <%s>', $this->name(), $this->reflector->getPosition(), $this->function());
    }

    public static function for(callable $function, int|string $parameter): self
    {
        return new self(new \ReflectionParameter($function, $parameter)); // @phpstan-ignore-line
    }

    public static function wrap(\ReflectionParameter|self $reflector): self
    {
        return $reflector instanceof self ? $reflector : new self($reflector);
    }

    public function reflector(): \ReflectionParameter
    {
        return $this->reflector;
    }

    public function name(): string
    {
        return $this->reflector->name;
    }

    public function isOptional(): bool
    {
        return $this->reflector->isOptional();
    }

    public function isRequired(): bool
    {
        return !$this->isOptional();
    }

    public function isVariadic(): bool
    {
        return $this->reflector->isVariadic();
    }

    public function defaultValue(): mixed
    {
        return $this->reflector->getDefaultValue();
    }

    public function function(): MirrorCallable
    {
        $function = $this->reflector->getDeclaringFunction();

        return $function instanceof \ReflectionMethod ? new MirrorMethod($function) : new MirrorFunction($function); // @phpstan-ignore-line
    }

    public function position(): int
    {
        return $this->reflector->getPosition();
    }

    public function type(): MirrorType
    {
        return new MirrorType($this->reflector->getType(), $this->reflector->getDeclaringClass()?->name);
    }
}
