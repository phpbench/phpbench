<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\Unit\Benchmark\Metadata\Driver;

use PhpBench\Benchmark\Metadata\Annotations;
use PhpBench\Benchmark\Metadata\Driver\AnnotationDriver;
use PhpBench\Benchmark\Remote\ReflectionClass;
use PhpBench\Benchmark\Remote\ReflectionHierarchy;
use PhpBench\Benchmark\Remote\ReflectionMethod;

class AnnotationDriverTest extends \PHPUnit_Framework_TestCase
{
    private $driver;
    private $reflector;

    public function setUp()
    {
        $this->reflector = $this->prophesize('PhpBench\Benchmark\Remote\Reflector');
        $this->driver = new AnnotationDriver(
            $this->reflector->reveal()
        );
    }

    /**
     * It should return class metadata according to annotations.
     */
    public function testLoadClassMetadata()
    {
        $reflection = new ReflectionClass();
        $reflection->class = 'Test';
        $reflection->comment = <<<EOT
/**
 * @BeforeMethods({"beforeOne", "beforeTwo"})
 * @AfterMethods({"afterOne", "afterTwo"})
 * @Groups({"groupOne", "groupTwo"})
 * @Iterations(50)
 * @ParamProviders({"ONE", "TWO"})
 * @Revs(1000)
 * @Skip()
 * @Concurrencies({1, 5})
 */
EOT;
        $hierarchy = new ReflectionHierarchy();
        $hierarchy->addReflectionClass($reflection);

        $metadata = $this->driver->getMetadataForHierarchy($hierarchy);
        $this->assertEquals(array('beforeOne', 'beforeTwo'), $metadata->getBeforeMethods());
        $this->assertEquals(array('afterOne', 'afterTwo'), $metadata->getAfterMethods());
        $this->assertEquals(array('groupOne', 'groupTwo'), $metadata->getGroups());
        $this->assertEquals(50, $metadata->getIterations());
        $this->assertEquals(array('ONE', 'TWO'), $metadata->getParamProviders());
        $this->assertEquals(1000, $metadata->getRevs());
        $this->assertEquals(array(1, 5), $metadata->getConcurrencies());
        $this->assertTrue($metadata->getSkip());
    }

    /**
     * It should ignore common annotations.
     */
    public function testIgnoreCommonAnnotations()
    {
        $reflection = new ReflectionClass();
        $reflection->class = 'Test';
        $reflection->comment = <<<EOT
/**
 * @since Foo
 * @author Daniel Leech
 */
EOT;
        $hierarchy = new ReflectionHierarchy();
        $hierarchy->addReflectionClass($reflection);

        $this->driver->getMetadataForHierarchy($hierarchy);
    }

    /**
     * It should return method metadata according to annotations.
     */
    public function testLoadSubjectMetadata()
    {
        $reflection = new ReflectionClass();
        $reflection->class = 'Test';
        $hierarchy = new ReflectionHierarchy();
        $hierarchy->addReflectionClass($reflection);

        $method = new ReflectionMethod();
        $method->class = 'Test';
        $method->name = 'benchFoo';
        $method->comment = <<<EOT
    /**
     * @BeforeMethods({"beforeOne", "beforeTwo"})
     * @AfterMethods({"afterOne", "afterTwo"})
     * @Groups({"groupOne", "groupTwo"})
     * @Iterations(50)
     * @ParamProviders({"ONE", "TWO"})
     * @Revs(1000)
     */
EOT;
        $reflection->methods[$method->name] = $method;

        $metadata = $this->driver->getMetadataForHierarchy($hierarchy);
        $subjectMetadatas = $metadata->getSubjectMetadatas();
        $this->assertCount(1, $subjectMetadatas);
        $metadata = reset($subjectMetadatas);
        $this->assertEquals(array('beforeOne', 'beforeTwo'), $metadata->getBeforeMethods());
        $this->assertEquals(array('afterOne', 'afterTwo'), $metadata->getAfterMethods());
        $this->assertEquals(array('groupOne', 'groupTwo'), $metadata->getGroups());
        $this->assertEquals(50, $metadata->getIterations());
        $this->assertEquals(array('ONE', 'TWO'), $metadata->getParamProviders());
        $this->assertEquals(1000, $metadata->getRevs());
        $this->assertFalse($metadata->getSkip());
    }

    /**
     * Subject metadata should override class metadata.
     */
    public function testLoadSubjectMetadataOverride()
    {
        $reflection = new ReflectionClass();
        $reflection->class = 'Test';
        $reflection->comment = <<<EOT
    /**
     * @BeforeMethods({"beforeOne", "beforeTwo"})
     */
EOT;
        $hierarchy = new ReflectionHierarchy();
        $hierarchy->addReflectionClass($reflection);

        $method = new ReflectionMethod();
        $method->class = 'Test';
        $method->name = 'benchFoo';
        $method->comment = <<<EOT
    /**
     * @BeforeMethods({"beforeFive"})
     */
EOT;
        $reflection->methods[$method->name] = $method;

        $metadata = $this->driver->getMetadataForHierarchy($hierarchy);
        $subjectMetadatas = $metadata->getSubjectMetadatas();
        $this->assertCount(1, $subjectMetadatas);
        $metadata = reset($subjectMetadatas);
        $this->assertEquals(array('beforeFive'), $metadata->getBeforeMethods());
    }

    /**
     * It should merge class parent classes.
     */
    public function testLoadSubjectMetadataMerge()
    {
        $reflection = new ReflectionClass();
        $reflection->class = 'TestChild';
        $reflection->comment = <<<EOT
    /**
     * @BeforeMethods({"class2"})
     */
EOT;
        $hierarchy = new ReflectionHierarchy();

        $method = new ReflectionMethod();
        $method->class = 'Test';
        $method->name = 'benchFoo';
        $method->comment = <<<EOT
    /**
     * @Revs(2000)
     */
EOT;
        $reflection->methods[$method->name] = $method;
        $method = new ReflectionMethod();
        $method->class = 'Test';
        $method->name = 'benchBar';
        $method->comment = <<<EOT
    /**
     * @Iterations(99)
     */
EOT;
        $reflection->methods[$method->name] = $method;
        $hierarchy->addReflectionClass($reflection);

        $reflection = new ReflectionClass();
        $reflection->class = 'Test';
        $reflection->comment = <<<EOT
    /**
     * @AfterMethods({"after"})
     * @Iterations(50)
     */
EOT;
        $method = new ReflectionMethod();
        $method->class = 'Test';
        $method->name = 'benchFoo';
        $method->comment = <<<EOT
    /**
     * @Revs(1000)
     */
EOT;
        $reflection->methods[$method->name] = $method;
        $method = new ReflectionMethod();
        $method->class = 'Test';
        $method->name = 'benchBar';
        $method->comment = <<<EOT
    /**
     * @Revs(50)
     */
EOT;
        $reflection->methods[$method->name] = $method;

        $method = new ReflectionMethod();
        $method->class = 'Test';
        $method->name = 'benchNoAnnotations';
        $method->comment = null;
        $reflection->methods[$method->name] = $method;
        $hierarchy->addReflectionClass($reflection);

        $metadata = $this->driver->getMetadataForHierarchy($hierarchy);

        $this->assertEquals(array('class2'), $metadata->getBeforeMethods());
        $subjectMetadatas = $metadata->getSubjectMetadatas();
        $this->assertCount(3, $subjectMetadatas);
        $subjectOne = array_shift($subjectMetadatas);
        $subjectTwo = array_shift($subjectMetadatas);
        $subjectThree = array_shift($subjectMetadatas);

        $this->assertEquals('benchFoo', $subjectOne->getName());
        $this->assertEquals(2000, $subjectOne->getRevs());

        $this->assertEquals('benchBar', $subjectTwo->getName());
        $this->assertEquals(99, $subjectTwo->getIterations());
        $this->assertEquals(50, $subjectTwo->getRevs());
        $this->assertEquals(array('class2'), $subjectTwo->getBeforeMethods());
        $this->assertEquals(array('after'), $subjectTwo->getAfterMethods());
        $this->assertEquals(array('class2'), $subjectThree->getBeforeMethods());
        $this->assertEquals(array('after'), $subjectThree->getAfterMethods());
    }

    /**
     * It should extend values of previous annotations when the "extend" option is true.
     */
    public function testMetadataExtend()
    {
        $reflection = new ReflectionClass();
        $reflection->class = 'TestChild';
        $reflection->comment = <<<EOT
    /**
     * @Groups({"group1"})
     */
EOT;
        $hierarchy = new ReflectionHierarchy();
        $hierarchy->addReflectionClass($reflection);

        $method = new ReflectionMethod();
        $method->class = 'Test';
        $method->name = 'benchFoo';
        $method->comment = <<<EOT
    /**
     * @Groups({"group2", "group3"}, extend=true)
     */
EOT;
        $reflection->methods[$method->name] = $method;
        $metadata = $this->driver->getMetadataForHierarchy($hierarchy);

        $this->assertEquals(array('group1'), $metadata->getGroups());
        $subjectMetadatas = $metadata->getSubjectMetadatas();
        $this->assertCount(1, $subjectMetadatas);
        $subjectOne = array_shift($subjectMetadatas);

        $this->assertEquals(array('group1', 'group2', 'group3'), $subjectOne->getGroups());
    }

    /**
     * It should throw a helpful exception when an annotation is not recognized.
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Unrecognized annotation @Foobar, valid PHPBench annotations: @BeforeMethods, @Aft
     */
    public function testUsefulException()
    {
        $hierarchy = 'test';

        $reflection = new ReflectionClass();
        $reflection->class = 'TestChild';
        $reflection->comment = <<<EOT
    /**
     * @Foobar("foo")
     */
EOT;
        $hierarchy = new ReflectionHierarchy();
        $hierarchy->addReflectionClass($reflection);

        $this->driver->getMetadataForHierarchy($hierarchy);
    }
}
