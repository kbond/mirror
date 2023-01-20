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
use Zenstruck\Mirror\AttributesMirror;
use Zenstruck\Mirror\Exception\MirrorException;
use Zenstruck\Mirror\Exception\UnresolveableArgument;
use Zenstruck\Mirror\Internal\HasAttributes;
use Zenstruck\Mirror\Parameters;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
abstract class MirrorCallable implements AttributesMirror, \Countable
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

        return "{$this->reflector->name}()";
    }

    /**
     * @param mixed[]|array<string,mixed>|Argument[]|Argument $arguments
     */
    abstract public function __invoke(array|Argument $arguments = []): mixed;

    public static function for(callable $callable): self
    {
        if (\is_string($callable) && \str_contains($callable, '::')) {
            $callable = \explode('::', $callable);
        }

        if (\is_object($callable) && !$callable instanceof \Closure) {
            $callable = [$callable, '__invoke'];
        }

        if (\is_array($callable)) {
            return new MirrorMethod(new \ReflectionMethod($callable[0], $callable[1]));
        }

        return new MirrorFunction(new \ReflectionFunction($callable)); // @phpstan-ignore-line
    }

    final public static function closureFrom(callable $callable): \Closure
    {
        return $callable instanceof \Closure ? $callable : \Closure::fromCallable($callable);
    }

    /**
     * @param mixed[]|array<string,mixed>|Argument[]|Argument $arguments
     *
     * @throws MirrorException
     */
    abstract public function invoke(array|Argument $arguments = []): mixed;

    public function name(): string
    {
        return $this->reflector->name;
    }

    public function comment(): ?string
    {
        return $this->reflector->getDocComment() ?: null;
    }

    public function parameters(): Parameters
    {
        return new Parameters($this);
    }

    public function reflector(): \ReflectionFunctionAbstract
    {
        return $this->reflector;
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
            $arguments = \array_fill(0, \count($this), $arguments);
        }

        $arguments = $this->normalizeArgumentOrder($arguments);
        $parameters = $this->parameters()->all();

        foreach ($arguments as $key => $argument) {
            if (!$argument instanceof Argument) {
                continue;
            }

            if (!\array_key_exists($key, $parameters)) {
                if (!$argument->isOptional()) {
                    throw new \ArgumentCountError(\sprintf('Missing argument #%d for "%s". Expected type: "%s".', $key, $this, $argument->type()));
                }

                $arguments[$key] = null;

                continue;
            }

            try {
                $arguments[$key] = $argument->resolve($parameters[$key]);
            } catch (UnresolveableArgument $e) {
                throw new UnresolveableArgument(\sprintf('Unable to resolve argument for "%s" (%s).', $parameters[$key], $e->getMessage()), $e);
            }
        }

        if (\count($arguments) < $this->reflector->getNumberOfRequiredParameters()) {
            throw new \ArgumentCountError(\sprintf('Too few arguments to "%s". Expected at least %d but got %d.', $this, $this->reflector->getNumberOfRequiredParameters(), \count($arguments)));
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
            if (isset($arguments[$parameter->name])) {
                $args[] = $arguments[$parameter->name];

                continue;
            }

            if ($parameter->isDefaultValueAvailable()) {
                $args[] = $parameter->getDefaultValue();

                continue;
            }

            throw new \ArgumentCountError(\sprintf('Expected at least %d arguments for %s. Got %d.', $this->reflector->getNumberOfRequiredParameters(), $this, \count($args)));
        }

        return $args;
    }
}
