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

use Generator;
use PhpBench\Benchmark\BenchmarkFinder;
use PhpBench\Benchmark\Metadata\BenchmarkMetadata;
use PhpBench\Benchmark\Metadata\MetadataFactory;
use PhpBench\Model\Subject;
use PhpBench\Tests\TestCase;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;

class BenchmarkFinderTest extends TestCase
{
    private $finder;
    private $factory;
    private $benchmark1;
    private $benchmark2;
    private $subject;
    private $logger;

    protected function setUp(): void
    {
        $this->factory = $this->prophesize(MetadataFactory::class);
        $this->benchmark1 = $this->prophesize(BenchmarkMetadata::class);
        $this->benchmark2 = $this->prophesize(BenchmarkMetadata::class);
        $this->subject = $this->prophesize(Subject::class);
        $this->logger = $this->prophesize(LoggerInterface::class);
    }

    private function createFinder(string $benchPattern = null): BenchmarkFinder
    {
        return new BenchmarkFinder($this->factory->reveal(), __DIR__ . '/', $this->logger->reveal(), $benchPattern);
    }

    /**
     * It should return a collection of all found bench benchmarks.
     * It should not instantiate abstract classes.
     */
    public function testBuildCollection(): void
    {
        $this->factory->getMetadataForFile(__DIR__ . '/findertest/FooCaseBench.php')->willReturn($this->benchmark1->reveal());
        $this->factory->getMetadataForFile(__DIR__ . '/findertest/FooCase2Bench.php')->willReturn($this->benchmark2->reveal());
        $this->factory->getMetadataForFile(__DIR__ . '/findertest/AbstractBench.php')->willReturn(null);
        $this->factory->getMetadataForFile(__DIR__ . '/findertest/ExampleWithNoBenchSuffix.php')->willReturn(null);
        $this->benchmark1->hasSubjects()->willReturn(true);
        $this->benchmark2->hasSubjects()->willReturn(true);
        $benchmarks = $this->createFinder()->findBenchmarks([__DIR__ . '/findertest']);

        $this->assertBenchmarkCount(2, $benchmarks);
        $this->logger->warning(Argument::containingString('but it does not end with'))->shouldHaveBeenCalledTimes(1);
    }

    public function testBuildCollectionByGlob(): void
    {
        $this->factory->getMetadataForFile(__DIR__ . '/findertest/FooCaseBench.php')->willReturn($this->benchmark1->reveal());
        $this->factory->getMetadataForFile(__DIR__ . '/findertest/FooCase2Bench.php')->willReturn($this->benchmark2->reveal());
        $this->factory->getMetadataForFile(__DIR__ . '/findertest/AbstractBench.php')->willReturn(null);
        $this->benchmark1->hasSubjects()->willReturn(true);
        $this->benchmark2->hasSubjects()->willReturn(true);
        $benchmarks = $this->createFinder('*Bench.php')->findBenchmarks([__DIR__ . '/findertest']);

        $this->assertBenchmarkCount(2, $benchmarks);
    }

    /**
     * It should return a collection of all found bench benchmarks.
     * It should not instantiate abstract classes.
     */
    public function testFromMultiplePaths(): void
    {
        $this->factory->getMetadataForFile(
            __DIR__ . '/findertest/FooCaseBench.php'
        )->willReturn($this->benchmark1->reveal());
        $this->factory->getMetadataForFile(
            __DIR__ . '/findertest/FooCase2Bench.php'
        )->willReturn($this->benchmark2->reveal());

        $this->benchmark1->hasSubjects()->willReturn(true);
        $this->benchmark2->hasSubjects()->willReturn(true);

        $benchmarks = $this->createFinder()->findBenchmarks([
            __DIR__ . '/findertest/FooCaseBench.php',
            __DIR__ . '/findertest/FooCase2Bench.php'
        ]);

        $this->assertBenchmarkCount(2, $benchmarks);
    }

    /**
     * It should run a specified benchmark.
     * It should not run other benchmarks.
     */
    public function testSpecificBenchmark(): void
    {
        $this->factory->getMetadataForFile(__DIR__ . '/findertestnested/MyBench.php')->willReturn($this->benchmark1->reveal());
        $this->benchmark1->hasSubjects()->willReturn(true);

        $benchmarks = $this->createFinder()->findBenchmarks([__DIR__ . '/findertestnested/MyBench.php']);

        $this->assertBenchmarkCount(1, $benchmarks);
    }

    public function testNoPatternSpecifiedWithNonBenchSuffixedFile(): void
    {
        $this->factory->getMetadataForFile(__DIR__ . '/findertestnobenchsuffix/NoBenchSuffix.php')->willReturn($this->benchmark1->reveal());
        $this->benchmark1->hasSubjects()->willReturn(true);

        $benchmarks = $this->createFinder()->findBenchmarks([__DIR__ . '/findertestnobenchsuffix']);

        $this->assertBenchmarkCount(1, $benchmarks);
        $this->logger->warning(Argument::containingString('but it does not end with'))->shouldHaveBeenCalled();
    }

    /**
     * It should skip benchmarks that have no subjects.
     */
    public function testNoSubjects(): void
    {
        $this->factory->getMetadataForFile(__DIR__ . '/findertestnested/MyBench.php')->willReturn($this->benchmark1->reveal());
        $this->benchmark1->hasSubjects()->willReturn(false);

        $benchmarks = $this->createFinder()->findBenchmarks([__DIR__ . '/findertestnested/MyBench.php']);

        $this->assertBenchmarkCount(0, $benchmarks);
    }

    /**
     * @param Generator<BenchmarkMetadata> $benchmarks
     */
    private function assertBenchmarkCount(int $int, Generator $benchmarks): void
    {
        self::assertCount($int, iterator_to_array($benchmarks, false));
    }
}
