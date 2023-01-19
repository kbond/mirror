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

use Zenstruck\Mirror\Exception\FailedToHydrateValue;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
interface Hydrator
{
    /**
     * @template T of object
     *
     * @param T                   $object
     * @param array<string,mixed> $values
     *
     * @return T
     *
     * @throws FailedToHydrateValue if unable to find or set property
     */
    public function __invoke(object $object, array $values = []): object;

    /**
     * @throws FailedToHydrateValue if unable to find or set property
     */
    public function set(object $object, string $name, mixed $value): void;
}
