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

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class MirrorType
{
    /**
     * Allow exact type (always enabled).
     */
    public const EXACT = 0;

    /**
     * If type is class, parent classes are supported.
     */
    public const COVARIANCE = 2;

    /**
     * If type is class, child classes are supported.
     */
    public const CONTRAVARIANCE = 4;

    /**
     * If type is string, do not support other scalar types. Follows
     * same logic as "declare(strict_types=1)".
     */
    public const STRICT = 8;

    /**
     * If type is float, do not support int (implies {@see STRICT).
     */
    public const VERY_STRICT = 16;

    public const DEFAULT = self::EXACT | self::COVARIANCE;

    private const TYPE_NORMALIZE_MAP = [
        'boolean' => 'bool',
        'integer' => 'int',
        'double' => 'float',
        'resource (closed)' => 'resource',
        'NULL' => 'null',
    ];
    private const ALLOWED_TYPE_MAP = [
        'string' => ['bool', 'int', 'float'],
        'bool' => ['string', 'int', 'float'],
        'float' => ['string', 'int', 'bool'],
        'int' => ['string', 'float', 'bool'],
    ];

    /** @var string[] */
    private array $types;

    /**
     * @param class-string|null $declaringClass
     */
    public function __construct(private ?\ReflectionType $reflector, private ?string $declaringClass = null)
    {
    }

    public function __toString(): string
    {
        return \implode($this->reflector instanceof \ReflectionIntersectionType ? '&' : '|', $this->types()) ?: '(none)';
    }

    /**
     * @return string[]
     */
    public function types(): array
    {
        if (isset($this->types)) {
            return $this->types;
        }

        if (!$this->reflector) {
            return $this->types = [];
        }

        $this->types = \array_map(
            fn(string $v) => 'self' === $v && $this->declaringClass ? $this->declaringClass : $v,
            match (true) {
                $this->reflector instanceof \ReflectionNamedType => [$this->reflector->getName()],
                $this->reflector instanceof \ReflectionUnionType => $this->reflector->getTypes(),
                $this->reflector instanceof \ReflectionIntersectionType => $this->reflector->getTypes(),
                default => ['mixed'],
            }
        );

        if (['mixed'] !== $this->types && $this->allowsNull()) {
            $this->types[] = 'null';
        }

        return $this->types = \array_unique($this->types);
    }

    public function isNamed(): bool
    {
        return $this->reflector instanceof \ReflectionNamedType;
    }

    public function isUnion(): bool
    {
        return $this->reflector instanceof \ReflectionUnionType;
    }

    public function isIntersection(): bool
    {
        return $this->reflector instanceof \ReflectionIntersectionType;
    }

    public function allowsNull(): bool
    {
        return !$this->reflector || $this->reflector->allowsNull();
    }

    public function isBuiltin(): bool
    {
        if (!$reflector = $this->reflector) {
            return true;
        }

        if ($reflector instanceof \ReflectionNamedType) {
            return $reflector->isBuiltin();
        }

        foreach ($reflector->getTypes() as $type) { // @phpstan-ignore-line
            if (!$type->isBuiltin()) {
                return false;
            }
        }

        return true;
    }

    public function hasType(): bool
    {
        return $this->reflector instanceof \ReflectionType;
    }

    /**
     * @param int-mask<self::EXACT,self::COVARIANCE,self::CONTRAVARIANCE,self::STRICT,self::VERY_STRICT> $mode
     */
    public function supports(string $type, int $mode = self::DEFAULT): bool
    {
        if (!$this->reflector) {
            // no type-hint so any type is supported
            return true;
        }

        if ($this->reflector instanceof \ReflectionIntersectionType) {
            foreach ($this->reflector->getTypes() as $refType) {
                $subType = clone $this;
                $subType->reflector = $refType;

                if (!$subType->supports($type, $mode)) {
                    return false;
                }
            }

            return true;
        }

        $type = self::TYPE_NORMALIZE_MAP[$type] ?? $type;

        foreach ($this->types() as $supportedType) {
            if ($type === $supportedType) {
                // exact match
                return true;
            }

            if ('object' === $supportedType && (\class_exists($type) || \interface_exists($type))) {
                return true;
            }

            if ($mode & self::COVARIANCE && \is_a($type, $supportedType, true)) {
                return true;
            }

            if ($mode & self::CONTRAVARIANCE && \is_a($supportedType, $type, true)) {
                return true;
            }

            if ('iterable' === $supportedType && ('array' === $type || \is_a($type, \Traversable::class, true))) {
                return true;
            }

            if ($mode & self::VERY_STRICT) {
                continue;
            }

            if ('float' === $supportedType && 'int' === $type) {
                // strict typing allows int to pass a float validation
                return true;
            }

            if ($mode & self::STRICT) {
                continue;
            }

            if (\in_array($type, self::ALLOWED_TYPE_MAP[$supportedType] ?? [], true)) {
                return true;
            }

            if ('string' === $supportedType && \method_exists($type, '__toString')) {
                return true;
            }
        }

        return false;
    }

    public function accepts(mixed $value, bool $strict = false): bool
    {
        if (!$this->reflector) {
            // no type-hint so any type is supported
            return true;
        }

        $type = \get_debug_type($value);

        if (!$this->supports($type, $strict ? self::DEFAULT | self::STRICT : self::DEFAULT)) {
            return false;
        }

        if ('string' === $type && !\is_numeric($value) && !\in_array('string', $this->types(), true)) {
            // non-numeric strings cannot be used for float/int
            return false;
        }

        return true;
    }
}
