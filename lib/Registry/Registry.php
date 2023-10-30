<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace PhpBench\Registry;

use Psr\Container\ContainerInterface;

/**
 * Service and configuration registry.
 *
 * Lazily instantiates tagged services which are associated with a name
 * and stores configurations which are relevant to these services.
 *
 * ```
 * $reg->getService($config['renderer']);
 * $reg->render($something, $config);
 * ```
 *
 * @template T of object
 */
class Registry
{
    protected $serviceType;

    /**
     * @var array<string,T|null>
     */
    protected $services = [];

    /**
     * @var array<string, string>
     */
    private $serviceMap = [];

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var string
     */
    private $defaultService;

    public function __construct(
        $serviceType,
        ContainerInterface $container,
        $defaultService = null
    ) {
        $this->serviceType = $serviceType;
        $this->container = $container;
        $this->defaultService = $defaultService;
    }

    /**
     * Register a service ID with against the given name.
     *
     */
    public function registerService(string $name, string $serviceId): void
    {
        if (isset($this->serviceMap[$name])) {
            throw new \InvalidArgumentException(sprintf(
                '%s service "%s" is already registered',
                $this->serviceType,
                $name
            ));
        }

        $this->serviceMap[$name] = $serviceId;
        $this->services[$name] = null;
    }

    /**
     * Directly set a named service.
     *
     * @param T $object
     */
    public function setService(string $name, object $object): void
    {
        if (isset($this->services[$name])) {
            throw new \InvalidArgumentException(sprintf(
                '%s service "%s" already exists.',
                $this->serviceType,
                $name
            ));
        }

        $this->services[$name] = $object;
    }

    /**
     * Return the named service, lazily creating it from the container
     * if it has not yet been accessed.
     *
     *
     * @return T
     */
    public function getService(string $name = null): object
    {
        $name = $name ?: $this->defaultService;

        if (!$name) {
            throw new \RuntimeException(sprintf(
                'You must configure a default %s service, registered %s services: "%s"',
                $this->serviceType,
                $this->serviceType,
                implode('", "', array_keys($this->services))
            ));
        }

        if (isset($this->services[$name])) {
            return $this->services[$name];
        }

        $this->assertServiceExists($name);
        $this->services[$name] = $this->container->get($this->serviceMap[$name]);

        return $this->services[$name];
    }

    public function hasService(string $name): bool
    {
        return array_key_exists($name, $this->services);
    }

    public function getServiceNames(): array
    {
        return array_keys($this->services);
    }

    private function assertServiceExists(string $name): void
    {
        if (!array_key_exists($name, $this->services)) {
            throw new \InvalidArgumentException(sprintf(
                '%s service "%s" does not exist. Registered %s services: "%s"',
                $this->serviceType,
                $name,
                $this->serviceType,
                implode('", "', array_keys($this->services))
            ));
        }
    }
}
