<?php

/*
 * This file is part of the zenstruck/mirror package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Mirror\Proxy;

use Symfony\Component\VarExporter\LazyGhostTrait;
use Symfony\Component\VarExporter\LazyObjectInterface;
use Zenstruck\Mirror\ProxyBuilder;
use Zenstruck\MirrorCallable;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @experimental
 *
 * @template T of object
 * @extends ProxyBuilder<T>
 */
final class GhostProxyBuilder extends ProxyBuilder
{
    /**
     * @see LazyGhostTrait::createLazyGhost()
     *
     * @param array<string,callable(T,string=,?string=):mixed>|callable(T,string=,?string=):mixed $initializer
     *
     * @return T&LazyObjectInterface
     */
    public function create(callable|array $initializer): LazyObjectInterface
    {
        return $this->class()::createLazyGhost(\is_callable($initializer) ? MirrorCallable::closureFrom($initializer) : $initializer); // @phpstan-ignore-line
    }

    protected static function proxyTrait(): string
    {
        return LazyGhostTrait::class;
    }

    protected static function proxyMethod(): string
    {
        return 'generateLazyGhost';
    }
}
