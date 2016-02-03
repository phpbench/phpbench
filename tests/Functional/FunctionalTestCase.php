<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\Functional;

use PhpBench\Benchmark\RunnerContext;
use PhpBench\DependencyInjection\Container;

class FunctionalTestCase extends \PHPUnit_Framework_TestCase
{
    private $container;

    /**
     * TODO: option to disable the cache is here because there is a bug
     * in the Runner/Builder which aggregates benchmarks on multiple runs.
     */
    protected function getContainer($cache = true)
    {
        if ($cache && $this->container) {
            return $this->container;
        }

        $this->container = new Container(array(
            'PhpBench\Extension\CoreExtension',
        ));
        $this->container->init();

        return $this->container;
    }

    protected function getSuite($bench = 'FooBench.php')
    {
        $runner = $this->getContainer(false)->get('benchmark.runner');
        $context = new RunnerContext(__DIR__ . '/benchmarks/' . $bench, array(
            'executor' => array(
                'executor' => 'debug',
                'times' => array(10, 20),
                'spread' => array(0, 1),
            ),
        ));

        return $runner->run($context);
    }

    protected function getMultipleSuites()
    {
        $context = new RunnerContext(__DIR__ . '/benchmarks/FooBench.php', array(
            'executor' => array(
                'executor' => 'debug',
                'times' => array(10, 20),
                'spread' => array(0, 1),
            ),
            'context_name' => 'foobar',
        ));
        $suite1 = $this->getContainer(false)->get('benchmark.runner')->run($context);

        $context = new RunnerContext(__DIR__ . '/benchmarks/FooBench.php', array(
            'executor' => array(
                'executor' => 'debug',
                'times' => array(20, 40),
                'spread' => array(1, 1),
            ),
            'context_name' => 'barfoo',
        ));
        $suite2 = $this->getContainer(false)->get('benchmark.runner')->run($context);

        return array($suite1, $suite2);
    }
}
