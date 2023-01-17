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

use Zenstruck\Mirror\Internal\MirrorIterator;
use Zenstruck\MirrorAttribute;
use Zenstruck\MirrorCallable;
use Zenstruck\MirrorClass;
use Zenstruck\MirrorClassConstant;
use Zenstruck\MirrorFunction;
use Zenstruck\MirrorMethod;
use Zenstruck\MirrorObject;
use Zenstruck\MirrorParameter;
use Zenstruck\MirrorProperty;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template T of object
 * @template V of AttributesMirror
 *
 * @extends MirrorIterator<MirrorAttribute<T,V>>
 *
 * @method MirrorAttribute[]    getIterator()
 * @method MirrorAttribute|null first()
 */
final class Attributes extends MirrorIterator
{
    private ?string $name = null;
    private int $flags = 0;

    public function __construct(private AttributesMirror $mirror)
    {
    }

    /**
     * @param object|class-string|callable $reflector
     *
     * @return self<object,AttributesMirror>
     */
    public static function for(callable|object|string $reflector): self
    {
        return new self(
            match (true) {
                $reflector instanceof AttributesMirror => $reflector,
                $reflector instanceof \ReflectionClass => MirrorClass::wrap($reflector),
                $reflector instanceof \ReflectionClassConstant => MirrorClassConstant::wrap($reflector),
                $reflector instanceof \ReflectionProperty => MirrorProperty::wrap($reflector),
                $reflector instanceof \ReflectionMethod => MirrorMethod::wrap($reflector),
                $reflector instanceof \ReflectionFunction => MirrorFunction::wrap($reflector),
                $reflector instanceof \ReflectionParameter => MirrorParameter::wrap($reflector),
                \is_callable($reflector) => MirrorCallable::for($reflector),
                \is_object($reflector) => MirrorObject::for($reflector),
                default => MirrorClass::for($reflector),
            }
        );
    }

    /**
     * @template Z of object
     *
     * @param class-string<Z> $name
     *
     * @return MirrorAttribute<Z,V>|null
     */
    public function firstOf(string $name, bool $instanceOf = false): ?MirrorAttribute
    {
        return $this->of($name, $instanceOf)->first();
    }

    /**
     * @template Z of object
     *
     * @param class-string<Z> $name
     *
     * @return Z|null
     */
    public function firstInstantiatedOf(string $name, bool $instanceOf = false): ?object
    {
        return $this->firstOf($name, $instanceOf)?->instantiate();
    }

    /**
     * @template Z of object
     *
     * @param class-string<Z> $name
     *
     * @return $this<Z,V>
     */
    public function of(string $name, bool $instanceOf = false): self
    {
        $clone = clone $this;
        $clone->name = $name;
        $clone->flags = $instanceOf ? \ReflectionAttribute::IS_INSTANCEOF : 0;

        return $clone;
    }

    /**
     * @return \Traversable<T>
     */
    public function instantiate(): \Traversable
    {
        foreach ($this as $attribute) {
            yield $attribute->instantiate(); // @phpstan-ignore-line
        }
    }

    /**
     * @template Z of object
     *
     * @param class-string<Z> $name
     */
    public function has(string $name, bool $instanceOf = false): bool
    {
        return isset($this->mirror->reflector()->getAttributes($name, $instanceOf ? \ReflectionAttribute::IS_INSTANCEOF : 0)[0]);
    }

    protected function iterator(): \Traversable
    {
        foreach ($this->mirror->reflector()->getAttributes($this->name, $this->flags) as $attribute) {
            yield new MirrorAttribute($attribute, $this->mirror); // @phpstan-ignore-line
        }
    }
}
