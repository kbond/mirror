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

use Zenstruck\Mirror\Internal\ClassReflectorIterator;
use Zenstruck\MirrorClassConstant;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @extends ClassReflectorIterator<MirrorClassConstant>
 *
 * @method MirrorClassConstant[]    getIterator()
 * @method MirrorClassConstant|null first()
 */
final class ClassConstants extends ClassReflectorIterator
{
    protected const PUBLIC = \ReflectionClassConstant::IS_PUBLIC;
    protected const PROTECTED = \ReflectionClassConstant::IS_PROTECTED;
    protected const PRIVATE = \ReflectionClassConstant::IS_PRIVATE;

    public function final(): self
    {
        return $this->filter(static fn(MirrorClassConstant $c) => $c->isFinal());
    }

    public function extendable(): self
    {
        return $this->filter(static fn(MirrorClassConstant $c) => !$c->isFinal());
    }

    /**
     * @return array<string,mixed>
     */
    public function values(): array
    {
        return $this->map(fn(MirrorClassConstant $c) => $c->value(), true);
    }

    /**
     * @return MirrorClassConstant<object>[]
     */
    protected function allForClass(\ReflectionClass $class): array
    {
        return \array_map(
            static fn(\ReflectionClassConstant $m) => new MirrorClassConstant($m),
            $class->getReflectionConstants(...$this->flags())
        );
    }
}
