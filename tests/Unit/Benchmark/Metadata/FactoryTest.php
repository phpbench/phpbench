<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\Unit\Benchmark\Metadata;

use PhpBench\Benchmark\Metadata\Factory;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    const FNAME = 'fname';
    const PATH = '/path/to';

    private $factory;

    public function setUp()
    {
        $this->reflector = $this->prophesize('PhpBench\Benchmark\Remote\Reflector');
        $this->driver = $this->prophesize('PhpBench\Benchmark\Metadata\DriverInterface');
        $this->factory = new Factory(
            $this->reflector->reveal(),
            $this->driver->reveal()
        );

        $this->hierarchy = $this->prophesize('PhpBench\Benchmark\Remote\ReflectionHierarchy');
        $this->reflection = $this->prophesize('PhpBench\Benchmark\Remote\ReflectionClass');
        $this->metadata = $this->prophesize('PhpBench\Benchmark\Metadata\BenchmarkMetadata');
        $this->subjectMetadata = $this->prophesize('PhpBench\Benchmark\Metadata\SubjectMetadata');

        $this->reflector->reflect(self::FNAME)->willReturn($this->hierarchy->reveal());
        $this->driver->getMetadataForHierarchy($this->hierarchy->reveal())->willReturn($this->metadata->reveal());
        $this->reflection->abstract = false;
        $this->hierarchy->getTop()->willReturn($this->reflection->reveal());
    }

    /**
     * It can retrieve the metadata for a file containing a class.
     */
    public function testGetMetadataForFile()
    {
        $this->hierarchy->isEmpty()->willReturn(false);
        $this->metadata->getSubjectMetadatas()->willReturn(array());
        $metadata = $this->factory->getMetadataForFile(self::FNAME);
        $this->assertInstanceOf('PhpBench\Benchmark\Metadata\BenchmarkMetadata', $metadata);
    }

    /**
     * It will return a benchmark populated with subjects.
     */
    public function testWithSubjects()
    {
        $this->hierarchy->isEmpty()->willReturn(false);
        $this->metadata->getSubjectMetadatas()->willReturn(array(
            $this->subjectMetadata->reveal(),
        ));
        $this->subjectMetadata->getBeforeMethods()->willReturn(array());
        $this->subjectMetadata->getAfterMethods()->willReturn(array());
        $this->subjectMetadata->getParamProviders()->willReturn(array());
        $this->metadata->getPath()->willReturn(self::PATH);
        $this->reflector->getParameterSets(self::PATH, array())->willReturn(array());
        $this->subjectMetadata->setParameterSets(array())->shouldBeCalled();

        $metadata = $this->factory->getMetadataForFile(self::FNAME);
        $this->assertInstanceOf('PhpBench\Benchmark\Metadata\BenchmarkMetadata', $metadata);
        $this->assertInternalType('array', $metadata->getSubjectMetadatas());
        $this->assertCount(1, $metadata->getSubjectMetadatas());
    }

    /**
     * It should throw an exception if a before method does not exist.
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Unknown before
     */
    public function testValidationBeforeMethods()
    {
        $this->hierarchy->isEmpty()->willReturn(false);
        $this->metadata->getSubjectMetadatas()->willReturn(array(
            $this->subjectMetadata->reveal(),
        ));
        $this->subjectMetadata->getBeforeMethods()->willReturn(array(
            'beforeMe',
        ));
        $this->subjectMetadata->getAfterMethods()->willReturn(array());
        $this->subjectMetadata->getParamProviders()->willReturn(array());
        $this->subjectMetadata->getClass()->willReturn('Test');
        $this->metadata->getPath()->willReturn(self::PATH);
        $this->hierarchy->hasMethod('beforeMe')->willReturn(false);

        $this->factory->getMetadataForFile(self::FNAME);
    }

    /**
     * It should throw an exception if an after method does not exist.
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Unknown after
     */
    public function testValidationAfterMethods()
    {
        $this->hierarchy->isEmpty()->willReturn(false);
        $this->metadata->getSubjectMetadatas()->willReturn(array(
            $this->subjectMetadata->reveal(),
        ));
        $this->subjectMetadata->getAfterMethods()->willReturn(array(
            'afterMe',
        ));
        $this->subjectMetadata->getBeforeMethods()->willReturn(array());
        $this->subjectMetadata->getParamProviders()->willReturn(array());
        $this->subjectMetadata->getClass()->willReturn('Test');
        $this->metadata->getPath()->willReturn(self::PATH);
        $this->hierarchy->hasMethod('afterMe')->willReturn(false);

        $this->factory->getMetadataForFile(self::FNAME);
    }

    /**
     * It should return null if the class hierarchy is empty.
     */
    public function testEmptyClassHierachy()
    {
        $this->hierarchy->isEmpty()->willReturn(true);
        $metadata = $this->factory->getMetadataForFile(self::FNAME);
        $this->assertNull($metadata);
    }
}
