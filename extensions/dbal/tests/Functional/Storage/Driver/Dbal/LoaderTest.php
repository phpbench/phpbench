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

namespace PhpBench\Extensions\Dbal\Tests\Functional\Storage\Driver\Dbal;

use PhpBench\Expression\Constraint\Comparison;
use PhpBench\Extensions\Dbal\Storage\Driver\Dbal\Loader;
use PhpBench\Extensions\Dbal\Storage\Driver\Dbal\Persister;
use PhpBench\Extensions\Dbal\Storage\Driver\Dbal\Repository;
use PhpBench\Extensions\Dbal\Storage\Driver\Dbal\Visitor\SqlVisitor;
use PhpBench\Extensions\Dbal\Storage\Driver\Dbal\Visitor\TokenValueVisitor;
use PhpBench\Extensions\Dbal\Tests\Functional\DbalTestCase;
use PhpBench\Model\Result\MemoryResult;
use PhpBench\Model\Result\TimeResult;
use PhpBench\Model\SuiteCollection;
use PhpBench\Tests\Util\TestUtil;

class LoaderTest extends DbalTestCase
{
    private $persister;
    private $manager;
    private $loader;
    private $visitor;

    public function setUp()
    {
        $this->manager = $this->getManager();

        // instantiate persister
        $this->persister = new Persister($this->manager);

        $this->visitor = new SqlVisitor();
        $tokenVisitor = $this->prophesize(TokenValueVisitor::class);
        $repository = new Repository($this->manager, $tokenVisitor->reveal(), $this->visitor);
        $this->loader = new Loader($repository);
    }

    public function tearDown()
    {
        $this->cleanWorkspace();
    }

    /**
     * The loader should load the collection from an Dbal database.
     */
    public function testLoader()
    {
        $suiteCollection = new SuiteCollection([
            TestUtil::createSuite([
                'uuid' => '1',
                'subjects' => ['benchOne', 'benchTwo'],
                'benchmarks' => ['BenchOne', 'BenchTwo'],
                'groups' => ['one', 'two'],
                'output_time_unit' => 'milliseconds',
                'output_time_precision' => 7,
                'output_mode' => 'throughput',
                'revs' => 5,
                'warmup' => 2,
                'sleep' => 9,
                'parameters' => ['foo' => 'bar', 'bar' => [1, 2]],
                'env' => [
                    'system' => [
                        'os' => 'linux',
                        'memory' => 8096,
                        'distribution' => 'debian',
                    ],
                    'vcs' => [
                        'system' => 'git',
                        'branch' => 'foo',
                    ],
                ],
            ]),
            TestUtil::createSuite([
                'uuid' => '2',
            ]),
        ]);
        $this->persister->persist($suiteCollection);

        $suiteCollection = $this->loader->load(new Comparison('$in', 'run', [1, 2]));

        $suites = $suiteCollection->getSuites();
        $this->assertCount(2, $suites);

        $suite = current($suites);

        // assert env information
        $envInformations = $suite->getEnvInformations();
        $this->assertCount(2, $envInformations);
        $this->assertArrayHasKey('system', $envInformations);
        $this->assertEquals('system', $envInformations['system']->getName());
        $this->assertEquals([
            'os' => 'linux',
            'memory' => 8096,
            'distribution' => 'debian',
        ], iterator_to_array($envInformations['system']));
        $this->assertArrayHasKey('vcs', $envInformations);
        $this->assertEquals('vcs', $envInformations['vcs']->getName());

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
        $this->assertEquals(['one', 'two'], $subject->getGroups());
        $this->assertEquals(9, $subject->getSleep());
        $this->assertEquals('milliseconds', $subject->getOutputTimeUnit());
        $this->assertEquals(7, $subject->getOutputTimePrecision());
        $this->assertEquals('throughput', $subject->getOutputMode());

        // assert variants
        $variants = $subject->getVariants();
        $this->assertCount(1, $variants);
        $variant = current($variants);
        $parameters = $variant->getParameterSet();
        $this->assertEquals([
            'foo' => 'bar',
            'bar' => [1, 2],
        ], $parameters->getArrayCopy());
        $this->assertEquals(5, $variant->getRevolutions());
        $this->assertEquals(2, $variant->getWarmup());

        $variant = current($variants);

        // assert iterations
        $iterations = $variant->getIterations();
        $this->assertCount(2, $iterations);
        $iteration = current($iterations);
        $this->assertEquals(10, $iteration->getResult(TimeResult::class)->getNet());
        $this->assertEquals(200, $iteration->getResult(MemoryResult::class)->getPeak());
    }

    /**
     * All fields generated by the constraint visitor should be valid.
     */
    public function testVisitorFields()
    {
        $fieldNames = $this->visitor->getValidFieldNames();

        foreach ($fieldNames as $fieldName) {
            // "param" is special.
            if ($fieldName === 'param') {
                continue;
            }

            $constraint = new Comparison('$eq', $fieldName, 'foo');
            $this->loader->load($constraint);
        }
        $this->addToAssertionCount(1);
    }

    /**
     * It should filer by groups.
     */
    public function testFilterGroups()
    {
        $suiteCollection = new SuiteCollection([
            TestUtil::createSuite([
                'uuid' => '1',
                'benchmark' => ['benchOne'],
                'subjects' => ['benchOne'],
                'groups' => ['one', 'two'],
            ]),
            TestUtil::createSuite([
                'uuid' => '2',
                'benchmark' => ['benchOne'],
                'subjects' => ['benchTwo', 'benchThree'],
                'groups' => ['foobar'],
            ]),
        ]);

        $this->persister->persist($suiteCollection);

        $suiteCollection = $this->loader->load(new Comparison('$eq', 'group', 'one'));
        $suite = $suiteCollection->getIterator()->current();
        $this->assertNotNull($suite);
        $this->assertCount(1, $suite->getSubjects());

        $suiteCollection = $this->loader->load(new Comparison('$eq', 'group', 'two'));
        $suite = $suiteCollection->getIterator()->current();
        $this->assertCount(1, $suite->getSubjects());

        $suiteCollection = $this->loader->load(new Comparison('$eq', 'group', 'foobar'));
        $suite = $suiteCollection->getIterator()->current();
        $this->assertCount(2, $suite->getSubjects());
    }
}
