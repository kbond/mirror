<?php

/*
 * This file is part of the zenstruck/mirror package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Mirror\Hydrator;

use Symfony\Component\PropertyAccess\Exception\AccessException;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Zenstruck\Mirror\Exception\FailedToHydrateValue;
use Zenstruck\Mirror\Hydrator;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class PropertyAccessHydrator implements Hydrator
{
    private PropertyAccessorInterface $accessor;

    public function __construct(?PropertyAccessorInterface $accessor = null)
    {
        if (!\interface_exists(PropertyAccessorInterface::class)) {
            throw new \LogicException('symfony/property-access is required. Install with "composer require symfony/property-access".');
        }

        $this->accessor = $accessor ?? new PropertyAccessor();
    }

    public function __invoke(object $object, array $values = []): object
    {
        foreach ($values as $name => $value) {
            $this->set($object, $name, $value);
        }

        return $object;
    }

    public function set(object $object, string $name, mixed $value): void
    {
        try {
            $this->accessor->setValue($object, $name, $value);
        } catch (AccessException $e) {
            throw new FailedToHydrateValue($e->getMessage(), previous: $e);
        }
    }
}
