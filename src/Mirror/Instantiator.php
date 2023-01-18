<?php

/*
 * This file is part of the zenstruck/mirror package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Mirror;

use Zenstruck\Mirror\Exception\FailedToInstantiate;
use Zenstruck\MirrorCallable;
use Zenstruck\MirrorClass;
use Zenstruck\MirrorFunction;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class Instantiator
{
    /** @var callable(mixed[]):object|callable(array<string,mixed>):object|string|bool */
    private $mode;

    /**
     * @param callable(mixed[]):object|callable(array<string,mixed>):object|string|bool $mode
     */
    private function __construct(callable|string|bool $mode)
    {
        $this->mode = $mode;
    }

    /**
     * @template T of object
     *
     * @param class-string<T>             $class
     * @param mixed[]|array<string,mixed> $arguments
     *
     * @return T
     *
     * @throws FailedToInstantiate
     */
    public function __invoke(string $class, array &$arguments = []): object
    {
        /** @var MirrorClass<T> $mirror */
        $mirror = MirrorClass::for($class);

        if (false === $this->mode) {
            return $mirror->isConcrete() ? $mirror->instantiateWithoutConstructor() : throw new FailedToInstantiate(\sprintf('%s is not a concrete class.', $mirror));
        }

        if (!$instantiator = $this->instantiatorFor($mirror)) {
            return $mirror->instantiate();
        }

        $object = true === $this->mode ? $mirror->instantiate($arguments) : $instantiator->invoke($arguments);

        if (!$object instanceof $class) {
            throw new FailedToInstantiate(\sprintf('Expected to instantiate "%s", got "%s".', $class, \get_debug_type($object)));
        }

        $arguments = \array_diff_key($arguments, \array_flip($instantiator->parameters()->names()));

        return $object;
    }

    public static function withConstructor(): self
    {
        return new self(true);
    }

    public static function withoutConstructor(): self
    {
        return new self(false);
    }

    /**
     * @param callable(mixed[]):object|callable(array<string,mixed>):object|string $callable
     */
    public static function with(callable|string $callable): self
    {
        return new self($callable);
    }

    /**
     * @param MirrorClass<object> $class
     */
    private function instantiatorFor(MirrorClass $class): ?MirrorCallable
    {
        if (true === $this->mode) {
            return $class->isInstantiable() ? $class->constructor() : throw new FailedToInstantiate(\sprintf('%s is not instantiable.', $class));
        }

        if (\is_string($this->mode) && $method = $class->method($this->mode)) {
            return $method->isStatic() && $method->isPublic() ? $method : throw new FailedToInstantiate(\sprintf('Cannot use "%s" to instantiate %s (method must be public/static).', $method, $class));
        }

        if (\is_callable($this->mode)) {
            return MirrorFunction::for($this->mode);
        }

        throw new FailedToInstantiate(\sprintf('Unable to use "%s" to instantiate %s.', $this->mode, $class));
    }
}
