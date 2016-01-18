<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Storage;

use PhpBench\DependencyInjection\Container;

/**
 * Factory for storage drivers.
 *
 * Storage drivers must be configured at "compile" time in confguration.
 *
 * NOTE: We use a factory to offer better exception messages.
 */
class DriverFactory
{
    private $serviceIds = array();
    private $driverName;
    private $container;

    public function __construct(Container $container, $driverName)
    {
        $this->container = $container;
        $this->driverName = $driverName;
    }

    /**
     * Register the service ID of a named storage driver.
     *
     * @param string $name
     * @param string $serviceId
     */
    public function registerDriver($name, $serviceId)
    {
        if (isset($this->serviceIds[$name])) {
            throw new \RuntimeException(sprintf(
                'Storage driver with name "%s" has already been registered when trying to register serviceID "%s"',
                $name,
                $serviceId
            ));
        }

        $this->serviceIds[$name] = $serviceId;
    }

    /**
     * Return the configured storage driver.
     *
     * @return PhpBench\Storage\DriverInterface
     */
    public function getDriver()
    {
        if (null === $this->driverName) {
            throw new \InvalidArgumentException(sprintf(
                'You have not configured a storage driver. You will need to add a value to the "storage" key in your configuration file. Available drivers: "%s"',
                implode('", "', array_keys($this->serviceIds))
            ));
        }

        if (!isset($this->serviceIds[$this->driverName])) {
            throw new \InvalidArgumentException(sprintf(
                'Unknown storage driver: "%s", known drivers: "%s"',
                $this->driverName,
                implode('", "', array_keys($this->serviceIds))
            ));
        }

        return $this->container->get($this->serviceIds[$this->driverName]);
    }
}
