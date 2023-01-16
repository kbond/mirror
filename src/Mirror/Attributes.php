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

use Zenstruck\Mirror\Internal\Iterator;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template T of object
 * @extends Iterator<T>
 */
final class Attributes extends Iterator
{
    private ?string $name = null;
    private int $flags = 0;
    private bool $instantiate = false;

    /**
     * @param \ReflectionClass<object>|\ReflectionClassConstant|\ReflectionFunctionAbstract|\ReflectionParameter|\ReflectionProperty $reflector
     */
    public function __construct(
        private \ReflectionClass|\ReflectionClassConstant|\ReflectionFunctionAbstract|\ReflectionParameter|\ReflectionProperty $reflector
    ) {
    }

    /**
     * @param \ReflectionClass<object>|\ReflectionClassConstant|\ReflectionFunctionAbstract|\ReflectionParameter|\ReflectionProperty $reflector
     *
     * @return self<\ReflectionAttribute<object>>
     */
    public static function for(\ReflectionClass|\ReflectionClassConstant|\ReflectionFunctionAbstract|\ReflectionParameter|\ReflectionProperty $reflector): self
    {
        return new self($reflector); // @phpstan-ignore-line
    }

    /**
     * @template V of object
     *
     * @param class-string<V> $name
     *
     * @return \ReflectionAttribute<V>|null
     */
    public function firstOf(string $name): ?\ReflectionAttribute
    {
        return $this->of($name)->first(); // @phpstan-ignore-line
    }

    /**
     * @template V of object
     *
     * @param class-string<V> $name
     *
     * @return V|null
     */
    public function firstInstantiatedOf(string $name): ?object
    {
        return $this->instantiate($name)->first(); // @phpstan-ignore-line
    }

    /**
     * @param class-string $name
     */
    public function has(string $name): bool
    {
        return (bool) $this->reflector->getAttributes($name, $this->flags);
    }

    /**
     * @template V of object
     *
     * @param class-string<V> $name
     *
     * @return $this<\ReflectionAttribute<V>>
     */
    public function of(string $name): self
    {
        $clone = clone $this;
        $clone->name = $name;

        return $clone;
    }

    /**
     * @return $this<T>
     */
    public function instanceOf(): self
    {
        $clone = clone $this;
        $clone->flags |= \ReflectionAttribute::IS_INSTANCEOF;

        return $clone;
    }

    /**
     * @template V of object
     *
     * @param class-string<V>|null $name
     *
     * @return $this<V>
     */
    public function instantiate(?string $name = null): self
    {
        $clone = clone $this;
        $clone->instantiate = true;

        if ($name) {
            $clone->name = $name;
        }

        return $clone;
    }

    protected function iterator(): \Traversable
    {
        foreach ($this->reflector->getAttributes($this->name, $this->flags) as $attribute) {
            yield $this->instantiate ? $attribute->newInstance() : $attribute; // @phpstan-ignore-line
        }
    }
}
