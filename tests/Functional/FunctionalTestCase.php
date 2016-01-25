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
use PhpBench\Serializer\XmlEncoder;

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

        $this->container = new Container();
        $this->container->configure();
        $this->container->build();

        return $this->container;
    }

    protected function getSuiteDocument($bench = 'FooBench.php')
    {
        $runner = $this->getContainer(false)->get('benchmark.runner');
        $context = new RunnerContext(__DIR__ . '/benchmarks/' . $bench, array(
            'executor' => array(
                'executor' => 'debug',
                'times' => array(10, 20),
                'spread' => array(0, 1),
            ),
        ));
        $suite = $runner->run($context);
        $encoder = new XmlEncoder();

        return $encoder->encode($suite);
    }

    protected function getMultipleSuiteDocument()
    {
        $encoder = new XmlEncoder();
        $context = new RunnerContext(__DIR__ . '/benchmarks/FooBench.php', array(
            'executor' => array(
                'executor' => 'debug',
                'times' => array(10, 20),
                'spread' => array(0, 1),
            ),
            'context_name' => 'foobar',
        ));
        $suite = $this->getContainer(false)->get('benchmark.runner')->run($context);
        $document1 = $encoder->encode($suite);

        $context = new RunnerContext(__DIR__ . '/benchmarks/FooBench.php', array(
            'executor' => array(
                'executor' => 'debug',
                'times' => array(20, 40),
                'spread' => array(1, 1),
            ),
            'context_name' => 'barfoo',
        ));
        $suite = $this->getContainer(false)->get('benchmark.runner')->run($context);
        $document2 = $encoder->encode($suite);

        $document1->appendSuiteDocument($document2, 'two');

        return $document1;
    }
}
