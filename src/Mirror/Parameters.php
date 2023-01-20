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

use Zenstruck\Mirror\Exception\NoSuchParameter;
use Zenstruck\Mirror\Internal\AttributesMirrorIterator;
use Zenstruck\MirrorCallable;
use Zenstruck\MirrorParameter;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @extends AttributesMirrorIterator<MirrorParameter>
 *
 * @method MirrorParameter[]    getIterator()
 * @method MirrorParameter|null first()
 */
final class Parameters extends AttributesMirrorIterator
{
    public function __construct(private MirrorCallable $function)
    {
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

    /**
     * @throws NoSuchParameter
     */
    public function getOrFail(string|int $name): MirrorParameter
    {
        return $this->get($name) ?? throw new NoSuchParameter(\sprintf('Parameter "%s" does not exist for function "%s".', $name, $this->function));
    }

    public function required(): self
    {
        return $this->filter(fn(MirrorParameter $p) => $p->isRequired());
    }

    public function optional(): self
    {
        return $this->filter(fn(MirrorParameter $p) => $p->isOptional());
    }

    protected function iterator(): \Traversable
    {
        foreach ($this->function->reflector()->getParameters() as $parameter) {
            yield new MirrorParameter($parameter);
        }
    }
}
