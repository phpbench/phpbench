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

namespace PhpBench\Tests\Unit\Reflection;

use PhpBench\Reflection\ReflectionClass;
use PhpBench\Reflection\ReflectionHierarchy;
use PhpBench\Reflection\RemoteReflector;
use PhpBench\Remote\Launcher;
use PhpBench\Tests\TestCase;
use PhpBench\Tests\Unit\Reflection\reflector\Class1;
use PhpBench\Tests\Unit\Reflection\reflector\Class2;
use PhpBench\Tests\Unit\Reflection\reflector\Class3;

class RemoteReflectorTest extends TestCase
{
    private $reflector;

    protected function setUp(): void
    {
        $executor = new Launcher(null, null, __DIR__ . '/../../../vendor/autoload.php', null);
        $this->reflector = new RemoteReflector($executor);
    }

    /**
     * It should return information about a class in a different application.
     */
    public function testReflector()
    {
        $fname = __DIR__ . '/reflector/ExampleClass.php';
        $classHierarchy = $this->reflector->reflect($fname);
        $this->assertInstanceOf(ReflectionHierarchy::class, $classHierarchy);
        $reflection = $classHierarchy->getTop();
        $this->assertInstanceOf(ReflectionClass::class, $reflection);
        $this->assertEquals('\PhpBench\Tests\Unit\Reflection\reflector\ExampleClass', $reflection->class);
        $this->assertStringContainsString('Some doc comment', $reflection->comment);
        $this->assertEquals($fname, $reflection->path);
        $this->assertEquals([
            'methodOne',
            'methodTwo',
            'provideParamsOne',
            'provideParamsTwo',
            'provideParamsNonScalar',
            'provideParamsNull',
        ], array_keys($reflection->methods));
        $this->assertStringContainsString('Method One Comment', $reflection->methods['methodOne']->comment);
    }

    /**
     * It should return an empty class hierarchy if no classes are found in a file.
     */
    public function testReflectorNoClass()
    {
        $fname = __DIR__ . '/reflector/EmptyFile.php';
        $classHierarchy = $this->reflector->reflect($fname);
        $this->assertInstanceOf(ReflectionHierarchy::class, $classHierarchy);
        $this->assertTrue($classHierarchy->isEmpty());
    }

    /**
     * It should parse a file whose class declaration is on the 20th line.
     * See: https://github.com/phpbench/phpbench/issues/325.
     */
    public function testReflector20LineFile()
    {
        $fname = __DIR__ . '/reflector/ExampleClass2.php';
        $this->reflector->reflect($fname);
        $this->addToAssertionCount(1);
    }

    /**
     * It should inherit metadata from parent classes.
     */
    public function testHierarchy()
    {
        $classHierarchy = $this->reflector->reflect(__DIR__ . '/reflector/Class3.php');
        $this->assertInstanceOf(ReflectionHierarchy::class, $classHierarchy);
        $classHierarchy = iterator_to_array($classHierarchy);
        $reflection = array_shift($classHierarchy);
        $this->assertEquals('\\' . Class3::class, $reflection->class);
        $reflection = array_shift($classHierarchy);
        $this->assertEquals(Class2::class, $reflection->class);
        $reflection = array_shift($classHierarchy);
        $this->assertEquals(Class1::class, $reflection->class);
    }

    /**
     * It should return the parameter sets from a benchmark class.
     */
    public function testGetParameterSets()
    {
        $parameterSets = $this->reflector->getParameterSets(__DIR__ . '/reflector/ExampleClass.php', [
            'provideParamsOne',
            'provideParamsTwo',
            'provideParamsNull',
        ]);

        $this->assertEquals([
            [
                [
                    'one' => 'two',
                    'three' => 'four',
                ],
            ],
            [
                [
                    'five' => 'six',
                    'seven' => 'eight',
                ],
            ],
            [
                [
                    'nine' => null,
                    'ten' => null,
                ],
            ],
        ], $parameterSets);
    }

    /**
     * It should not throw an exception if the parameter set contains non-scalar values.
     */
    public function testGetParameterSetsNonScalar()
    {
        $this->reflector->getParameterSets(__DIR__ . '/reflector/ExampleClass.php', [
            'provideParamsNonScalar',
        ]);
        $this->addToAssertionCount(1);
    }

    /**
     * It should parse a class which has multiple class keywords and return the first
     * declared class.
     */
    public function testMultipleClassKeywords()
    {
        if (version_compare(phpversion(), '5.5', '<')) {
            $this->markTestSkipped();
        }

        $fname = __DIR__ . '/reflector/ClassWithClassKeywords.php';
        $classHierarchy = $this->reflector->reflect($fname);
        $reflection = $classHierarchy->getTop();
        $this->assertEquals('\Test\ClassWithClassKeywords', $reflection->class);
    }
}
