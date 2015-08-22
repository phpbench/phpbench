<?php

namespace PhpBench\Tests\Functional\Report\Generator;

use PhpBench\Container;

abstract class GeneratorTestCase extends \PHPUnit_Framework_TestCase
{
    private $container;

    protected abstract function getGenerator();

    protected function getContainer()
    {
        if ($this->container) {
            return $this->container;
        }

        $this->container = new Container();
        $this->container->configure();
        $this->container->build();

        return $this->container;
    }

    protected function getConfig(array $config)
    {
        return array_merge(
            $this->getGenerator()->getDefaultConfig(),
            $config
        );
    }
}
