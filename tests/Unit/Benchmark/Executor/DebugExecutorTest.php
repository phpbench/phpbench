<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\Unit\Benchmark\Executor;

use PhpBench\Benchmark\Executor\DebugExecutor;
use PhpBench\Registry\Config;

class DebugExecutorTest extends \PHPUnit_Framework_TestCase
{
    private $executor;

    public function setUp()
    {
        $launcher = $this->prophesize('PhpBench\Benchmark\Remote\Launcher');
        $this->executor = new DebugExecutor($launcher->reveal());
    }

    /**
     * It should return constant times.
     *
     * @dataProvider provideConstantTimes
     */
    public function testConstantTimes(array $times, array $spread, $nbCollections, $nbIterations, $expectedTimes)
    {
        $results = array();
        for ($i = 0; $i < $nbCollections; $i++) {
            $collection = $this->prophesize('PhpBench\Benchmark\IterationCollection');
            for ($ii = 0; $ii < $nbIterations; $ii++) {
                $iteration = $this->prophesize('PhpBench\Benchmark\Iteration');
                $iteration->getCollection()->willReturn($collection->reveal());
                $iteration->getIndex()->willReturn($ii);
                $results[] = $this->executor->execute(
                    $iteration->reveal(),
                    new Config(array(
                        'times' => $times,
                        'spread' => $spread,
                    )
                ));
            }
        }

        $times = array_map(function ($result) { return $result->getTime(); }, $results);

        $this->assertEquals($expectedTimes, $times);
    }

    public function provideConstantTimes()
    {
        return array(
            array(
                array(),
                array(),
                2,
                2,
                array(0, 0, 0, 0),
            ),
            array(
                array(10),
                array(),
                2,
                4,
                array(10, 10, 10, 10, 10, 10, 10, 10),
            ),
            array(
                array(10, 20, 30, 40),
                array(),
                2,
                4,
                array(10, 10, 10, 10, 20, 20, 20, 20),
            ),
            array(
                array(1, 2),
                array(),
                4,
                2,
                array(1, 1, 2, 2, 1, 1, 2, 2),
            ),
            array(
                array(1, 2),
                array(0, 1),
                4,
                2,
                array(1, 2, 2, 3, 1, 2, 2, 3),
            ),
            array(
                array(1, 2),
                array(0, 1),
                4,
                2,
                array(1, 2, 2, 3, 1, 2, 2, 3),
            ),
        );
    }
}
