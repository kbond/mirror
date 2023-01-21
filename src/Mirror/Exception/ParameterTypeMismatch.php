<?php

/*
 * This file is part of the zenstruck/mirror package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Mirror\Exception;

use Zenstruck\Mirror\Parameters;
use Zenstruck\MirrorParameter;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ParameterTypeMismatch extends TypeMismatch
{
    public function __construct(private mixed $value, private MirrorParameter $parameter, ?\Throwable $previous = null)
    {
        parent::__construct(
            \sprintf('Parameter "%s" does not support type "%s".', $this->parameter, \get_debug_type($this->value)),
            $previous
        );
    }

    /**
     * @param mixed[] $arguments
     */
    public static function for(\TypeError $error, array $arguments, Parameters $parameters): self|TypeMismatch
    {
        foreach ($arguments as $position => $value) {
            if (!$parameter = $parameters->get($position)) {
                continue;
            }

            if (!$parameter->accepts($value)) {
                throw new self($value, $parameter, $error);
            }
        }

        throw new TypeMismatch($error->getMessage(), $error);
    }

    public function value(): mixed
    {
        return $this->value;
    }

    public function parameter(): MirrorParameter
    {
        return $this->parameter;
    }
}
