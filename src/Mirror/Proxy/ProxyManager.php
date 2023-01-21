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

use Zenstruck\Mirror\ProxyBuilder;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @experimental
 */
final class ProxyManager
{
    public function __construct(private ?string $directory = null, private bool $debugMode = false)
    {
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $class
     *
     * @return GhostProxyBuilder<T>
     */
    public function ghostProxy(string $class): GhostProxyBuilder
    {
        return $this->configureBuilder(new GhostProxyBuilder($class)); // @phpstan-ignore-line
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $class
     *
     * @return VirtualProxyBuilder<T>
     */
    public function virtualProxy(string $class): VirtualProxyBuilder
    {
        return $this->configureBuilder(new VirtualProxyBuilder($class)); // @phpstan-ignore-line
    }

    /**
     * @template T of ProxyBuilder<object>
     *
     * @param T $builder
     *
     * @return T
     */
    private function configureBuilder(ProxyBuilder $builder): ProxyBuilder
    {
        if ($this->directory) {
            $builder->in($this->directory);
        }

        if ($this->debugMode) {
            $builder->debugMode();
        }

        return $builder;
    }
}
