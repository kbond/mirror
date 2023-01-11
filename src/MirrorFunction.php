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

    /**
     * @param mixed[]|array<string,mixed>|Argument[]|Argument $arguments
     */
    public function invoke(array|Argument $arguments = []): mixed
    {
        return $this->reflector->invokeArgs($this->normalizeArguments($arguments));
    }

    public static function for(callable $callable): self
    {
        if (\is_string($callable) && \function_exists($callable)) {
            return new self(new \ReflectionFunction($callable));
        }

        return new self(new \ReflectionFunction(self::closureFrom($callable)));
    }

    public function reflector(): \ReflectionFunction
    {
        return $this->reflector;
    }

    public function returnType(): MirrorType
    {
        return new MirrorType($this->reflector->getReturnType(), $this->reflector->getClosureScopeClass()?->name);
    }
}
