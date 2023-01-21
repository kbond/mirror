<?php

/*
 * This file is part of the zenstruck/mirror package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck;

use Zenstruck\Mirror\Argument;
use Zenstruck\Mirror\Exception\ObjectInstanceRequired;
use Zenstruck\Mirror\Exception\ParameterTypeMismatch;
use Zenstruck\Mirror\Internal\VisibilityMethods;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template T of object
 */
final class MirrorMethod extends MirrorCallable
{
    use VisibilityMethods;

    /**
     * @param T|null $object
     */
    public function __construct(private \ReflectionMethod $reflector, private ?object $object = null)
    {
        $this->reflector->setAccessible(true);

        parent::__construct($this->reflector);
    }

    /**
     * @param mixed[]|array<string,mixed>|Argument[]|Argument $arguments
     * @param T|null                                          $object
     */
    public function __invoke(array|Argument $arguments = [], ?object $object = null): mixed
    {
        return $this->invoke($arguments, $object);
    }

    /**
     * @param class-string<T>|T|(T&callable) $object
     *
     * @return self<T>
     */
    public static function for(string|object|callable $object, ?string $method = null): self
    {
        if (!$method && \is_object($object) && \is_callable($object)) {
            $method = '__invoke';
        }

        return new self(new \ReflectionMethod($object, $method)); // @phpstan-ignore-line
    }

    /**
     * @param \ReflectionMethod|MirrorMethod<T> $reflector
     *
     * @return self<T>
     */
    public static function wrap(\ReflectionMethod|self $reflector): self
    {
        return $reflector instanceof self ? $reflector : new self($reflector); // @phpstan-ignore-line
    }

    /**
     * @param mixed[]|array<string,mixed>|Argument[]|Argument $arguments
     * @param T|null                                          $object
     *
     * @throws ObjectInstanceRequired
     */
    public function invoke(array|Argument $arguments = [], ?object $object = null): mixed
    {
        $object ??= $this->object;

        if (!$object && $this->isInstance()) {
            throw new ObjectInstanceRequired(\sprintf('Method "%s" is not static so an object instance is required to invoke.', $this));
        }

        $arguments = $this->normalizeArguments($arguments);

        try {
            return $this->reflector->invokeArgs($object, $arguments);
        } catch (\TypeError $e) { // @phpstan-ignore-line
            throw ParameterTypeMismatch::for($e, $arguments, $this->parameters());
        }
    }

    public function reflector(): \ReflectionMethod
    {
        return $this->reflector;
    }

    public function returnType(): MirrorType
    {
        return new MirrorType($this->reflector->getReturnType(), $this->reflector->class);
    }

    public function isStatic(): bool
    {
        return $this->reflector->isStatic();
    }

    public function isInstance(): bool
    {
        return !$this->isStatic();
    }

    public function isFinal(): bool
    {
        return $this->reflector->isFinal();
    }

    public function isExtendable(): bool
    {
        return !$this->isFinal();
    }

    public function isAbstract(): bool
    {
        return $this->reflector->isAbstract();
    }

    public function isConcrete(): bool
    {
        return !$this->isAbstract();
    }

    public function isConstructor(): bool
    {
        return $this->reflector->isConstructor();
    }

    public function isDestructor(): bool
    {
        return $this->reflector->isDestructor();
    }

    /**
     * @return MirrorClass<T>
     */
    public function class(): MirrorClass
    {
        return new MirrorClass($this->reflector->getDeclaringClass()); // @phpstan-ignore-line
    }
}
