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

namespace PhpBench\Tests\Benchmark;

use PhpBench\Benchmark\BenchmarkFinder;
use PhpBench\Benchmark\Metadata\BenchmarkMetadata;
use PhpBench\Benchmark\Metadata\MetadataFactory;
use PhpBench\Model\Subject;
use PhpBench\Tests\TestCase;

class BenchmarkFinderTest extends TestCase
{
    private $finder;
    private $factory;
    private $benchmark1;
    private $benchmark2;

    protected function setUp(): void
    {
        $this->factory = $this->prophesize(MetadataFactory::class);
        $this->benchmark1 = $this->prophesize(BenchmarkMetadata::class);
        $this->benchmark2 = $this->prophesize(BenchmarkMetadata::class);
        $this->subject = $this->prophesize(Subject::class);
        $this->finder = new BenchmarkFinder($this->factory->reveal());
    }

    /**
     * It should return a collection of all found bench benchmarks.
     * It should not instantiate abstract classes.
     */
    public function testBuildCollection()
    {
        $this->factory->getMetadataForFile(__DIR__ . '/findertest/FooCaseBench.php')->willReturn($this->benchmark1->reveal());
        $this->factory->getMetadataForFile(__DIR__ . '/findertest/FooCase2Bench.php')->willReturn($this->benchmark2->reveal());
        $this->factory->getMetadataForFile(__DIR__ . '/findertest/AbstractBench.php')->willReturn(null);
        $this->benchmark1->hasSubjects()->willReturn(true);
        $this->benchmark2->hasSubjects()->willReturn(true);
        $benchmarks = $this->finder->findBenchmarks([__DIR__ . '/findertest']);

        $this->assertCount(2, $benchmarks);
    }

    /**
     * It should return a collection of all found bench benchmarks.
     * It should not instantiate abstract classes.
     */
    public function testFromMultiplePaths()
    {
        $this->factory->getMetadataForFile(
            __DIR__ . '/findertest/FooCaseBench.php'
        )->willReturn($this->benchmark1->reveal());
        $this->factory->getMetadataForFile(
            __DIR__ . '/findertest/FooCase2Bench.php'
        )->willReturn($this->benchmark2->reveal());

        $this->benchmark1->hasSubjects()->willReturn(true);
        $this->benchmark2->hasSubjects()->willReturn(true);

        $benchmarks = $this->finder->findBenchmarks([
            __DIR__ . '/findertest/FooCaseBench.php',
            __DIR__ . '/findertest/FooCase2Bench.php'
        ]);

        $this->assertCount(2, iterator_to_array($benchmarks));
    }

    /**
     * It should run a specified benchmark.
     * It should not run other benchmarks.
     */
    public function testSpecificBenchmark()
    {
        $this->factory->getMetadataForFile(__DIR__ . '/findertestnested/MyBench.php')->willReturn($this->benchmark1->reveal());
        $this->benchmark1->hasSubjects()->willReturn(true);

        $benchmarks = $this->finder->findBenchmarks([__DIR__ . '/findertestnested/MyBench.php']);

        $this->assertCount(1, $benchmarks);
    }

    /**
     * It should skip benchmarks that have no subjects.
     */
    public function testNoSubjects()
    {
        $this->factory->getMetadataForFile(__DIR__ . '/findertestnested/MyBench.php')->willReturn($this->benchmark1->reveal());
        $this->benchmark1->hasSubjects()->willReturn(false);

        $benchmarks = $this->finder->findBenchmarks([__DIR__ . '/findertestnested/MyBench.php']);

        $this->assertCount(0, $benchmarks);
    }
}
