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

namespace PhpBench\Tests\Unit\Benchmark\Metadata;

use InvalidArgumentException;
use PhpBench\Benchmark\Metadata\BenchmarkMetadata;
use PhpBench\Benchmark\Metadata\DriverInterface;
use PhpBench\Benchmark\Metadata\MetadataFactory;
use PhpBench\Benchmark\Metadata\SubjectMetadata;
use PhpBench\Reflection\ReflectionClass;
use PhpBench\Reflection\ReflectionHierarchy;
use PhpBench\Reflection\ReflectorInterface;
use PhpBench\Tests\TestCase;
use PhpBench\Benchmark\Metadata\Exception\CouldNotLoadMetadataException;
use PhpBench\Tests\Util\TestLogger;
use PhpBench\Tests\Util\TestUtil;
use Prophecy\Prophecy\ObjectProphecy;

class MetadataFactoryTest extends TestCase
{
    public const FNAME = 'fname';
    public const PATH = '/path/to';

    private $factory;

    /**
     * @var MetadataFactoryTest
     */
    private $reflector;

    /**
     * @var ObjectProphecy<DriverInterface>
     */
    private $driver;

    /**
     * @var ObjectProphecy<ReflectionHierarchy>
     */
    private $hierarchy;

    /**
     * @var ObjectProphecy<ReflectionClass>
     */
    private $reflection;

    /**
     * @var ObjectProphecy<BenchmarkMetdata>
     */
    private $metadata;

    /**
     * @var ObjectProphecy<SubjectMetadata>
     */
    private $subjectMetadata;

    protected function setUp(): void
    {
        $this->reflector = $this->prophesize(ReflectorInterface::class);
        $this->driver = $this->prophesize(DriverInterface::class);
        $this->factory = new MetadataFactory(
            $this->reflector->reveal(),
            $this->driver->reveal()
        );

        $this->hierarchy = $this->prophesize(ReflectionHierarchy::class);
        $this->hierarchy->reveal();
        $this->reflection = $this->prophesize(ReflectionClass::class);
        $this->metadata = $this->prophesize(BenchmarkMetadata::class);
        $this->subjectMetadata = $this->prophesize(SubjectMetadata::class);

        $this->reflector->reflect(self::FNAME)->willReturn($this->hierarchy->reveal());
        $this->driver->getMetadataForHierarchy($this->hierarchy->reveal())->willReturn($this->metadata->reveal());
        $this->reflection->abstract = false;
        $this->hierarchy->getTop()->willReturn($this->reflection->reveal());
    }

    /**
     * It can retrieve the metadata for a file containing a class.
     */
    public function testGetMetadataForFile(): void
    {
        $this->hierarchy->isEmpty()->willReturn(false);
        $this->metadata->getSubjects()->willReturn([]);
        TestUtil::configureBenchmarkMetadata($this->metadata);
        $metadata = $this->factory->getMetadataForFile(self::FNAME);
        $this->assertInstanceOf('PhpBench\Benchmark\Metadata\BenchmarkMetadata', $metadata);
    }

    public function testWarnOnCouldNotLoadMetadata(): void
    {
        $this->hierarchy->isEmpty()->willReturn(false);
        $logger = new TestLogger();
        $factory = new MetadataFactory(
            $this->reflector->reveal(),
            $this->driver->reveal(),
            $logger,
            true
        );
        $this->driver->getMetadataForHierarchy($this->hierarchy->reveal())->willThrow(new CouldNotLoadMetadataException('no'));

        $factory->getMetadataForFile(self::FNAME);
        self::assertCount(1, $logger->records);
    }

    public function testNoWarnOnCouldNotLoadMetadataByDefault(): void
    {
        $this->expectException(CouldNotLoadMetadataException::class);
        $this->expectExceptionMessage('no');
        $this->hierarchy->isEmpty()->willReturn(false);
        $logger = new TestLogger();
        $factory = new MetadataFactory(
            $this->reflector->reveal(),
            $this->driver->reveal(),
            $logger
        );
        $this->driver->getMetadataForHierarchy($this->hierarchy->reveal())->willThrow(new CouldNotLoadMetadataException('no'));

        $factory->getMetadataForFile(self::FNAME);
        self::assertCount(1, $logger->records);
    }

    public function testExceptionIfDriverDoesNotThrowCouldNotLoadMetadata(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('no');
        $this->hierarchy->isEmpty()->willReturn(false);
        $logger = new TestLogger();
        $factory = new MetadataFactory(
            $this->reflector->reveal(),
            $this->driver->reveal(),
            $logger,
            true
        );
        $this->driver->getMetadataForHierarchy($this->hierarchy->reveal())->willThrow(new \RuntimeException('no'));
        $factory->getMetadataForFile(self::FNAME);
    }

    /**
     * It will return a benchmark populated with subjects.
     */
    public function testWithSubjects(): void
    {
        $this->hierarchy->isEmpty()->willReturn(false);
        $this->metadata->getSubjects()->willReturn([
            $this->subjectMetadata->reveal(),
        ]);
        TestUtil::configureBenchmarkMetadata($this->metadata, [
            'path' => self::PATH,
        ]);
        TestUtil::configureSubjectMetadata($this->subjectMetadata);

        $metadata = $this->factory->getMetadataForFile(self::FNAME);
        $this->assertInstanceOf('PhpBench\Benchmark\Metadata\BenchmarkMetadata', $metadata);
        $this->assertIsArray($metadata->getSubjects());
        $this->assertCount(1, $metadata->getSubjects());
    }

    /**
     * It should throw an exception if a before/after method does not exist on the benchmark.
     *
     */
    public function testValidationBeforeMethodsBenchmark(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown before');
        $this->hierarchy->isEmpty()->willReturn(false);
        TestUtil::configureBenchmarkMetadata($this->metadata, [
            'beforeClassMethods' => ['beforeMe'],
        ]);
        $this->hierarchy->hasMethod('beforeMe')->willReturn(false);
        $this->hierarchy->hasStaticMethod('beforeMe')->willReturn(true);

        $this->factory->getMetadataForFile(self::FNAME);
    }

    /**
     * It should throw an exception if a before class method is not static.
     *
     */
    public function testValidationBeforeClassMethodsBenchmarkNotStatic(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('must be static in benchmark class "TestClass"');
        $this->hierarchy->isEmpty()->willReturn(false);
        $this->reflection->class = 'TestClass';
        TestUtil::configureBenchmarkMetadata($this->metadata, [
            'beforeClassMethods' => ['beforeMe'],
        ]);
        $this->hierarchy->hasMethod('beforeMe')->willReturn(true);
        $this->hierarchy->hasStaticMethod('beforeMe')->willReturn(false);

        $this->factory->getMetadataForFile(self::FNAME);
    }

    /**
     * It should throw an exception if a before method IS static.
     *
     */
    public function testValidationBeforeMethodsBenchmarkIsStatic(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('before method "beforeMe" must not be static in benchmark class "TestClass"');
        $this->hierarchy->isEmpty()->willReturn(false);
        $this->reflection->class = 'TestClass';
        TestUtil::configureBenchmarkMetadata($this->metadata, []);
        $this->metadata->getSubjects()->willReturn([
            $this->subjectMetadata->reveal(),
        ]);
        TestUtil::configureSubjectMetadata($this->subjectMetadata, [
            'beforeMethods' => ['beforeMe'],
        ]);
        $this->hierarchy->hasMethod('beforeMe')->willReturn(true);
        $this->hierarchy->hasStaticMethod('beforeMe')->willReturn(true);

        $this->factory->getMetadataForFile(self::FNAME);
    }

    /**
     * It should throw an exception if a before/after method does not exist on the subject.
     *
     */
    public function testValidationBeforeMethodsSubject(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown before method "beforeMe" in benchmark class "TestClass"');
        $this->hierarchy->isEmpty()->willReturn(false);
        $this->reflection->class = 'TestClass';
        TestUtil::configureBenchmarkMetadata($this->metadata, []);
        $this->metadata->getSubjects()->willReturn([
            $this->subjectMetadata->reveal(),
        ]);
        TestUtil::configureSubjectMetadata($this->subjectMetadata, [
            'beforeMethods' => ['beforeMe'],
        ]);
        $this->hierarchy->hasMethod('beforeMe')->willReturn(false);

        $this->factory->getMetadataForFile(self::FNAME);
    }

    /**
     * It should throw an exception if an after method does not exist.
     *
     */
    public function testValidationAfterMethods(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown after');
        $this->hierarchy->isEmpty()->willReturn(false);
        TestUtil::configureBenchmarkMetadata($this->metadata, [
        ]);
        $this->metadata->getSubjects()->willReturn([
            $this->subjectMetadata->reveal(),
        ]);
        TestUtil::configureSubjectMetadata($this->subjectMetadata, [
            'afterMethods' => ['afterMe'],
        ]);
        $this->hierarchy->hasMethod('afterMe')->willReturn(false);

        $this->factory->getMetadataForFile(self::FNAME);
    }

    /**
     * It should return null if the class hierarchy is empty.
     */
    public function testEmptyClassHierachy(): void
    {
        $this->hierarchy->isEmpty()->willReturn(true);
        $metadata = $this->factory->getMetadataForFile(self::FNAME);
        $this->assertNull($metadata);
    }
}
