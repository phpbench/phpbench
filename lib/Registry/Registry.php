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
 */
class Registry
{
    protected $serviceType;
    private $serviceMap = [];
    private $container;
    private $services = [];
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
     * @param string $name
     * @param string $serviceId
     */
    public function registerService($name, $serviceId)
    {
        if (isset($this->serviceMap[$name])) {
            throw new \InvalidArgumentException(sprintf(
                '%s service "%s" is already registered',
                $this->serviceType, $name
            ));
        }

        $this->serviceMap[$name] = $serviceId;
        $this->services[$name] = null;
    }

    /**
     * Directly set a named service.
     *
     * @param string $name
     * @param object $object
     */
    public function setService($name, $object)
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
     * @param string $name
     *
     * @return object
     */
    public function getService($name = null)
    {
        $name = $name ?: $this->defaultService;

        if (!$name) {
            throw new \RuntimeException(sprintf(
                'You must configure a default %s service, registered %s services: "%s"',
                $this->serviceType, $this->serviceType,
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

    private function assertServiceExists($name)
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
