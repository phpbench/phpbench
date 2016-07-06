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
use PhpBench\Benchmark\Remote\Reflector;
use BetterReflection\Reflection\ReflectionClass;
use BetterReflection\Reflection\ReflectionMethod;

class AnnotationDriverTest extends \PHPUnit_Framework_TestCase
{
    private $driver;
    private $reflector;

    public function setUp()
    {
        $this->driver = new AnnotationDriver();
    }

    /**
     * It should return class metadata according to annotations.
     */
    public function testLoadClassMetadata()
    {
        $reflection = $this->prophesize(ReflectionClass::class);
        $reflection->getFileName()->willReturn('asd');
        $reflection->getParentClass()->willReturn(null);
        $reflection->getMethods()->willReturn([]);
        $reflection->getName()->willReturn('Test');;
        $reflection->getDocComment()->willReturn(<<<'EOT'
/**
 * @BeforeClassMethods({"beforeClass"})
 * @AfterClassMethods({"afterClass"})
 */
EOT
        );

        $metadata = $this->driver->getMetadataForClass($reflection->reveal());
        $this->assertEquals(['beforeClass'], $metadata->getBeforeClassMethods());
        $this->assertEquals(['afterClass'], $metadata->getAfterClassMethods());
        $this->assertEquals('Test', $metadata->getClass());
    }

    /**
     * It should ignore common annotations.
     */
    public function testIgnoreCommonAnnotations()
    {
        $reflection = $this->prophesize(ReflectionClass::class);
        $reflection->getName()->willReturn('Test');;
        $reflection->getDocComment()->willReturn(<<<'EOT'
/**
 * @since Foo
 * @author Daniel Leech
 */
EOT
        );

        $this->driver->getMetadataForClass($reflection->reveal());
    }

    /**
     * It should return method metadata according to annotations.
     */
    public function testLoadSubject()
    {
        $reflection = $this->prophesize(ReflectionClass::class);
        $reflection->getName()->willReturn('Test');;

        $method = $this->prophesize(ReflectionMethod::class);
        $method->getDeclaringClass()->willReturn($reflection);
        $method->getClass()->willReturn('Test');;
        $method->getName()->willReturn('benchFoo');;
        $method->getDocComment()->willReturn(<<<'EOT'
    /**
     * @BeforeMethods({"beforeOne", "beforeTwo"})
     * @AfterMethods({"afterOne", "afterTwo"})
     * @Groups({"groupOne", "groupTwo"})
     * @Iterations(50)
     * @ParamProviders({"ONE", "TWO"})
     * @Revs(1000)
     * @Skip()
     * @Sleep(500)
     * @OutputTimeUnit("seconds")
     * @OutputMode("throughput")
     * @Warmup(501)
     */
EOT
        );
        $reflection->methods[$method->name] = $method;

        $metadata = $this->driver->getMetadataForClass($reflection->reveal());
        $subjects = $metadata->getSubjects();
        $this->assertCount(1, $subjects);
        $metadata = reset($subjects);
        $this->assertEquals(['beforeOne', 'beforeTwo'], $metadata->getBeforeMethods());
        $this->assertEquals(['afterOne', 'afterTwo'], $metadata->getAfterMethods());
        $this->assertEquals(['groupOne', 'groupTwo'], $metadata->getGroups());
        $this->assertEquals([50], $metadata->getIterations());
        $this->assertEquals(['ONE', 'TWO'], $metadata->getParamProviders());
        $this->assertEquals([1000], $metadata->getRevs());
        $this->assertEquals(500, $metadata->getSleep());
        $this->assertEquals('seconds', $metadata->getOutputTimeUnit());
        $this->assertEquals('throughput', $metadata->getOutputMode());
        $this->assertEquals([501], $metadata->getWarmup());
        $this->assertTrue($metadata->getSkip());
    }

    /**
     * It should return method metadata for non-prefixed methods with the
     * Subject annotation.
     */
    public function testLoadSubjectNonPrefixed()
    {
        $reflection = $this->prophesize(ReflectionClass::class);
        $reflection->getName()->willReturn('Test');;

        $method = $this->prophesize(ReflectionMethod::class);
        $method->getDeclaringClass()->willReturn($reflection);
        $method->getClass()->willReturn('Test');;
        $method->getName()->willReturn('foo');;
        $method->getDocComment()->willReturn(<<<'EOT'
/**
 * @Subject()
 */
EOT
        );
        $reflection->methods[$method->name] = $method;

        $metadata = $this->driver->getMetadataForClass($reflection->reveal());
        $subjects = $metadata->getSubjects();
        $this->assertCount(1, $subjects);
    }

    /**
     * It should ignore non-prefixed subjects without the Subject annotation.
     */
    public function testLoadIgnoreNonPrefixed()
    {
        $reflection = $this->prophesize(ReflectionClass::class);
        $reflection->getName()->willReturn('Test');;

        $method = $this->prophesize(ReflectionMethod::class);
        $method->getDeclaringClass()->willReturn($reflection);
        $method->getClass()->willReturn('Test');;
        $method->getName()->willReturn('foo');;
        $method->getDocComment()->willReturn(<<<'EOT'
/**
 * @Iterations(10)
 */
EOT
        );
        $reflection->methods[$method->name] = $method;

        $metadata = $this->driver->getMetadataForClass($reflection->reveal());
        $subjects = $metadata->getSubjects();
        $this->assertCount(0, $subjects);
    }

    /**
     * It should raise an exception if either before or after class methods
     * are specified at the method level rather than the class level.
     *
     * @dataProvider provideClassMethodsOnMethodException
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage annotation can only be applied
     */
    public function testClassMethodsOnException($annotation)
    {
        $reflection = $this->prophesize(ReflectionClass::class);
        $reflection->getName()->willReturn('Test');;

        $method = $this->prophesize(ReflectionMethod::class);
        $method->getDeclaringClass()->willReturn($reflection);
        $method->getClass()->willReturn('Test');;
        $method->getName()->willReturn('benchFoo');;
        $method->comment = sprintf('/** %s */', $annotation);
        $reflection->methods[$method->name] = $method;

        $this->driver->getMetadataForClass($reflection->reveal());
    }

    public function provideClassMethodsOnMethodException()
    {
        return [
            [
                '@beforeClassMethods({"foo"})',
            ],
            [
                '@afterClassMethods({"foo"})',
            ],
        ];
    }

    /**
     * Test optional values.
     */
    public function testSubjectOptionalValues()
    {
        $reflection = $this->prophesize(ReflectionClass::class);
        $reflection->getName()->willReturn('Test');;

        $method = $this->prophesize(ReflectionMethod::class);
        $method->getDeclaringClass()->willReturn($reflection);
        $method->getClass()->willReturn('Test');;
        $method->getName()->willReturn('benchFoo');;
        $method->getDocComment()->willReturn(<<<'EOT'
    /**
     * @OutputTimeUnit("seconds", precision=3)
     */
EOT
        );
        $reflection->methods[$method->name] = $method;

        $metadata = $this->driver->getMetadataForClass($reflection->reveal());
        $subjects = $metadata->getSubjects();
        $this->assertCount(1, $subjects);
        $metadata = reset($subjects);

        $this->assertEquals(3, $metadata->getOutputTimePrecision());
    }

    /**
     * Subject metadata should override class metadata.
     */
    public function testLoadSubjectOverride()
    {
        $reflection = $this->prophesize(ReflectionClass::class);
        $reflection->getName()->willReturn('Test');;
        $reflection->getDocComment()->willReturn(<<<'EOT'
    /**
     * @BeforeMethods({"beforeOne", "beforeTwo"})
     */
EOT
        );

        $method = $this->prophesize(ReflectionMethod::class);
        $method->getDeclaringClass()->willReturn($reflection);
        $method->getClass()->willReturn('Test');;
        $method->getName()->willReturn('benchFoo');;
        $method->getDocComment()->willReturn(<<<'EOT'
    /**
     * @BeforeMethods({"beforeFive"})
     */
EOT
        );
        $reflection->methods[$method->name] = $method;

        $metadata = $this->driver->getMetadataForClass($reflection->reveal());
        $subjects = $metadata->getSubjects();
        $this->assertCount(1, $subjects);
        $metadata = reset($subjects);
        $this->assertEquals(['beforeFive'], $metadata->getBeforeMethods());
    }

    /**
     * It should merge class parent classes.
     */
    public function testLoadSubjectMerge()
    {
        $reflection = $this->prophesize(ReflectionClass::class);
        $reflection->getName()->willReturn('TestChild');;
        $reflection->getDocComment()->willReturn(<<<'EOT'
    /**
     * @BeforeMethods({"class2"})
     */
EOT
        );
        $method = $this->prophesize(ReflectionMethod::class);
        $method->getDeclaringClass()->willReturn($reflection);
        $method->getClass()->willReturn('Test');;
        $method->getName()->willReturn('benchFoo');;
        $method->getDocComment()->willReturn(<<<'EOT'
    /**
     * @Revs(2000)
     */
EOT
        );
        $reflection->methods[$method->name] = $method;
        $method = $this->prophesize(ReflectionMethod::class);
        $method->getDeclaringClass()->willReturn($reflection);
        $method->getClass()->willReturn('Test');;
        $method->getName()->willReturn('benchBar');;
        $method->getDocComment()->willReturn(<<<'EOT'
    /**
     * @Iterations(99)
     */
EOT
        );
        $reflection->methods[$method->name] = $method;
        $reflection->addReflectionClass($reflection);

        $reflection = $this->prophesize(ReflectionClass::class);
        $reflection->getName()->willReturn('Test');;
        $reflection->getDocComment()->willReturn(<<<'EOT'
    /**
     * @AfterMethods({"after"})
     * @Iterations(50)
     */
EOT
        );
        $method = $this->prophesize(ReflectionMethod::class);
        $method->getDeclaringClass()->willReturn($reflection);
        $method->getClass()->willReturn('Test');;
        $method->getName()->willReturn('benchFoo');;
        $method->getDocComment()->willReturn(<<<'EOT'
    /**
     * @Revs(1000)
     */
EOT
        );
        $reflection->methods[$method->name] = $method;
        $method = $this->prophesize(ReflectionMethod::class);
        $method->getDeclaringClass()->willReturn($reflection);
        $method->getClass()->willReturn('Test');;
        $method->getName()->willReturn('benchBar');;
        $method->getDocComment()->willReturn(<<<'EOT'
    /**
     * @Revs(50)
     */
EOT
        );
        $reflection->methods[$method->name] = $method;

        $method = $this->prophesize(ReflectionMethod::class);
        $method->getDeclaringClass()->willReturn($reflection);
        $method->getClass()->willReturn('Test');;
        $method->getName()->willReturn('benchNoAnnotations');;
        $method->comment = null;
        $reflection->methods[$method->name] = $method;
        $reflection->addReflectionClass($reflection);

        $metadata = $this->driver->getMetadataForClass($reflection->reveal());

        $subjects = $metadata->getSubjects();
        $this->assertCount(3, $subjects);
        $subjectOne = array_shift($subjects);
        $subjectTwo = array_shift($subjects);
        $subjectThree = array_shift($subjects);

        $this->assertEquals('benchFoo', $subjectOne->getName());
        $this->assertEquals([2000], $subjectOne->getRevs());

        $this->assertEquals('benchBar', $subjectTwo->getName());
        $this->assertEquals([99], $subjectTwo->getIterations());
        $this->assertEquals([50], $subjectTwo->getRevs());
        $this->assertEquals(['class2'], $subjectTwo->getBeforeMethods());
        $this->assertEquals(['after'], $subjectTwo->getAfterMethods());
        $this->assertEquals(['class2'], $subjectThree->getBeforeMethods());
        $this->assertEquals(['after'], $subjectThree->getAfterMethods());
    }

    /**
     * It should extend values of previous annotations when the "extend" option is true.
     */
    public function testMetadataExtend()
    {
        $reflection = $this->prophesize(ReflectionClass::class);
        $reflection->getName()->willReturn('TestChild');;
        $reflection->getDocComment()->willReturn(<<<'EOT'
    /**
     * @Groups({"group1"})
     */
EOT
        );

        $method = $this->prophesize(ReflectionMethod::class);
        $method->getDeclaringClass()->willReturn($reflection);
        $method->getClass()->willReturn('Test');;
        $method->getName()->willReturn('benchFoo');;
        $method->getDocComment()->willReturn(<<<'EOT'
    /**
     * @Groups({"group2", "group3"}, extend=true)
     */
EOT
        );
        $reflection->methods[$method->name] = $method;
        $metadata = $this->driver->getMetadataForClass($reflection->reveal());

        $subjects = $metadata->getSubjects();
        $this->assertCount(1, $subjects);
        $subjectOne = array_shift($subjects);

        $this->assertEquals(['group1', 'group2', 'group3'], $subjectOne->getGroups());
    }

    /**
     * It should allow multiple array elements for warmup, iterations and revolutions.
     */
    public function testArrayElements()
    {
        $reflection = $this->prophesize(ReflectionClass::class);
        $reflection->getName()->willReturn('Test');;

        $method = $this->prophesize(ReflectionMethod::class);
        $method->getDeclaringClass()->willReturn($reflection);
        $method->getClass()->willReturn('Test');;
        $method->getName()->willReturn('benchFoo');;
        $method->getDocComment()->willReturn(<<<'EOT'
/**
 * @Iterations({10, 20, 30})
 * @Revs({1, 2, 3})
 * @Warmup({5, 15, 115})
 */
EOT
        );
        $reflection->methods[$method->name] = $method;

        $metadata = $this->driver->getMetadataForClass($reflection->reveal());
        $subject = $metadata->getSubjects();
        $subject = current($subject);
        $this->assertEquals([10, 20, 30], $subject->getIterations());
        $this->assertEquals([1, 2, 3], $subject->getRevs());
        $this->assertEquals([5, 15, 115], $subject->getWarmup());
    }

    /**
     * It should throw a helpful exception when an annotation is not recognized.
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Unrecognized annotation @Foobar, valid PHPBench annotations: @BeforeMethods,
     */
    public function testUsefulException()
    {
        $reflection = $this->prophesize(ReflectionClass::class);
        $reflection->getName()->willReturn('TestChild');;
        $reflection->getDocComment()->willReturn(<<<'EOT'
    /**
     * @Foobar("foo")
     */
EOT
        );

        $this->driver->getMetadataForClass($reflection->reveal());
    }
}
