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

namespace PhpBench\Tests\Unit\Registry;

use PhpBench\DependencyInjection\Container;
use PhpBench\Registry\RegistrableInterface;
use PhpBench\Registry\Registry;
use PHPUnit\Framework\TestCase;

class RegistryTest extends TestCase
{
    protected $registry;
    protected $container;
    protected $service1;
    protected $service2;

    public function setUp()
    {
        $this->container = $this->prophesize(Container::class);
        $this->service1 = $this->prophesize(RegistrableInterface::class);
        $this->service2 = $this->prophesize(RegistrableInterface::class);

        $this->registry = new Registry(
            'test',
            $this->container->reveal()
        );
    }

    /**
     * It should register a service
     * It should retrieve a service
     * It should retrieve a service from the container only once.
     */
    public function testRegisterRetrieveService()
    {
        $this->container->get('foo')
            ->willReturn(
                $this->service1->reveal()
            )
            ->shouldBeCalledTimes(1);

        $this->registry->registerService('bar', 'foo');
        $this->registry->getService('bar');
        $this->registry->getService('bar');
    }

    /**
     * It should set and retrieve services.
     */
    public function testSetAndRetrieve()
    {
        $this->registry->setService('one', $this->service1->reveal());
        $service = $this->registry->getService('one');
        $this->assertSame(
            $this->service1->reveal(),
            $service
        );
    }

    /**
     * It should return a default service if no argument is given to get().
     */
    public function testDefaultGet()
    {
        $registry = new Registry(
            'test',
            $this->container->reveal(),
            'foo'
        );
        $registry->setService('foo', 'bar');
        $this->assertEquals($registry->getService(), 'bar');
    }

    /**
     * It should throw an exception if no argument given to get() and no default is defined.
     *
     * @expectedException RuntimeException
     * @expectedExceptionMessage You must configure a default test service, registered test services: "foo"
     */
    public function testDefaultGetNoDefault()
    {
        $registry = new Registry(
            'test',
            $this->container->reveal()
        );
        $registry->setService('foo', 'bar');
        $this->assertEquals($registry->getService(), 'bar');
    }

    /**
     * It should throw an exception if a service does not exist.
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage test service "bar" does not exist. Registered test services: "one"
     */
    public function testExceptionServivceNotExist()
    {
        $this->registry->setService('one', $this->service1->reveal());
        $this->registry->getService('bar');
    }

    /**
     * It should throw an exception if setting an already set service.
     *
     * @expectedException InvalidArgumentException
     * @expectedException test service "bar" is already registered.
     */
    public function testRegisterAlreadyRegistered()
    {
        $this->registry->setService('one', $this->service1->reveal());
        $this->registry->setService('one', $this->service1->reveal());
    }
}
