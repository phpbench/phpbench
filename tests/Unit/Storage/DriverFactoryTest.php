<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\Unit\Storage;

use PhpBench\Benchmark\Metadata\DriverInterface;
use PhpBench\DependencyInjection\Container;
use PhpBench\Storage\DriverFactory;

class DriverFactoryTest extends \PHPUnit_Framework_TestCase
{
    private $container;
    private $driver1;

    public function setUp()
    {
        $this->container = $this->prophesize(Container::class);
        $this->driver1 = $this->prophesize(DriverInterface::class);
    }

    /**
     * It should get the configured driver.
     */
    public function testGetDriver()
    {
        $factory = $this->createDriverFactory('test');
        $factory->registerDriver('test', 'test.id');
        $this->container->get('test.id')->willReturn($this->driver1->reveal());

        $driver = $factory->getDriver();
        $this->assertSame($this->driver1->reveal(), $driver);
    }

    /**
     * It should throw an exception if no driver is configured.
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage You have not configured a storage driver. You will need to add a value to the "storage" key in your configuration file. Available drivers: "test"
     */
    public function testNoDriver()
    {
        $factory = $this->createDriverFactory(null);
        $factory->registerDriver('test', 'test.id');
        $factory->getDriver();
    }

    /**
     * It should throw an exception if an unknown storage driver is configured.
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Unknown storage driver: "foobar", known drivers: "test"
     */
    public function testUnknownDriver()
    {
        $factory = $this->createDriverFactory('foobar');
        $factory->registerDriver('test', 'test.id');
        $factory->getDriver();
    }

    public function createDriverFactory($driverName)
    {
        return new DriverFactory($this->container->reveal(), $driverName);
    }
}
