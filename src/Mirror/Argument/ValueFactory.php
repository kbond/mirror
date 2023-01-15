<?php

/*
 * This file is part of the zenstruck/mirror package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Mirror\Argument;

use Zenstruck\Mirror\Argument;
use Zenstruck\MirrorFunction;
use Zenstruck\MirrorType;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 */
final class ValueFactory
{
    /** @var callable */
    private $factory;

    public function __construct(private string $type, callable $factory)
    {
        if ($factory instanceof self) {
            throw new \InvalidArgumentException('Cannot nest value factories.');
        }

        $this->factory = $factory;
    }

    public function __invoke(MirrorType $type): mixed
    {
        return MirrorFunction::for($this->factory)
            ->invoke(Argument::new(
                $type,
                $type->types(),
                (string) $type,
            ))
        ;
    }

    public function type(): string
    {
        return $this->type;
    }
}
