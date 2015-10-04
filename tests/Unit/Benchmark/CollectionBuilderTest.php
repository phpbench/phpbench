<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\Benchmark;

use PhpBench\Benchmark\CollectionBuilder;

class CollectionBuilderTest extends \PHPUnit_Framework_TestCase
{
    private $builder;
    private $factory;
    private $benchmark1;
    private $benchmark2;

    public function setUp()
    {
        $this->factory = $this->prophesize('PhpBench\Benchmark\Metadata\Factory');
        $this->benchmark1 = $this->prophesize('PhpBench\Benchmark\Metadata\BenchmarkMetadata');
        $this->benchmark2 = $this->prophesize('PhpBench\Benchmark\Metadata\BenchmarkMetadata');
        $this->subject = $this->prophesize('PhpBench\Benchmark\Metadata\SubjectMetadata');
        $this->builder = new CollectionBuilder($this->factory->reveal());
    }

    /**
     * It should return a collection of all found bench benchmarks.
     * It should not instantiate abstract classes.
     */
    public function testBuildCollection()
    {
        $this->factory->getMetadataForFile(__DIR__ . '/buildertest/FooCaseBench.php')->willReturn($this->benchmark1->reveal());
        $this->factory->getMetadataForFile(__DIR__ . '/buildertest/FooCase2Bench.php')->willReturn($this->benchmark2->reveal());
        $this->factory->getMetadataForFile(__DIR__ . '/buildertest/AbstractBench.php')->willReturn(null);
        $collection = $this->builder->buildCollection(__DIR__ . '/buildertest');

        $benchmarks = $collection->getBenchmarks();
        $this->assertCount(2, $benchmarks);
        $this->assertContainsOnlyInstancesOf('PhpBench\Benchmark\Metadata\BenchmarkMetadata', $benchmarks);
    }

    /**
     * It should run a specified benchmark.
     * It should not run other benchmarks.
     */
    public function testSpecificBenchmark()
    {
        $this->factory->getMetadataForFile(__DIR__ . '/buildertestnested/MyBench.php')->willReturn($this->benchmark1->reveal());

        $collection = $this->builder->buildCollection(__DIR__ . '/buildertestnested/MyBench.php');
        $benchmarks = $collection->getBenchmarks();

        $this->assertCount(1, $benchmarks);
    }

    /**
     * It should skip benchmarks that have no subjects.
     */
    public function testNoSubjects()
    {
        $this->factory->getMetadataForFile(__DIR__ . '/buildertestnested/MyBench.php')->willReturn($this->benchmark1->reveal());
        $this->benchmark1->hasSubjects()->willReturn(false);

        $collection = $this->builder->buildCollection(__DIR__ . '/buildertestnested/MyBench.php');
        $benchmarks = $collection->getBenchmarks();

        $this->assertCount(0, $benchmarks);
    }
}
