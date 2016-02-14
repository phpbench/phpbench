<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\Functional\Storage\Driver\Sqlite;

use PhpBench\Expression\Constraint\Comparison;
use PhpBench\Extensions\Sqlite\Storage\Driver\Sqlite\ConnectionManager;
use PhpBench\Extensions\Sqlite\Storage\Driver\Sqlite\Loader;
use PhpBench\Extensions\Sqlite\Storage\Driver\Sqlite\Persister;
use PhpBench\Extensions\Sqlite\Storage\Driver\Sqlite\Repository;
use PhpBench\Model\SuiteCollection;
use PhpBench\Tests\Functional\FunctionalTestCase;
use PhpBench\Tests\Util\TestUtil;

class LoaderTest extends FunctionalTestCase
{
    private $persister;
    private $manager;

    public function setUp()
    {
        $this->initWorkspace();
        $this->manager = new ConnectionManager($this->getWorkspacePath() . '/test.sqlite');

        // instantiate persister
        $this->persister = new Persister($this->manager);

        $repository = new Repository($this->manager);
        $this->loader = new Loader($repository);
    }

    public function tearDown()
    {
        $this->cleanWorkspace();
    }

    /**
     * The loader should load the collection from an Sqlite database.
     */
    public function testLoader()
    {
        $suiteCollection = new SuiteCollection(array(
            TestUtil::createSuite(array(
                'subjects' => array('benchOne', 'benchTwo'),
                'benchmarks' => array('BenchOne', 'BenchTwo'),
                'groups' => array('one', 'two'),
                'output_time_unit' => 'milliseconds',
                'output_mode' => 'throughput',
                'revs' => 5,
                'warmup' => 2,
                'sleep' => 9,
                'parameters' => array('foo' => 'bar', 'bar' => array(1, 2)),
                'env' => array(
                    'system' => array(
                        'os' => 'linux',
                        'memory' => 8096,
                        'distribution' => 'debian',
                    ),
                    'vcs' => array(
                        'system' => 'git',
                        'branch' => 'foo',
                    ),
                ),
            )),
            TestUtil::createSuite(),
        ));

        $this->persister->persist($suiteCollection);
        $suiteCollection = $this->loader->load(new Comparison('$in', 'run', array(1, 2)));

        $suites = $suiteCollection->getSuites();
        $this->assertCount(2, $suites);

        $suite = current($suites);

        // assert env information
        $envInformations = $suite->getEnvInformations();
        $this->assertCount(2, $envInformations);
        $this->assertEquals('system', $envInformations[0]->getName());
        $this->assertEquals(array(
            'os' => 'linux',
            'memory' => 8096,
            'distribution' => 'debian',
        ), iterator_to_array($envInformations[0]));
        $this->assertEquals('vcs', $envInformations[1]->getName());

        // assert benchmarks
        $benchmarks = $suite->getBenchmarks();
        $this->assertCount(2, $benchmarks);
        $this->assertEquals('BenchOne', $benchmarks[0]->getClass());
        $this->assertEquals('BenchTwo', $benchmarks[1]->getClass());

        // assert subjects
        $subjects = array_values($benchmarks[0]->getSubjects());
        $this->assertCount(2, $subjects);
        $this->assertEquals('benchOne', $subjects[0]->getName());
        $this->assertEquals('benchTwo', $subjects[1]->getName());
        $subject = $subjects[0];
        $this->assertEquals(array('one', 'two'), $subject->getGroups());
        $this->assertEquals(9, $subject->getSleep());
        $this->assertEquals('milliseconds', $subject->getOutputTimeUnit());
        $this->assertEquals('throughput', $subject->getOutputMode());

        // assert variants
        $variants = $subject->getVariants();
        $this->assertCount(1, $variants);
        $variant = current($variants);
        $parameters = $variant->getParameterSet();
        $this->assertEquals(array(
            'foo' => 'bar',
            'bar' => array(1, 2),
        ), $parameters->getArrayCopy());
        $this->assertEquals(5, $variant->getRevolutions());
        $this->assertEquals(2, $variant->getWarmup());

        $variant = current($variants);

        // assert iterations
        $iterations = $variant->getIterations();
        $this->assertCount(2, $iterations);
        $iteration = current($iterations);
        $this->assertEquals(10, $iteration->getTime());
        $this->assertEquals(200, $iteration->getMemory());
    }
}
