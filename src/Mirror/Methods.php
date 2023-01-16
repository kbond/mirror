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
use Zenstruck\MirrorMethod;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @extends ClassReflectorIterator<MirrorMethod>
 *
 * @method MirrorMethod[] getIterator()
 */
final class Methods extends ClassReflectorIterator
{
    protected const PUBLIC = \ReflectionMethod::IS_PUBLIC;
    protected const PROTECTED = \ReflectionMethod::IS_PROTECTED;
    protected const PRIVATE = \ReflectionMethod::IS_PRIVATE;

    public function static(): self
    {
        return $this->filter(static fn(MirrorMethod $p) => $p->isStatic());
    }

    public function instance(): self
    {
        return $this->filter(static fn(MirrorMethod $p) => $p->isInstance());
    }

    public function final(): self
    {
        return $this->filter(static fn(MirrorMethod $p) => $p->isFinal());
    }

    public function extendable(): self
    {
        return $this->filter(static fn(MirrorMethod $p) => $p->isExtendable());
    }

    public function abstract(): self
    {
        return $this->filter(static fn(MirrorMethod $p) => $p->isAbstract());
    }

    public function concrete(): self
    {
        return $this->filter(static fn(MirrorMethod $p) => $p->isConcrete());
    }

    /**
     * @return MirrorMethod<object>[]
     */
    protected function allForClass(\ReflectionClass $class): array
    {
        return \array_map(
            static fn(\ReflectionMethod $m) => new MirrorMethod($m),
            $class->getMethods(...$this->flags())
        );
    }
}
