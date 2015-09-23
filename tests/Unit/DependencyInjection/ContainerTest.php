<?php

/*
 * This file is part of the PHP Bench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\Unit\DependencyInjection;

use PhpBench\DependencyInjection\Container;

class ContainerTest extends \PHPUnit_Framework_TestCase
{
    private $container;

    public function setUp()
    {
        $this->container = new Container();
    }

    /**
     * It should register and get services
     * It should return the same instance on consecutive calls.
     */
    public function testRegisterSet()
    {
        $this->container->register('stdclass', function () {
            return new \stdClass();
        });

        $instance = $this->container->get('stdclass');
        $this->assertInstanceOf('stdClass', $instance);
        $this->assertSame($instance, $this->container->get('stdclass'));
    }

    /**
     * It should register and retrieve tagged services IDs with attributes.
     */
    public function testServiceIdTags()
    {
        $this->container->register('stdclass1', function () {
            return new \stdClass();
        }, array('tag1' => array('name' => 'hello')));
        $this->container->register('stdclass2', function () {
            return new \stdClass();
        }, array('tag1' => array('name' => 'hello')));

        $this->container->register('stdclass3', function () {
            return new \stdClass();
        }, array('tag2' => array('name' => 'goodbye')));

        $serviceIds = $this->container->getServiceIdsForTag('tag1');
        $this->assertNotNull($serviceIds);
        $this->assertCount(2, $serviceIds);

        foreach ($serviceIds as $attributes) {
            $this->assertEquals('hello', $attributes['name']);
        }
    }

    /**
     * It should merge parameters
     * It should say if a parameter exists
     * It should retrieve parameter values.
     */
    public function testParameters()
    {
        $this->container->mergeParameters(array('hello' => 'goodbye'));
        $this->container->mergeParameters(array('goodbye' => 'hello'));
        $this->assertTrue($this->container->hasParameter('hello'));
        $this->assertEquals('hello', $this->container->getParameter('goodbye'));
    }
}
