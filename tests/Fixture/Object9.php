<?php

declare(strict_types=1);

/*
 * This file is part of the zenstruck/mirror package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Tests\Fixture;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class Object9
{
    private string $prop1 = 'original1';
    private string $prop2 = 'original2';
    private Collection $collectionItems;
    private \Traversable $traversableItems;

    public function __construct()
    {
        $this->collectionItems = new ArrayCollection();
        $this->traversableItems = new \ArrayIterator();
    }

    public function getProp1()
    {
        return $this->prop1;
    }

    public function setProp1(string $value)
    {
        $this->prop1 = $value;
    }

    public function getProp2()
    {
        return $this->prop2;
    }

    public function getCollectionItems(): array
    {
        return $this->collectionItems->toArray();
    }

    public function addCollectionItem(string $item): void
    {
        $this->collectionItems->add($item);
    }

    public function removeCollectionItem(string $item): void
    {
        $this->collectionItems->removeElement($item);
    }

    public function getTraversableItems(): array
    {
        return \iterator_to_array($this->traversableItems);
    }

    public function addTraversableItem(string $item): void
    {
        $this->traversableItems[] = $item;
    }

    public function removeTraversableItem(string $item): void
    {
        // noop
    }

    public function traversableItems(): \Traversable
    {
        return $this->traversableItems;
    }
}
