<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\Functional\Report\Generator;

use PhpBench\Benchmark\RunnerContext;
use PhpBench\Benchmark\SuiteDocument;
use PhpBench\DependencyInjection\Container;
use PhpBench\Dom\Document;
use PhpBench\Registry\Config;

abstract class GeneratorTestCase extends \PHPUnit_Framework_TestCase
{
    private $container;

    abstract protected function getGenerator();

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

    protected function getConfig(array $config)
    {
        return new Config(array_merge(
            $this->getGenerator()->getDefaultConfig(),
            $config
        ));
    }

    protected function generate(SuiteDocument $document, $config)
    {
        return $this->getGenerator()->generate(
            $document,
            $this->getConfig($config)
        );
    }

    protected function assertXPathEvaluation(Document $dom, $expected, $expr)
    {
        $result = $dom->evaluate($expr);
        $this->assertEquals($expected, $result, $expr);
    }

    protected function getSuiteDocument()
    {
        $runner = $this->getContainer()->get('benchmark.runner');
        $context = new RunnerContext(__DIR__ . '/benchmarks/FooBench.php', array(
            'executor' => array(
                'executor' => 'debug',
                'times' => array(10, 20),
                'spread' => array(0, 1),
            ),
        ));
        $dom = $runner->run($context);

        return $dom;
    }

    protected function getMultipleSuiteDocument()
    {
        $context = new RunnerContext(__DIR__ . '/benchmarks/FooBench.php', array(
            'executor' => array(
                'executor' => 'debug',
                'times' => array(10, 20),
                'spread' => array(0, 1),
            ),
            'context_name' => 'foobar',
        ));
        $document1 = $this->getContainer(false)->get('benchmark.runner')->run($context);
        $context = new RunnerContext(__DIR__ . '/benchmarks/FooBench.php', array(
            'executor' => array(
                'executor' => 'debug',
                'times' => array(20, 40),
                'spread' => array(1, 1),
            ),
            'context_name' => 'barfoo',
        ));
        $document2 = $this->getContainer(false)->get('benchmark.runner')->run($context);

        $document1->appendSuiteDocument($document2, 'two');

        return $document1;
    }
}
