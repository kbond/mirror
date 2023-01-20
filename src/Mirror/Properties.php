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
use Zenstruck\MirrorProperty;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @extends ClassReflectorIterator<MirrorProperty>
 *
 * @method MirrorProperty[]    getIterator()
 * @method MirrorProperty|null first()
 */
final class Properties extends ClassReflectorIterator
{
    protected const PUBLIC = \ReflectionProperty::IS_PUBLIC;
    protected const PROTECTED = \ReflectionProperty::IS_PROTECTED;
    protected const PRIVATE = \ReflectionProperty::IS_PRIVATE;

    public function __construct(\ReflectionClass $class, private ?object $object)
    {
        parent::__construct($class);
    }

    public function static(): self
    {
        return $this->filter(static fn(MirrorProperty $p) => $p->isStatic());
    }

    public function instance(): self
    {
        return $this->filter(static fn(MirrorProperty $p) => $p->isInstance());
    }

    public function readOnly(): self
    {
        return $this->filter(static fn(MirrorProperty $p) => $p->isReadOnly());
    }

    public function modifiable(): self
    {
        return $this->filter(static fn(MirrorProperty $p) => $p->isModifiable());
    }

    /**
     * @return array<string,mixed>
     */
    public function values(?object $object = null): array
    {
        return $this->map(fn(MirrorProperty $p) => $p->get($object), namesAsKeys: true);
    }

    /**
     * @return MirrorProperty<object>[]
     */
    protected function allForClass(\ReflectionClass $class): array
    {
        return \array_map(
            fn(\ReflectionProperty $m) => new MirrorProperty($m, $this->object),
            $class->getProperties(...$this->flags())
        );
    }
}
