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
use PhpBench\Json\JsonDecoder;
use PhpBench\Registry\Config;
use PhpBench\Registry\ConfigurableRegistry;
use Prophecy\Argument;
use Seld\JsonLint\ParsingException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConfigurableRegistryTest extends RegistryTest
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->registry = new ConfigurableRegistry(
            'test',
            $this->container->reveal(),
            new JsonDecoder()
        );
    }

    /**
     * It should set configuration for a registered service
     * It should retrieve configurations.
     */
    public function testGetSetConfig()
    {
        $config = [
            'test' => 'service',
            'one' => 1,
            'two' => 2,
        ];

        $this->registry->setService('service', $this->service1->reveal());
        $this->service1->configure(Argument::type(OptionsResolver::class))->will(function ($args) {
            $args[0]->setDefaults(['one' => 'one', 'two' => 'two']);
        });

        $this->registry->setConfig('one', $config);

        $result = $this->registry->getConfig('one');
        $this->assertEquals($config, $result->getArrayCopy());
    }

    /**
     * It should resolve configs that exend other configs.
     */
    public function testResolveExtended()
    {
        $config1 = [
            'test' => 'service',
            'one' => 'two',
            'three' => 'four',
        ];
        $config2 = [
            'extends' => 'config1',
            'two' => 'three',
            'four' => 'five',
        ];

        $this->registry->setService('service', $this->service1->reveal());
        $this->service1->configure(Argument::type(OptionsResolver::class))->will(function ($args) {
            $args[0]->setDefaults([
                'test' => null,
                'one' => null,
                'two' => null,
                'three' => null,
                'four' => null,
            ]);
        });

        $this->registry->setConfig('config1', $config1);
        $this->registry->setConfig('config2', $config2);

        $result = $this->registry->getConfig('config2');
        $this->assertEquals([
            'test' => 'service',
            'one' => 'two',
            'three' => 'four',
            'two' => 'three',
            'four' => 'five',
        ], $result->getArrayCopy());
    }

    /**
     * It should throw an exception if a config extends a config from a different service.
     *
     */
    public function testExtendsDifferentServiceException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('test configuration for service "service2" cannot extend configuration for different service "service1"');
        $config1 = [
            'test' => 'service1',
        ];
        $config2 = [
            'test' => 'service2',
            'extends' => 'config1',
        ];

        $this->registry->setService('service1', $this->service1->reveal());
        $this->registry->setService('service2', $this->service2->reveal());

        $this->registry->setConfig('config1', $config1);
        $this->registry->setConfig('config2', $config2);

        $this->registry->getConfig('config2');
    }

    /**
     * If a JSON encoded string is passed to getConfig, then it should be processed.
     */
    public function testGetConfigJsonString()
    {
        $this->registry->setService('service', $this->service1->reveal());
        $this->service1->configure(Argument::type(OptionsResolver::class))->shouldBeCalled();

        $result = $this->registry->getConfig('{"test": "service"}');
        $this->assertEquals(new Config('test', [
            'test' => 'service',
        ]), $result);
    }

    /**
     * If a invalid JSON encoded string is passed to getConfig, then it should throw an exception.
     *
     */
    public function testGetConfigJsonStringInvalid()
    {
        $this->expectException(ParsingException::class);
        $this->registry->setService('service', $this->service1->reveal());

        $result = $this->registry->getConfig('{tes  t: se  rvice');
        $this->assertEquals(new Config('test', [
            'test' => 'service',
        ]), $result);
    }
}
