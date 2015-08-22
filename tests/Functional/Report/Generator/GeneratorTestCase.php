<?php

namespace PhpBench\Tests\Functional\Report\Generator;

use PhpBench\Container;

abstract class GeneratorTestCase extends \PHPUnit_Framework_TestCase
{
    protected abstract function getGenerator();

    protected function getContainer()
    {
        static $container;

        if ($container) {
            return $container;
        }

        $container = new Container();
        $container->configure();
        $container->build();

        return $container;
    }

    protected function getConfig(array $config)
    {
        return array_merge(
            $this->getGenerator()->getDefaultConfig(),
            $config
        );
    }
}
