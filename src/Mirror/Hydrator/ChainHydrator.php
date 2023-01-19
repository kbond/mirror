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

use Zenstruck\Mirror\Exception\FailedToHydrateValue;
use Zenstruck\Mirror\Hydrator;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ChainHydrator implements Hydrator
{
    /**
     * @param Hydrator[] $hydrators
     */
    public function __construct(private iterable $hydrators)
    {
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
        foreach ($this->hydrators as $hydrator) {
            try {
                $hydrator->set($object, $name, $value);

                return;
            } catch (FailedToHydrateValue $e) {
                continue;
            }
        }

        throw $e ?? new FailedToHydrateValue(\sprintf('No hydrator able to hydrate %s::$%s with "%s".', $object::class, $name, \get_debug_type($value)));
    }
}
