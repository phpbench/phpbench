<?php

namespace PhpBench\Tests\Unit\Benchmark\Metadata\Driver;

use Generator;
use PHPUnit\Framework\TestCase;
use PhpBench\Assertion\ParameterProvider;
use PhpBench\Benchmark\Metadata\Attributes\AfterClassMethods;
use PhpBench\Benchmark\Metadata\Attributes\AfterMethods;
use PhpBench\Benchmark\Metadata\Attributes\Assert;
use PhpBench\Benchmark\Metadata\Attributes\BeforeClassMethods;
use PhpBench\Benchmark\Metadata\Attributes\BeforeMethods;
use PhpBench\Benchmark\Metadata\Attributes\Executor;
use PhpBench\Benchmark\Metadata\Attributes\Groups;
use PhpBench\Benchmark\Metadata\Attributes\Iterations;
use PhpBench\Benchmark\Metadata\Attributes\OutputMode;
use PhpBench\Benchmark\Metadata\Attributes\OutputTimeUnit;
use PhpBench\Benchmark\Metadata\Attributes\ParamProviders;
use PhpBench\Benchmark\Metadata\Attributes\Revs;
use PhpBench\Benchmark\Metadata\Attributes\Skip;
use PhpBench\Benchmark\Metadata\Attributes\Sleep;
use PhpBench\Benchmark\Metadata\Attributes\Timeout;
use PhpBench\Benchmark\Metadata\Attributes\Warmup;
use PhpBench\Benchmark\Metadata\BenchmarkMetadata;
use PhpBench\Benchmark\Metadata\Driver\AttributeDriver;
use PhpBench\Benchmark\Metadata\SubjectMetadata;
use PhpBench\Reflection\ReflectionClass;
use PhpBench\Reflection\ReflectionHierarchy;
use PhpBench\Reflection\ReflectionMethod;

class AttributeDriverTest extends TestCase
{
    /**
     * @dataProvider provideLoadBenchmark
     */
    public function testLoadBenchmark(array $attributes, callable $assertion): void
    {
        $reflection = new ReflectionClass();
        $reflection->class = 'Test';
        $reflection->path = 'foo';
        $reflection->attributes = $attributes;
        $hierarchy = new ReflectionHierarchy();
        $hierarchy->addReflectionClass($reflection);

        $metadata = $this->createDriver()->getMetadataForHierarchy($hierarchy);

        $assertion($metadata);
    }

    /**
     * @return Generator<mixed>
     */
    public function provideLoadBenchmark(): Generator
    {
        yield [
            [
                new Executor('foobar'),
            ],
            function (BenchmarkMetadata $metadata) {
                self::assertEquals('foobar', $metadata->getExecutor()->getName());
            }
        ];
        yield [
            [
                new Executor('foobar', ['foo' => 'bar']),
            ],
            function (BenchmarkMetadata $metadata) {
                self::assertEquals(['foo' => 'bar'], $metadata->getExecutor()->getConfig());
            }
        ];
        yield [
            [
                new BeforeClassMethods(['foo','bar']),
            ],
            function (BenchmarkMetadata $metadata) {
                self::assertEquals(['foo', 'bar'], $metadata->getBeforeClassMethods());
            }
        ];
        yield [
            [
                new AfterClassMethods(['foo','bar']),
            ],
            function (BenchmarkMetadata $metadata) {
                self::assertEquals(['foo', 'bar'], $metadata->getAfterClassMethods());
            }
        ];
    }
    /**
     * @dataProvider provideLoadSubject
     */
    public function testLoadSubject(array $attributes, callable $assertion): void
    {
        $reflection = new ReflectionClass();
        $reflection->class = 'Test';
        $reflection->path = 'foo';
        $hierarchy = new ReflectionHierarchy();
        $hierarchy->addReflectionClass($reflection);

        $method = new ReflectionMethod();
        $method->reflectionClass = $reflection;
        $method->class = 'Test';
        $method->name = 'benchFoo';
        $method->attributes = $attributes;
        $reflection->methods[$method->name] = $method;

        $metadata = $this->createDriver()->getMetadataForHierarchy($hierarchy);
        $subjects = $metadata->getSubjects();
        $this->assertCount(1, $subjects);

        /** @var SubjectMetadata $metadata */
        $metadata = reset($subjects);

        $assertion($metadata);
    }

    /**
     * @return Generator<mixed>
     */
    public function provideLoadSubject(): Generator
    {
        yield [
            [
                new BeforeMethods(['foobar', 'barfoo']),
            ],
            function (SubjectMetadata $metadata) {
                self::assertEquals(['foobar', 'barfoo'], $metadata->getBeforeMethods());
            }
        ];
        yield [
            [
                new BeforeMethods('foobar'),
            ],
            function (SubjectMetadata $metadata) {
                self::assertEquals(['foobar'], $metadata->getBeforeMethods());
            }
        ];
        yield [
            [
                new AfterMethods(['foobar', 'barfoo']),
            ],
            function (SubjectMetadata $metadata) {
                self::assertEquals(['foobar', 'barfoo'], $metadata->getAfterMethods());
            }
        ];
        yield [
            [
                new Groups(['foobar', 'barfoo']),
            ],
            function (SubjectMetadata $metadata) {
                self::assertEquals(['foobar', 'barfoo'], $metadata->getGroups());
            }
        ];
        yield [
            [
                new Iterations(12),
            ],
            function (SubjectMetadata $metadata) {
                self::assertEquals([12], $metadata->getIterations());
            }
        ];
        yield [
            [
                new ParamProviders('one'),
            ],
            function (SubjectMetadata $metadata) {
                self::assertEquals(['one'], $metadata->getParamProviders());
            }
        ];
        yield [
            [
                new ParamProviders(['one', 'two']),
            ],
            function (SubjectMetadata $metadata) {
                self::assertEquals(['one','two'], $metadata->getParamProviders());
            }
        ];
        yield [
            [
                new Revs(12),
            ],
            function (SubjectMetadata $metadata) {
                self::assertEquals([12], $metadata->getRevs());
            }
        ];
        yield [
            [
                new Skip(),
            ],
            function (SubjectMetadata $metadata) {
                self::assertTrue($metadata->getSkip());
            }
        ];
        yield [
            [
                new Sleep(500),
            ],
            function (SubjectMetadata $metadata) {
                self::assertEquals(500, $metadata->getSleep());
            }
        ];
        yield [
            [
                new OutputTimeUnit('seconds'),
            ],
            function (SubjectMetadata $metadata) {
                self::assertEquals('seconds', $metadata->getOutputTimeUnit());
            }
        ];
        yield [
            [
                new OutputTimeUnit('seconds', 12),
            ],
            function (SubjectMetadata $metadata) {
                self::assertEquals('seconds', $metadata->getOutputTimeUnit());
                self::assertEquals(12, $metadata->getOutputTimePrecision());
            }
        ];
        yield [
            [
                new OutputMode('throughput'),
            ],
            function (SubjectMetadata $metadata) {
                self::assertEquals('throughput', $metadata->getOutputMode());
            }
        ];
        yield [
            [
                new Warmup(12),
            ],
            function (SubjectMetadata $metadata) {
                self::assertEquals([12], $metadata->getWarmup());
            }
        ];
        yield [
            [
                new Assert('12 < 12'),
            ],
            function (SubjectMetadata $metadata) {
                self::assertEquals(['12 < 12'], $metadata->getAssertions());
            }
        ];
        yield [
            [
                new Executor('foobar'),
            ],
            function (SubjectMetadata $metadata) {
                self::assertEquals('foobar', $metadata->getExecutor()->getName());
            }
        ];
        yield [
            [
                new Executor('foobar', ['foo' => 'bar']),
            ],
            function (SubjectMetadata $metadata) {
                self::assertEquals(['foo' => 'bar'], $metadata->getExecutor()->getConfig());
            }
        ];
        yield [
            [
                new Timeout(12.1),
            ],
            function (SubjectMetadata $metadata) {
                self::assertEquals(12.1, $metadata->getTimeout());
            }
        ];
    }

    private function createDriver(): AttributeDriver
    {
        return new AttributeDriver();
    }
}
