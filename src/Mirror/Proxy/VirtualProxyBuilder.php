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

use Symfony\Component\VarExporter\LazyObjectInterface;
use Symfony\Component\VarExporter\LazyProxyTrait;
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
final class VirtualProxyBuilder extends ProxyBuilder
{
    /**
     * @see LazyProxyTrait::createLazyProxy()
     *
     * @param T|callable():T $initializer
     *
     * @return T&LazyObjectInterface
     */
    public function create(callable|object $initializer): LazyObjectInterface
    {
        $initializer = \is_a($initializer, $this->className(), true) ? static fn() => $initializer : $initializer; // @phpstan-ignore-line

        return $this->class()::createLazyProxy(MirrorCallable::closureFrom($initializer)); // @phpstan-ignore-line
    }

    protected static function proxyTrait(): string
    {
        return LazyProxyTrait::class;
    }

    protected static function proxyMethod(): string
    {
        return 'generateLazyProxy';
    }
}
