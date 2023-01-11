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
use Zenstruck\Mirror\Iterator;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template T of Mirror
 * @extends Iterator<T>
 */
abstract class RecursiveClassIterator extends Iterator
{
    private bool $recursive = false;
    private bool $includeDuplicates = false;

    /**
     * @param \ReflectionClass<object> $class
     */
    final public function __construct(private \ReflectionClass $class)
    {
        parent::__construct(function() {
            foreach ($this->classes() as $class) {
                yield from $this->allForClass($class);
            }
        });
    }

    final public function recursive(): static
    {
        $clone = clone $this;
        $clone->recursive = true;

        return $clone;
    }

    final public function includeDuplicates(): static
    {
        $clone = clone $this;
        $clone->includeDuplicates = true;

        return $clone;
    }

    /**
     * @return string[]
     */
    final public function names(): array
    {
        return \array_map(static fn(Mirror $p) => $p->name(), $this->all());
    }

    final public function getIterator(): \Traversable
    {
        if ($this->includeDuplicates) {
            yield from parent::getIterator();

            return;
        }

        $returned = [];

        foreach (parent::getIterator() as $mirror) {
            if (\in_array($mirror->name(), $returned, true)) {
                continue;
            }

            $returned[] = $mirror->name();

            yield $mirror;
        }
    }

    /**
     * @return \Traversable<\ReflectionClass<object>>
     */
    final protected function classes(): \Traversable
    {
        yield $this->class;

        if (!$this->recursive) {
            return;
        }

        while ($parent = $this->class->getParentClass()) {
            yield $parent;
        }
    }

    /**
     * @param \ReflectionClass<object> $class
     *
     * @return T[]
     */
    abstract protected function allForClass(\ReflectionClass $class): array;
}
