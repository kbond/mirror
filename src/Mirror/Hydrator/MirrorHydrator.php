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

use Zenstruck\Mirror\Exception\FailedToTransformType;
use Zenstruck\Mirror\Exception\NoSuchProperty;
use Zenstruck\Mirror\Exception\TypeMismatch;
use Zenstruck\Mirror\Hydrator;
use Zenstruck\Mirror\Hydrator\Transformer\ChainTypeTransformer;
use Zenstruck\MirrorObject;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class MirrorHydrator implements Hydrator
{
    private static TypeTransformer $defaultTransformer;
    private TypeTransformer $typeTransformer;

    public function __construct(?TypeTransformer $typeTransformer = null)
    {
        $this->typeTransformer = $typeTransformer ?? self::defaultTransformer();
    }

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

            return;
        } catch (\ReflectionException $e) {
            throw new NoSuchProperty($e->getMessage(), previous: $e);
        } catch (\TypeError) {
        }

        try {
            $this->set($object, $name, $this->typeTransformer->transform($object->propertyOrFail($name)->type(), $value));
        } catch (FailedToTransformType $e) {
            throw new TypeMismatch($e->getMessage(), previous: $e);
        }
    }

    private static function defaultTransformer(): TypeTransformer
    {
        return self::$defaultTransformer ??= new ChainTypeTransformer();
    }
}
