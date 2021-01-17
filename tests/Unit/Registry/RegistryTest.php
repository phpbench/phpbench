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

use InvalidArgumentException;
use PhpBench\DependencyInjection\Container;
use PhpBench\Registry\RegistrableInterface;
use PhpBench\Registry\Registry;
use PhpBench\Tests\TestCase;
use RuntimeException;

class RegistryTest extends TestCase
{
    protected $registry;
    protected $container;
    protected $service1;
    protected $service2;

    protected function setUp(): void
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
    public function testRegisterRetrieveService(): void
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
    public function testSetAndRetrieve(): void
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
    public function testDefaultGet(): void
    {
        $registry = new Registry(
            'test',
            $this->container->reveal(),
            'foo'
        );
        $service = new \stdClass();
        $registry->setService('foo', $service);
        $this->assertEquals($registry->getService(), $service);
    }

    /**
     * It should throw an exception if no argument given to get() and no default is defined.
     *
     */
    public function testDefaultGetNoDefault(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('You must configure a default test service, registered test services: "foo"');
        $registry = new Registry(
            'test',
            $this->container->reveal()
        );
        $service = new \stdClass();
        $registry->setService('foo', $service);
        $this->assertEquals($registry->getService(), $service);
    }

    /**
     * It should throw an exception if a service does not exist.
     *
     */
    public function testExceptionServivceNotExist(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('test service "bar" does not exist. Registered test services: "one"');
        $this->registry->setService('one', $this->service1->reveal());
        $this->registry->getService('bar');
    }

    /**
     * It should throw an exception if setting an already set service.
     *
     */
    public function testRegisterAlreadyRegistered(): void
    {
        $this->expectExceptionMessage('test service "one" already exists');
        $this->expectException(InvalidArgumentException::class);
        $this->registry->setService('one', $this->service1->reveal());
        $this->registry->setService('one', $this->service1->reveal());
    }
}
