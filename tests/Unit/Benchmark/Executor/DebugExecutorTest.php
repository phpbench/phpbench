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
use PhpBench\Benchmark\Metadata\SubjectMetadata;
use PhpBench\Benchmark\Remote\Launcher;
use PhpBench\Model\Iteration;
use PhpBench\Model\Variant;
use PhpBench\Registry\Config;

class DebugExecutorTest extends \PHPUnit_Framework_TestCase
{
    private $executor;

    public function setUp()
    {
        $launcher = $this->prophesize(Launcher::class);
        $this->executor = new DebugExecutor($launcher->reveal());
        $this->subjectMetadata = $this->prophesize(SubjectMetadata::class);
    }

    /**
     * It should return constant times.
     *
     * @dataProvider provideConstantTimes
     */
    public function testConstantTimes(array $times, array $spread, $nbCollections, $nbIterations, $expectedTimes)
    {
        $results = [];
        for ($i = 0; $i < $nbCollections; $i++) {
            $collection = $this->prophesize(Variant::class);
            for ($ii = 0; $ii < $nbIterations; $ii++) {
                $iteration = $this->prophesize(Iteration::class);
                $iteration->getVariant()->willReturn($collection->reveal());
                $iteration->getIndex()->willReturn($ii);
                $results[] = $this->executor->execute(
                    $this->subjectMetadata->reveal(),
                    $iteration->reveal(),
                    new Config('test', [
                        'times' => $times,
                        'spread' => $spread,
                    ])
                );
            }
        }

        $times = array_map(function ($result) {
            return $result->getTime();
        }, $results);

        $this->assertEquals($expectedTimes, $times);
    }

    public function provideConstantTimes()
    {
        return [
            [
                [],
                [],
                2,
                2,
                [0, 0, 0, 0],
            ],
            [
                [10],
                [],
                2,
                4,
                [10, 10, 10, 10, 10, 10, 10, 10],
            ],
            [
                [10, 20, 30, 40],
                [],
                2,
                4,
                [10, 10, 10, 10, 20, 20, 20, 20],
            ],
            [
                [1, 2],
                [],
                4,
                2,
                [1, 1, 2, 2, 1, 1, 2, 2],
            ],
            [
                [1, 2],
                [0, 1],
                4,
                2,
                [1, 2, 2, 3, 1, 2, 2, 3],
            ],
            [
                [1, 2],
                [0, 1],
                4,
                2,
                [1, 2, 2, 3, 1, 2, 2, 3],
            ],
        ];
    }
}
