<?php

namespace PhpBench\Tests\Unit\Benchmark;

use PhpBench\Benchmark\Telespector;
use PhpBench\Benchmark\Teleflector;

class TeleflectorTest extends \PHPUnit_Framework_TestCase
{
    private $teleflector;

    public function setUp()
    {
        $telespector = new Telespector(__DIR__ . '/../../../vendor/autoload.php');
        $this->teleflector = new Teleflector($telespector);
    }

    /**
     * It should return information about a class in a different application
     */
    public function testTeleflector()
    {
        $classHierarchy = $this->teleflector->getClassInfo(__DIR__ . '/teleflector/ExampleClass.php');
        $this->assertCount(1, $classHierarchy);
        $classInfo = $classHierarchy[0];
        $this->assertEquals('\PhpBench\Tests\Unit\Benchmark\teleflector\ExampleClass', $classInfo['class']);
        $this->assertContains('Some doc comment', $classInfo['comment']);
        $this->assertEquals(array(
            'methodOne',
            'methodTwo',
            'provideParamsOne',
            'provideParamsTwo',
        ), array_keys($classInfo['methods']));
        $this->assertContains('Method One Comment', $classInfo['methods']['methodOne']['comment']);
    }

    /**
     * It should return the parameter sets from a benchmark class
     */
    public function testGetParameterSets()
    {
        $parameterSets = $this->teleflector->getParameterSets(__DIR__ . '/teleflector/ExampleClass.php', array(
            'provideParamsOne',
            'provideParamsTwo',
        ));

        $this->assertEquals(array(
            array(
                array(
                    'one' => 'two',
                    'three' => 'four',
                ),
            ),
            array(
                array(
                    'five' => 'six',
                    'seven' => 'eight',
                ),
            ),
        ), $parameterSets);
    }
}
