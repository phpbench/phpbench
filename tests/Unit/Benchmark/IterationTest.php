<?php

namespace PhpBench\Tests\Unit\Benchmark;

use PhpBench\Benchmark\Iteration;

class IterationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Its should have parameters
     */
    public function testParameters()
    {
        $iteration = new Iteration(0, array('foo' => 'bar'));
        $this->assertEquals('bar', $iteration->getParameter('foo'));
    }

    /**
     * Its should throw an exception if an unknown parameter is requested
     * @expectedException PhpBench\Exception\InvalidArgumentException
     * @expectedExceptionMessage Unknown iteration parameters "ffff", known parameters: "foo"
     */
    public function testGetUnknownParameter()
    {
        $iteration = new Iteration(0, array('foo' => 'bar'));
        $iteration->getParameter('ffff');
    }
}
