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
 * @template T of Mirror
 * @extends MirrorIterator<T>
 */
abstract class RecursiveClassIterator extends MirrorIterator
{
    private bool $recursive = false;
    private bool $includeDuplicates = false;

    /**
     * @param \ReflectionClass<object> $class
     */
    final public function __construct(private \ReflectionClass $class)
    {
    }

    final public function recursive(bool $includeDuplicates = false): static
    {
        $clone = clone $this;
        $clone->recursive = true;
        $clone->includeDuplicates = $includeDuplicates;

        return $clone;
    }

    final public function getIterator(): \Traversable
    {
        $returned = [];

        foreach ($this->classes() as $class) {
            foreach ($this->allForClass($class) as $mirror) {
                if (\in_array($mirror->name(), $returned, true)) {
                    continue;
                }

                if (!$this->includeDuplicates) {
                    $returned[] = $mirror->name();
                }

                foreach ($this->filters as $filter) {
                    if (!$filter($mirror)) {
                        continue 2;
                    }
                }

                yield $mirror;
            }
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

        $class = $this->class;

        while ($class = $class->getParentClass()) {
            yield $class;
        }
    }

    /**
     * @param \ReflectionClass<object> $class
     *
     * @return T[]
     */
    abstract protected function allForClass(\ReflectionClass $class): iterable;
}
