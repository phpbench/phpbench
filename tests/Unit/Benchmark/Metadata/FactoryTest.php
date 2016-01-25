<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\Unit\Model;

use PhpBench\Benchmark\Metadata\Factory;
use PhpBench\Tests\Util\TestUtil;

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
        $this->hierarchy->reveal()->class = 'Class';
        $this->reflection = $this->prophesize('PhpBench\Benchmark\Remote\ReflectionClass');
        $this->metadata = $this->prophesize('PhpBench\Model\Benchmark');
        $this->subjectMetadata = $this->prophesize('PhpBench\Model\Subject');

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
        $this->metadata->getSubjects()->willReturn(array());
        TestUtil::configureBenchmark($this->metadata);
        $metadata = $this->factory->getMetadataForFile(self::FNAME);
        $this->assertInstanceOf('PhpBench\Model\Benchmark', $metadata);
    }

    /**
     * It will return a benchmark populated with subjects.
     */
    public function testWithSubjects()
    {
        $this->hierarchy->isEmpty()->willReturn(false);
        $this->metadata->getSubjects()->willReturn(array(
            $this->subjectMetadata->reveal(),
        ));
        TestUtil::configureBenchmark($this->metadata, array(
            'path' => self::PATH,
        ));
        TestUtil::configureSubject($this->subjectMetadata);
        $this->reflector->getParameterSets(self::PATH, array())->willReturn(array());
        $this->subjectMetadata->setParameterSets(array())->shouldBeCalled();

        $metadata = $this->factory->getMetadataForFile(self::FNAME);
        $this->assertInstanceOf('PhpBench\Model\Benchmark', $metadata);
        $this->assertInternalType('array', $metadata->getSubjects());
        $this->assertCount(1, $metadata->getSubjects());
    }

    /**
     * It should throw an exception if a before/after method does not exist on the benchmark.
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Unknown before
     */
    public function testValidationBeforeMethodsBenchmark()
    {
        $this->hierarchy->isEmpty()->willReturn(false);
        TestUtil::configureBenchmark($this->metadata, array(
            'beforeClassMethods' => array('beforeMe'),
        ));
        $this->hierarchy->hasMethod('beforeMe')->willReturn(false);
        $this->hierarchy->hasStaticMethod('beforeMe')->willReturn(true);

        $this->factory->getMetadataForFile(self::FNAME);
    }

    /**
     * It should throw an exception if a before class method is not static.
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage must be static in benchmark class "TestClass"
     */
    public function testValidationBeforeMethodsBenchmarkNotStatic()
    {
        $this->hierarchy->isEmpty()->willReturn(false);
        $this->reflection->class = 'TestClass';
        TestUtil::configureBenchmark($this->metadata, array(
            'beforeClassMethods' => array('beforeMe'),
        ));
        $this->hierarchy->hasMethod('beforeMe')->willReturn(true);
        $this->hierarchy->hasStaticMethod('beforeMe')->willReturn(false);

        $this->factory->getMetadataForFile(self::FNAME);
    }

    /**
     * It should throw an exception if a before/after method does not exist on the subject.
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Unknown before method "beforeMe" in benchmark class "TestClass"
     */
    public function testValidationBeforeMethodsSubject()
    {
        $this->hierarchy->isEmpty()->willReturn(false);
        $this->reflection->class = 'TestClass';
        TestUtil::configureBenchmark($this->metadata, array());
        $this->metadata->getSubjects()->willReturn(array(
            $this->subjectMetadata->reveal(),
        ));
        TestUtil::configureSubject($this->subjectMetadata, array(
            'beforeMethods' => array('beforeMe'),
        ));
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
        TestUtil::configureBenchmark($this->metadata, array(
        ));
        $this->metadata->getSubjects()->willReturn(array(
            $this->subjectMetadata->reveal(),
        ));
        TestUtil::configureSubject($this->subjectMetadata, array(
            'afterMethods' => array('afterMe'),
        ));
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

    /**
     * It should throw an exception if the parameters are not in a valid format.
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Each parameter set must be an array, got "string" for TestBench::benchTest
     */
    public function testInvalidParameters()
    {
        $this->hierarchy->isEmpty()->willReturn(false);
        $this->metadata->getSubjects()->willReturn(array(
            $this->subjectMetadata->reveal(),
        ));
        TestUtil::configureBenchmark($this->metadata, array(
            'class' => 'TestBench',
            'path' => self::PATH,
        ));
        TestUtil::configureSubject($this->subjectMetadata, array(
            'name' => 'benchTest',
        ));
        $this->reflector->getParameterSets(self::PATH, array())->willReturn(array('asd' => 'bar'));

        $this->factory->getMetadataForFile(self::FNAME);
    }
}
