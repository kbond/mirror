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

use Zenstruck\MirrorParameter;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @extends Iterator<MirrorParameter>
 *
 * @method MirrorParameter[] getIterator()
 */
final class Parameters extends Iterator
{
    public function __construct(\ReflectionFunctionAbstract $function)
    {
        parent::__construct(static function() use ($function) {
            foreach ($function->getParameters() as $parameter) {
                yield new MirrorParameter($parameter);
            }
        });
    }

    public function get(string|int $name): ?MirrorParameter
    {
        foreach ($this as $parameter) {
            if ($name === $parameter->name() || $name === $parameter->position()) {
                return $parameter;
            }
        }

        return null;
    }

    public function getOrFail(string|int $name): MirrorParameter
    {
        return $this->get($name) ?? throw new \ReflectionException(); // todo
    }

    public function required(): self
    {
        return $this->filter(fn(MirrorParameter $p) => $p->isRequired());
    }

    public function optional(): self
    {
        return $this->filter(fn(MirrorParameter $p) => $p->isOptional());
    }

    /**
     * @return string[]
     */
    public function names(): array
    {
        return \array_map(static fn(MirrorParameter $p) => $p->name(), $this->all());
    }
}
