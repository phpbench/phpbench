<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\Unit\Registry;

use JsonSchema\Validator;
use PhpBench\Json\JsonDecoder;
use PhpBench\Registry\Config;
use PhpBench\Registry\Registry;

class RegistryTest extends \PHPUnit_Framework_TestCase
{
    private $registry;
    private $container;
    private $validator;
    private $service1;
    private $service2;

    public function setUp()
    {
        $this->container = $this->prophesize('PhpBench\DependencyInjection\Container');
        $this->validator = new Validator();
        $this->service1 = $this->prophesize('PhpBench\Registry\RegistrableInterface');
        $this->service2 = $this->prophesize('PhpBench\Registry\RegistrableInterface');

        $this->registry = new Registry(
            'test',
            $this->container->reveal(),
            $this->validator,
            new JsonDecoder()
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
     * It should set configuration for a registered service
     * It should retrieve configurations.
     */
    public function testGetSetConfig()
    {
        $config = array(
            'test' => 'service',
            'one' => 1,
            'two' => 2,
        );

        $this->registry->setService('service', $this->service1->reveal());
        $this->service1->getDefaultConfig()->willReturn(array());
        $this->service1->getSchema()->willReturn(array());

        $this->registry->setConfig('one', $config);

        $result = $this->registry->getConfig('one');
        $this->assertEquals($config, $result->getArrayCopy());
    }

    /**
     * It should merge the default config.
     */
    public function testMergeDefaultConfig()
    {
        $config = array(
            'test' => 'service',
            'one' => array(
                'foo' => 'bar',
            ),
            'two' => 2,
        );

        $this->registry->setService('service', $this->service1->reveal());
        $this->service1->getDefaultConfig()->willReturn(array(
            'hello' => 'goodbye',
            'one' => array(
                'bar' => 'foo',
            ),
        ));
        $this->service1->getSchema()->willReturn(array());

        $this->registry->setConfig('one', $config);
        $result = $this->registry->getConfig('one');

        $this->assertEquals(array(
            'test' => 'service',
            'one' => array(
                'foo' => 'bar',
            ),
            'two' => 2,
            'hello' => 'goodbye',
        ), $result->getArrayCopy());
    }

    /**
     * It should resolve configs that exend other configs.
     */
    public function testResolveExtended()
    {
        $config1 = array(
            'test' => 'service',
            'one' => 'two',
            'three' => 'four',
        );
        $config2 = array(
            'extends' => 'config1',
            'two' => 'three',
            'four' => 'five',
        );

        $this->registry->setService('service', $this->service1->reveal());
        $this->service1->getDefaultConfig()->willReturn(array());
        $this->service1->getSchema()->willReturn(array());

        $this->registry->setConfig('config1', $config1);
        $this->registry->setConfig('config2', $config2);

        $result = $this->registry->getConfig('config2');
        $this->assertEquals(array(
            'test' => 'service',
            'one' => 'two',
            'three' => 'four',
            'two' => 'three',
            'four' => 'five',
        ), $result->getarraycopy());
    }

    /**
     * It should throw an exception if a config extends a config from a different service.
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage test configuration for service "service2" cannot extend configuration for different service "service1"
     */
    public function testExtendsDifferentServiceException()
    {
        $config1 = array(
            'test' => 'service1',
            'one' => 'two',
        );
        $config2 = array(
            'test' => 'service2',
            'extends' => 'config1',
        );

        $this->registry->setService('service1', $this->service1->reveal());
        $this->registry->setService('service2', $this->service2->reveal());

        $this->service1->getDefaultConfig()->willReturn(array());
        $this->service1->getSchema()->willReturn(array());
        $this->service2->getDefaultConfig()->willReturn(array());
        $this->service2->getSchema()->willReturn(array());

        $this->registry->setConfig('config1', $config1);
        $this->registry->setConfig('config2', $config2);

        $this->registry->getConfig('config2');
    }

    /**
     * It should validate configuration.
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid JSON
     */
    public function testValidate()
    {
        $config = array(
            'test' => 'service',
            'one' => 1,
            'two' => 2,
        );

        $this->registry->setService('service', $this->service1->reveal());
        $this->service1->getDefaultConfig()->willReturn(array());
        $this->service1->getSchema()->willReturn(array(
            'type' => 'object',
            'additionalProperties' => false,
            'properties' => array(
                'title' => array(
                    'type' => 'string',
                ),
            ),
        ));

        $this->registry->setConfig('one', $config);

        $result = $this->registry->getConfig('one');
        $this->assertEquals($config, $result->getArrayCopy());
    }

    /**
     * It should throw an exception if the registrable class does not return an array as a schema.
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage must return the JSON schema as an array
     */
    public function testJsonSchemaAsArrayException()
    {
        $this->registry->setService('service', $this->service1->reveal());
        $this->service1->getDefaultConfig()->willReturn(array());
        $this->service1->getSchema()->willReturn(new \stdClass());
        $this->registry->setConfig('one', array(
            'test' => 'service',
        ));

        $this->registry->getConfig('one');
    }

    /**
     * If a JSON encoded string is passed to getConfig, then it should be processed.
     */
    public function testGetConfigJsonString()
    {
        $this->registry->setService('service', $this->service1->reveal());
        $this->service1->getDefaultConfig()->willReturn(array());
        $this->service1->getSchema()->willReturn(array());

        $result = $this->registry->getConfig('{"test": "service"}');
        $this->assertEquals(new Config('test', array(
            'test' => 'service',
        )), $result);
    }

    /**
     * If a invalid JSON encoded string is passed to getConfig, then it should throw an exception.
     *
     * @expectedException Seld\JsonLint\ParsingException
     */
    public function testGetConfigJsonStringInvalid()
    {
        $this->registry->setService('service', $this->service1->reveal());
        $this->service1->getDefaultConfig()->willReturn(array());
        $this->service1->getSchema()->willReturn(array());

        $result = $this->registry->getConfig('{test": service}');
        $this->assertEquals(new Config('test', array(
            'test' => 'service',
        )), $result);
    }
}
