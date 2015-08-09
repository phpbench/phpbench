<?php

/*
 * This file is part of the PHP Bench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\Unit\Benchmark;

use PhpBench\Benchmark\Iteration;

class IterationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Its should have parameters.
     */
    public function testParameters()
    {
        $iteration = new Iteration(0, array('foo' => 'bar'), 1);
        $this->assertEquals('bar', $iteration->getParameter('foo'));
    }

    /**
     * Its should throw an exception if an unknown parameter is requested.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Unknown iteration parameters "ffff", known parameters: "foo"
     */
    public function testGetUnknownParameter()
    {
        $iteration = new Iteration(0, array('foo' => 'bar'), 1);
        $iteration->getParameter('ffff');
    }
}
