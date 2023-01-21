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
use Symfony\Component\VarExporter\ProxyHelper;
use Zenstruck\MirrorCallable;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template T of object
 */
final class VirtualProxyBuilder
{
    /** @var class-string<T> */
    private string $class;
    private string $name;
    private string $directory;
    private bool $debug = false;

    /** @var class-string[] */
    private array $interfaces = [];

    /** @var class-string[] */
    private array $traits = [];

    /** @var array<string,string> */
    private array $replacements = [];

    /**
     * @param class-string<T>|T $class
     */
    public function __construct(string|object $class)
    {
        if (!\class_exists(ProxyHelper::class)) {
            throw new \LogicException('symfony/var-exporter 6.2+ required to generate proxies. Install with "composer require symfony/var-exporter".');
        }

        $this->class = \is_string($class) ? $class : $class::class;
    }

    /**
     * @return $this
     */
    public function named(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return $this
     */
    public function in(string $directory): self
    {
        $this->directory = $directory;

        return $this;
    }

    /**
     * @return $this
     */
    public function debugMode(): self
    {
        $this->debug = true;

        return $this;
    }

    /**
     * @param class-string ...$interface
     *
     * @return $this
     */
    public function implementing(string ...$interface): self
    {
        $this->interfaces = \array_merge($this->interfaces, $interface);

        return $this;
    }

    /**
     * @param class-string ...$trait
     *
     * @return $this
     */
    public function using(string ...$trait): self
    {
        $this->traits = \array_merge($this->traits, $trait);

        return $this;
    }

    /**
     * @return $this
     */
    public function replace(string $search, string $replace): self
    {
        $this->replacements[$search] = $replace;

        return $this;
    }

    public function contents(): string
    {
        $contents = \sprintf('class %s%s', $this->name(), ProxyHelper::generateLazyProxy(new \ReflectionClass($this->class)));

        foreach ($this->replacements() as $search => $replace) {
            $contents = \str_replace($search, $replace, $contents);
        }

        return $contents;
    }

    /**
     * @return class-string<T&LazyObjectInterface>
     */
    public function class(): string
    {
        if (\class_exists($name = $this->name())) {
            return $name; // @phpstan-ignore-line
        }

        $filename = $this->filenameFor($name);

        if (!$this->debug && \file_exists($filename)) {
            require_once $filename;

            return $name; // @phpstan-ignore-line
        }

        if (!\is_dir($dir = \dirname($filename)) && !@\mkdir($dir, recursive: true) && !\is_dir($dir)) {
            throw new \RuntimeException(\sprintf('Directory "%s" was not created', $dir));
        }

        \file_put_contents($filename, "<?php\n\n".$this->contents());

        require_once $filename;

        return $name; // @phpstan-ignore-line
    }

    /**
     * @param T|callable():T $initializer
     *
     * @return T&LazyObjectInterface
     */
    public function create(callable|object $initializer): LazyObjectInterface
    {
        $initializer = \is_a($initializer, $this->class, true) ? static fn() => $initializer : $initializer; // @phpstan-ignore-line

        return $this->class()::createLazyProxy(MirrorCallable::closureFrom($initializer)); // @phpstan-ignore-line
    }

    private function name(): string
    {
        return $this->name ??= 'Proxy'.\sha1(\implode('', \array_merge(
            $this->interfaces,
            $this->traits
        )));
    }

    private function filenameFor(string $name): string
    {
        return \sprintf('%s/%s.php', $this->directory ?? \sys_get_temp_dir(), $name);
    }

    /**
     * @return \Traversable<string,string>
     */
    private function replacements(): \Traversable
    {
        foreach ($this->interfaces as $interface) {
            if (!\interface_exists($interface)) {
                throw new \LogicException(\sprintf('"%s" is not an interface.', $interface));
            }

            yield LazyObjectInterface::class => \sprintf('%s, \%s', LazyObjectInterface::class, $interface);
        }

        foreach ($this->traits as $trait) {
            if (!\trait_exists($trait)) {
                throw new \LogicException(\sprintf('"%s" is not a trait.', $trait));
            }

            yield LazyProxyTrait::class => \sprintf('%s, \%s', LazyProxyTrait::class, $trait);
        }

        foreach ($this->replacements as $search => $replace) {
            yield $search => $replace;
        }
    }
}
