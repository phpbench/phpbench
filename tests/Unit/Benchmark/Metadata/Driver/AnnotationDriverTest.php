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

use BetterReflection\Reflection\ReflectionClass;
use BetterReflection\Reflection\ReflectionMethod;
use PhpBench\Benchmark\Metadata\Annotations;
use PhpBench\Benchmark\Metadata\Driver\AnnotationDriver;

class AnnotationDriverTest extends \PHPUnit_Framework_TestCase
{
    private $driver;

    public function setUp()
    {
        $this->driver = new AnnotationDriver();
    }

    /**
     * It should return class metadata according to annotations.
     */
    public function testLoadClassMetadata()
    {
        $reflection = $this->getReflection([
            'comment' => <<<'EOT'
/**
 * @BeforeClassMethods({"beforeClass"})
 * @AfterClassMethods({"afterClass"})
 */
EOT
        ]);

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
        $reflection = $this->getReflection([
            'comment' => <<<'EOT'
/**
 * @since Foo
 * @author Daniel Leech
 */
EOT
        ]);

        $this->driver->getMetadataForClass($reflection->reveal());
    }

    /**
     * It should return method metadata according to annotations.
     */
    public function testLoadSubject()
    {
        $method = $this->getMethod([
            'comment' => <<<'EOT'
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
        ]);

        $reflection = $this->getReflection([
            'methods' => [$method],
        ]);

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
        $method = $this->getMethod([
            'comment' => <<<'EOT'
/**
 * @Subject()
 */
EOT
        ]);

        $reflection = $this->getReflection([
            'methods' => [$method],
        ]);

        $metadata = $this->driver->getMetadataForClass($reflection->reveal());
        $subjects = $metadata->getSubjects();
        $this->assertCount(1, $subjects);
    }

    /**
     * It should ignore non-prefixed subjects without the Subject annotation.
     */
    public function testLoadIgnoreNonPrefixed()
    {
        $method = $this->getMethod([
            'name' => 'foo',
            'comment' => <<<'EOT'
/**
 * @Iterations(10)
 */
EOT
        ]);

        $reflection = $this->getReflection([
            'methods' => [$method],
        ]);

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
        $method = $this->getMethod([
            'comment' => sprintf('/** %s */', $annotation),
        ]);
        $reflection = $this->getReflection([
            'methods' => [$method],
        ]);

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
        $method = $this->getMethod([
            'comment' => <<<'EOT'
/**
 * @OutputTimeUnit("seconds", precision=3)
 */
EOT
        ]);
        $reflection = $this->getReflection([
            'methods' => [$method],
        ]);

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
        $method = $this->getMethod([
            'comment' => <<<'EOT'
    /**
     * @BeforeMethods({"beforeFive"})
     */
EOT
        ]);

        $reflection = $this->getReflection([
            'methods' => [$method],
            'comment' => <<<'EOT'
    /**
     * @BeforeMethods({"beforeOne", "beforeTwo"})
     */
EOT
        ]);


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
        $method1 = $this->getMethod([
            'name' => 'benchFoo',
            'comment' => <<<'EOT'
    /**
     * @Revs(2000)
     */
EOT
        ]);

        $method2 = $this->getMethod([
            'name' => 'benchBar',
            'comment' => <<<'EOT'
    /**
     * @Iterations(99)
     */
EOT
        ]);
        $reflection1 = $this->getReflection([
            'methods' => [$method1, $method2],
            'comment' => <<<'EOT'
    /**
     * @BeforeMethods({"class2"})
     */
EOT
        ]);

        $method1 = $this->getMethod([
            'name' => 'benchFoo',
            'comment' => <<<'EOT'
    /**
     * @Revs(1000)
     */
EOT
        ]);

        $method2 = $this->getMethod([
            'name' => 'benchBar',
            'comment' => <<<'EOT'
    /**
     * @Revs(50)
     */
EOT
        ]);
        $method3 = $this->getMethod([
            'name' => 'benchNoAnnotations',
            'comment' => null,
        ]);
        $reflection2 = $this->getReflection([
            'methods' => [$method1, $method2, $method3],
            'comment' => <<<'EOT'
    /**
     * @AfterMethods({"after"})
     * @Iterations(50)
     */
EOT
        ]);
        $reflection2->getParentClass()->willReturn($reflection1->reveal());

        $metadata = $this->driver->getMetadataForClass($reflection2->reveal());

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
        $method = $this->getMethod([
            'comment' => <<<'EOT'
    /**
     * @Groups({"group2", "group3"}, extend=true)
     */
EOT
        ]);
        $reflection = $this->getReflection([
            'methods' => [$method],
            'comment' => <<<'EOT'
    /**
     * @Groups({"group1"})
     */
EOT
        ]);

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
        $method = $this->getMethod([
            'comment' => <<<'EOT'
/**
 * @Iterations({10, 20, 30})
 * @Revs({1, 2, 3})
 * @Warmup({5, 15, 115})
 */
EOT
        ]);
        $reflection = $this->getReflection([
            'methods' => [$method],
        ]);

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
        $reflection = $this->getReflection([
            'comment' => <<<'EOT'
    /**
     * @Foobar("foo")
     */
EOT
        ]);

        $this->driver->getMetadataForClass($reflection->reveal());
    }

    private function getReflection(array $data)
    {
        $data = array_merge([
            'filename' => 'filename.php',
            'parent' => null,
            'methods' => [],
            'name' => 'Test',
            'comment' => '',
        ], $data);

        $reflection = $this->prophesize(ReflectionClass::class);

        $unwrappedMethods = [];
        foreach ($data['methods'] as $method) {
            $method->getDeclaringClass()->willReturn($reflection);
            $unwrappedMethods[] = $method->reveal();
        }
        $reflection->getFileName()->willReturn($data['filename']);
        $reflection->getParentClass()->willReturn($data['parent']);
        $reflection->getMethods()->willReturn($data['methods']);
        $reflection->getName()->willReturn($data['name']);
        $reflection->getDocComment()->willReturn($data['comment']);

        return $reflection;
    }

    private function getMethod(array $data)
    {
        $data = array_merge([
            'class' => null,
            'name' => 'benchFoo',
            'comment' => '',
        ], $data);

        $method = $this->prophesize(ReflectionMethod::class);
        $method->getDeclaringClass()->willReturn($data['class']);
        $method->getName()->willReturn($data['name']);
        $method->getDocComment()->willReturn($data['comment']);

        return $method;
    }
}
