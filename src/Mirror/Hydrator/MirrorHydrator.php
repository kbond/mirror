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
use Zenstruck\MirrorObject;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class MirrorHydrator implements Hydrator
{
    public function __invoke(object $object, array $values = []): object
    {
        $mirror = MirrorObject::for($object);

        foreach ($values as $name => $value) {
            $this->set($mirror, $name, $value);
        }

        return $object;
    }

    public function set(object $object, string $name, mixed $value): void
    {
        $object = MirrorObject::wrap($object);

        try {
            $object->set($name, $value);
        } catch (\ReflectionException|\TypeError $e) {
            // todo type coercion system on type error
            throw new FailedToHydrateValue($e->getMessage(), previous: $e);
        }
    }
}
