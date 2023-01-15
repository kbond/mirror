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
use Zenstruck\Mirror\Exception\UnresolveableArgument;
use Zenstruck\Mirror\Internal\HasAttributes;
use Zenstruck\Mirror\Parameters;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
abstract class MirrorCallable implements Mirror, \Countable
{
    use HasAttributes;

    protected function __construct(private \ReflectionFunctionAbstract $reflector)
    {
    }

    public function __toString(): string
    {
        if ($this->reflector->isClosure()) {
            return "(closure) {$this->reflector->getFileName()}:{$this->reflector->getStartLine()}";
        }

        if ($this->reflector instanceof \ReflectionMethod) {
            return "{$this->reflector->class}::{$this->reflector->name}()";
        }

        return "(function) {$this->reflector->name}()";
    }

    final public static function closureFrom(callable $callable): \Closure
    {
        return $callable instanceof \Closure ? $callable : \Closure::fromCallable($callable);
    }

    public function name(): string
    {
        return $this->reflector->name;
    }

    public function parameters(): Parameters
    {
        return new Parameters($this->reflector);
    }

    public function count(): int
    {
        return $this->reflector->getNumberOfParameters();
    }

    /**
     * @internal
     *
     * @param mixed[]|array<string,mixed>|Argument[]|Argument $arguments
     *
     * @return mixed[]
     */
    public function normalizeArguments(array|Argument $arguments): array
    {
        if ($arguments instanceof Argument) {
            return \array_fill(0, \count($this), $arguments);
        }

        $arguments = $this->normalizeArgumentOrder($arguments);
        $parameters = $this->parameters()->all();

        foreach ($arguments as $key => $argument) {
            if (!$argument instanceof Argument) {
                continue;
            }

            if (!\array_key_exists($key, $parameters)) {
                if (!$argument->isOptional()) {
                    throw new \ArgumentCountError(); // todo
                }

                $arguments[$key] = null;

                continue;
            }

            try {
                $arguments[$key] = $argument->resolve($parameters[$key]);
            } catch (UnresolveableArgument) {
                throw new UnresolveableArgument(); // todo
            }
        }

        return $arguments;
    }

    /**
     * @param array<string,mixed>|mixed[] $arguments
     *
     * @return mixed[]
     */
    private function normalizeArgumentOrder(array $arguments): array
    {
        if (array_is_list($arguments)) {
            return $arguments;
        }

        $args = [];

        foreach ($this->reflector->getParameters() as $parameter) {
            if (!isset($arguments[$parameter->name])) {
                continue;
            }

            $args[] = $arguments[$parameter->name];
        }

        return $args;
    }
}
