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

use Zenstruck\Mirror;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @immutable
 *
 * @template T of Mirror
 * @implements \IteratorAggregate<T>
 */
abstract class MirrorIterator implements \IteratorAggregate, \Countable
{
    /** @var (callable(T):bool)[] */
    private array $filters = [];

    /**
     * @return string[]
     */
    final public function names(): array
    {
        return $this->map(static fn(Mirror $p) => $p->name());
    }

    public function has(string $name): bool
    {
        return \in_array($name, $this->names(), true);
    }

    /**
     * @return T|null
     */
    final public function first(): mixed
    {
        foreach ($this as $item) {
            return $item;
        }

        return null;
    }

    /**
     * @param callable(T):bool $filter
     */
    final public function filter(callable $filter): static
    {
        $clone = clone $this;
        $clone->filters[] = $filter;

        return $clone;
    }

    /**
     * @return T[]
     */
    final public function all(): array
    {
        return \iterator_to_array($this);
    }

    /**
     * @template V
     *
     * @param callable(T):V $fn
     *
     * @return V[]
     */
    final public function map(callable $fn): array
    {
        return \array_map($fn, $this->all());
    }

    /**
     * @return \Traversable<T>|T[]
     */
    final public function getIterator(): \Traversable
    {
        $iterator = new \IteratorIterator($this->iterator());

        foreach ($this->filters as $filter) {
            $iterator = new \CallbackFilterIterator($iterator, $filter);
        }

        foreach ($iterator as $item) {
            yield $item;
        }
    }

    final public function count(): int
    {
        return \iterator_count($this);
    }

    /**
     * @return \Traversable<T>
     */
    abstract protected function iterator(): \Traversable;
}
