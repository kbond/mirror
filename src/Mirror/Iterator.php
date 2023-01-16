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

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @immutable
 *
 * @template T of object
 * @implements \IteratorAggregate<T>
 */
class Iterator implements \IteratorAggregate, \Countable
{
    /** @var (callable(T):bool)[] */
    protected array $filters = [];
    /** @var \Traversable<T>|\Closure():\Traversable<T> */
    private \Traversable|\Closure $items;

    /**
     * @param iterable<T>|\Closure():\Traversable<T> $items
     */
    public function __construct(iterable|\Closure $items)
    {
        $this->items = \is_array($items) ? new \ArrayIterator($items) : $items;
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
    public function map(callable $fn): array
    {
        return \array_map($fn, $this->all());
    }

    /**
     * @return \Traversable<T>|T[]
     */
    public function getIterator(): \Traversable
    {
        $iterator = new \IteratorIterator($this->items instanceof \Closure ? ($this->items)() : $this->items);

        foreach ($this->filters as $filter) {
            $iterator = new \CallbackFilterIterator($iterator, $filter);
        }

        yield from $iterator;
    }

    final public function count(): int
    {
        return \iterator_count($this);
    }
}
