<?php

namespace PhpBench\Tests\Unit\Benchmark\Metadata\Driver;

use DTL\Invoke\Invoke;
use Generator;
use PhpBench\Benchmark\Metadata\BenchmarkMetadata;
use PhpBench\Benchmark\Metadata\Driver\ConfigDriver;
use PhpBench\Benchmark\Metadata\DriverInterface;
use PhpBench\Benchmark\Metadata\SubjectMetadata;
use PhpBench\Reflection\ReflectionHierarchy;
use PhpBench\Tests\ProphecyTrait;
use PHPUnit\Framework\TestCase;

class ConfigDriverTest extends TestCase
{
    use ProphecyTrait;
    public const EXAMPLE_SUBJECT = 'testSubject';

    /**
     * @var ObjectProphecy<DriverInterface>
     */
    private $innerDriver;

    /**
     * @var ReflectionHierarchy
     */
    private $hierarchy;

    protected function setUp(): void
    {
        $this->hierarchy = new ReflectionHierarchy([]);
        $this->innerDriver = $this->prophesize(DriverInterface::class);
    }

    /**
     * @dataProvider provideDriver
     */
    public function testDriver(array $config, callable $assertion): void
    {
        $driver = Invoke::new(ConfigDriver::class, array_merge([
            'innerDriver' => $this->innerDriver->reveal(),
        ], $config));

        $metadata = new BenchmarkMetadata(__DIR__, 'Foo');
        $metadata->getOrCreateSubject(self::EXAMPLE_SUBJECT);

        $this->innerDriver->getMetadataForHierarchy($this->hierarchy)->willReturn($metadata);

        assert($driver instanceof DriverInterface);
        $assertion($driver->getMetadataForHierarchy($this->hierarchy)->getOrCreateSubject(self::EXAMPLE_SUBJECT));
    }

    public static function provideDriver(): Generator
    {
        yield [
            [
                'assert' => ['example_assert'],
                'executor' => 'example_executor',
                'format' => 'example_format',
                'iterations' => [5],
                'mode' => 'example_mode',
                'timeUnit' => 'example_time_unit',
                'revs' => [10],
                'timeout' => 20.1,
                'warmup' => [30],
                'retryThreshold' => 5.0,
            ],
            function (SubjectMetadata $subject): void {
                self::assertEquals(['example_assert'], $subject->getAssertions());
                self::assertEquals('example_executor', $subject->getExecutor()->getName());
                self::assertEquals('example_format', $subject->getFormat());
                self::assertEquals([5], $subject->getIterations());
                self::assertEquals('example_mode', $subject->getOutputMode());
                self::assertEquals('example_time_unit', $subject->getOutputTimeUnit());
                self::assertEquals([10], $subject->getRevs());
                self::assertEquals(20.1, $subject->getTimeout());
                self::assertEquals([30], $subject->getWarmup());
                self::assertEquals(5.0, $subject->getRetryThreshold());
            }
        ];
    }
}
