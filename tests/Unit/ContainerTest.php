<?php

namespace PhpBench\Tests\Unit;

use PhpBench\Container;

class ContainerTest extends \PHPUnit_Framework_TestCase
{
    private $container;

    public function setUp()
    {
        $this->container = new Container();
    }

    /**
     * It should register and get services
     * It should return the same instance on consecutive calls
     */
    public function testRegisterSet()
    {
        $this->container->register('stdclass', function () {
            return new \stdClass;
        });

        $instance = $this->container->get('stdclass');
        $this->assertInstanceOf('stdClass', $instance);
        $this->assertSame($instance, $this->container->get('stdclass'));
    }

    /**
     * It should register and retrieve tagged services IDs with attributes
     */
    public function testServiceIdTags()
    {
        $this->container->register('stdclass1', function () {
            return new \stdClass;
        }, array('tag1' => array('name' => 'hello')));
        $this->container->register('stdclass2', function () {
            return new \stdClass;
        }, array('tag1' => array('name' => 'hello')));

        $this->container->register('stdclass3', function () {
            return new \stdClass;
        }, array('tag2' => array('name' => 'goodbye')));

        $serviceIds = $this->container->getServiceIdsForTag('tag1');
        $this->assertNotNull($serviceIds);
        $this->assertCount(2, $serviceIds);

        foreach ($serviceIds as $attributes) {
            $this->assertEquals('hello', $attributes['name']);
        }
    }

}
