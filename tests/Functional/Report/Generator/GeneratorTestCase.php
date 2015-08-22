<?php

/*
 * This file is part of the PHP Bench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\Functional\Report\Generator;

use PhpBench\Container;

abstract class GeneratorTestCase extends \PHPUnit_Framework_TestCase
{
    private $container;

    abstract protected function getGenerator();

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
