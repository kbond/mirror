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

use Zenstruck\Mirror\Argument;
use Zenstruck\Mirror\Exception\ParameterTypeMismatch;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class MirrorFunction extends MirrorCallable
{
    public function __construct(private \ReflectionFunction $reflector)
    {
        parent::__construct($this->reflector);
    }

    /**
     * @param mixed[]|array<string,mixed>|Argument[]|Argument $arguments
     */
    public function __invoke(array|Argument $arguments = []): mixed
    {
        return $this->invoke($arguments);
    }

    public static function wrap(\ReflectionFunction|self $reflector): self
    {
        return $reflector instanceof self ? $reflector : new self($reflector);
    }

    public static function for(callable $callable): self
    {
        if (\is_string($callable) && \function_exists($callable)) {
            return new self(new \ReflectionFunction($callable));
        }

        return new self(new \ReflectionFunction(self::closureFrom($callable)));
    }

    /**
     * @param mixed[]|array<string,mixed>|Argument[]|Argument $arguments
     */
    public function invoke(array|Argument $arguments = []): mixed
    {
        $arguments = $this->normalizeArguments($arguments);

        try {
            return $this->reflector->invokeArgs($arguments);
        } catch (\TypeError $e) {
            throw ParameterTypeMismatch::for($e, $arguments, $this->parameters());
        }
    }

    public function reflector(): \ReflectionFunction
    {
        return $this->reflector;
    }

    public function returnType(): MirrorType
    {
        return new MirrorType($this->reflector->getReturnType(), $this->reflector->getClosureScopeClass()?->name);
    }

    public function this(): ?object
    {
        return $this->reflector->getClosureThis();
    }
}
