<?php

namespace PhpBench\Tests\Unit\Benchmark\Metadata\Driver;

use Generator;
use PhpBench\Attributes\AfterClassMethods;
use PhpBench\Attributes\AfterMethods;
use PhpBench\Attributes\Assert;
use PhpBench\Attributes\BeforeClassMethods;
use PhpBench\Attributes\BeforeMethods;
use PhpBench\Attributes\Executor;
use PhpBench\Attributes\Format;
use PhpBench\Attributes\Groups;
use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\OutputMode;
use PhpBench\Attributes\OutputTimeUnit;
use PhpBench\Attributes\ParamProviders;
use PhpBench\Attributes\RetryThreshold;
use PhpBench\Attributes\Revs;
use PhpBench\Attributes\Skip;
use PhpBench\Attributes\Sleep;
use PhpBench\Attributes\Timeout;
use PhpBench\Attributes\Warmup;
use PhpBench\Benchmark\Metadata\BenchmarkMetadata;
use PhpBench\Benchmark\Metadata\Driver\AttributeDriver;
use PhpBench\Benchmark\Metadata\SubjectMetadata;
use PhpBench\Reflection\ReflectionClass;
use PhpBench\Reflection\ReflectionHierarchy;
use PhpBench\Reflection\ReflectionMethod;
use PHPUnit\Framework\TestCase;

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
    public static function provideLoadBenchmark(): Generator
    {
        yield [
            [
                new Executor('foobar'),
            ],
            function (BenchmarkMetadata $metadata): void {
                self::assertEquals('foobar', $metadata->getExecutor()->getName());
            }
        ];

        yield [
            [
                new Executor('foobar', ['foo' => 'bar']),
            ],
            function (BenchmarkMetadata $metadata): void {
                self::assertEquals(['foo' => 'bar'], $metadata->getExecutor()->getConfig());
            }
        ];

        yield [
            [
                new BeforeClassMethods(['foo','bar']),
            ],
            function (BenchmarkMetadata $metadata): void {
                self::assertEquals(['foo', 'bar'], $metadata->getBeforeClassMethods());
            }
        ];

        yield [
            [
                new AfterClassMethods(['foo','bar']),
            ],
            function (BenchmarkMetadata $metadata): void {
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
    public static function provideLoadSubject(): Generator
    {
        yield [
            [
                new BeforeMethods(['foobar', 'barfoo']),
            ],
            function (SubjectMetadata $metadata): void {
                self::assertEquals(['foobar', 'barfoo'], $metadata->getBeforeMethods());
            }
        ];

        yield [
            [
                new BeforeMethods('foobar'),
            ],
            function (SubjectMetadata $metadata): void {
                self::assertEquals(['foobar'], $metadata->getBeforeMethods());
            }
        ];

        yield [
            [
                new AfterMethods(['foobar', 'barfoo']),
            ],
            function (SubjectMetadata $metadata): void {
                self::assertEquals(['foobar', 'barfoo'], $metadata->getAfterMethods());
            }
        ];

        yield [
            [
                new Groups(['foobar', 'barfoo']),
            ],
            function (SubjectMetadata $metadata): void {
                self::assertEquals(['foobar', 'barfoo'], $metadata->getGroups());
            }
        ];

        yield [
            [
                new Iterations(12),
            ],
            function (SubjectMetadata $metadata): void {
                self::assertEquals([12], $metadata->getIterations());
            }
        ];

        yield [
            [
                new ParamProviders('one'),
            ],
            function (SubjectMetadata $metadata): void {
                self::assertEquals(['one'], $metadata->getParamProviders());
            }
        ];

        yield [
            [
                new ParamProviders(['one', 'two']),
            ],
            function (SubjectMetadata $metadata): void {
                self::assertEquals(['one','two'], $metadata->getParamProviders());
            }
        ];

        yield [
            [
                new Revs(12),
            ],
            function (SubjectMetadata $metadata): void {
                self::assertEquals([12], $metadata->getRevs());
            }
        ];

        yield [
            [
                new Skip(),
            ],
            function (SubjectMetadata $metadata): void {
                self::assertTrue($metadata->getSkip());
            }
        ];

        yield [
            [
                new Sleep(500),
            ],
            function (SubjectMetadata $metadata): void {
                self::assertEquals(500, $metadata->getSleep());
            }
        ];

        yield [
            [
                new OutputTimeUnit('seconds'),
            ],
            function (SubjectMetadata $metadata): void {
                self::assertEquals('seconds', $metadata->getOutputTimeUnit());
            }
        ];

        yield [
            [
                new OutputTimeUnit('seconds', 12),
            ],
            function (SubjectMetadata $metadata): void {
                self::assertEquals('seconds', $metadata->getOutputTimeUnit());
                self::assertEquals(12, $metadata->getOutputTimePrecision());
            }
        ];

        yield [
            [
                new OutputMode('throughput'),
            ],
            function (SubjectMetadata $metadata): void {
                self::assertEquals('throughput', $metadata->getOutputMode());
            }
        ];

        yield [
            [
                new Warmup(12),
            ],
            function (SubjectMetadata $metadata): void {
                self::assertEquals([12], $metadata->getWarmup());
            }
        ];

        yield [
            [
                new Assert('12 < 12'),
            ],
            function (SubjectMetadata $metadata): void {
                self::assertEquals(['12 < 12'], $metadata->getAssertions());
            }
        ];

        yield [
            [
                new Format('mode(foobar) as ms'),
            ],
            function (SubjectMetadata $metadata): void {
                self::assertEquals('mode(foobar) as ms', $metadata->getFormat());
            }
        ];

        yield [
            [
                new Executor('foobar'),
            ],
            function (SubjectMetadata $metadata): void {
                self::assertEquals('foobar', $metadata->getExecutor()->getName());
            }
        ];

        yield [
            [
                new Executor('foobar', ['foo' => 'bar']),
            ],
            function (SubjectMetadata $metadata): void {
                self::assertEquals(['foo' => 'bar'], $metadata->getExecutor()->getConfig());
            }
        ];

        yield [
            [
                new Timeout(12.1),
            ],
            function (SubjectMetadata $metadata): void {
                self::assertEquals(12.1, $metadata->getTimeout());
            }
        ];

        yield [
            [
                new RetryThreshold(10),
            ],
            function (SubjectMetadata $metadata): void {
                self::assertEquals(10.0, $metadata->getRetryThreshold());
            }
        ];
    }

    private function createDriver(): AttributeDriver
    {
        return new AttributeDriver();
    }

    private function shouldSkip(): bool
    {
        return PHP_VERSION_ID < 80000;
    }

    public function testInheritedMethodsWillNotAppearMultipleTimesInTheSubject(): void
    {
        if ($this->shouldSkip()) {
            $this->markTestSkipped('PHP 8 only');

            return;
        }

        $baseClass = new ReflectionClass(__FILE__, 'BaseClassBench');
        $childClass = new ReflectionClass(__FILE__, 'ChildClassBench');

        $baseClassMethod = new ReflectionMethod();
        $childClassOverridingBenchMethod = new ReflectionMethod();

        $baseClassMethod->name = 'benchInheritedMethod';
        $childClassOverridingBenchMethod->name = 'benchInheritedMethod';

        $baseClass->methods[] = $baseClassMethod;
        $childClass->methods[] = $childClassOverridingBenchMethod;

        $hierarchy = new ReflectionHierarchy([
            $childClass,
            $baseClass
        ]);

        self::assertCount(
            1,
            $this->createDriver()
                ->getMetadataForHierarchy($hierarchy)
                ->getSubjects()
        );
    }

    public function testInheritedMethodsWillNotLeadToRepeatedParameterProviderRegistration(): void
    {
        if ($this->shouldSkip()) {
            $this->markTestSkipped('PHP 8 only');

            return;
        }

        $baseClass = new ReflectionClass(__FILE__, 'BaseClassBench');
        $childClass = new ReflectionClass(__FILE__, 'ChildClassBench');

        $baseClassProvider = new ReflectionMethod();
        $baseClassMethod = new ReflectionMethod();
        $childClassOverridingBenchMethod = new ReflectionMethod();

        $baseClassProvider->name = 'parameterProvider';
        $baseClassMethod->name = 'benchInheritedMethod';
        $childClassOverridingBenchMethod->name = 'benchInheritedMethod';

        $baseClassMethod->attributes[] = new ParamProviders(['parameterProvider']);
        // Attributes are inherited - we're emulating what the PHP engine does
        $childClassOverridingBenchMethod->attributes[] = new ParamProviders(['parameterProvider']);

        $baseClass->methods[] = $baseClassProvider;
        $baseClass->methods[] = $baseClassMethod;
        $childClass->methods[] = $baseClassProvider;
        $childClass->methods[] = $childClassOverridingBenchMethod;

        $hierarchy = new ReflectionHierarchy([
            $childClass,
            $baseClass
        ]);

        $subjects = $this->createDriver()
            ->getMetadataForHierarchy($hierarchy)
            ->getSubjects();

        self::assertCount(1, $subjects);
        self::assertArrayHasKey('benchInheritedMethod', $subjects);

        $subject = $subjects['benchInheritedMethod'];

        self::assertCount(1, $subject->getParamProviders());
    }
}
