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

namespace PhpBench\Tests\Unit\Executor\Benchmark;

use PhpBench\Benchmark\Metadata\SubjectMetadata;
use PhpBench\Benchmark\Remote\Launcher;
use PhpBench\Executor\Benchmark\DebugExecutor;
use PhpBench\Model\Iteration;
use PhpBench\Model\Result\MemoryResult;
use PhpBench\Model\Result\TimeResult;
use PhpBench\Model\Variant;
use PhpBench\Registry\Config;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class DebugExecutorTest extends TestCase
{
    private $executor;

    protected function setUp(): void
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
        $actualTimes = [];

        for ($i = 0; $i < $nbCollections; $i++) {
            $variant = $this->prophesize(Variant::class);

            for ($ii = 0; $ii < $nbIterations; $ii++) {
                $iteration = $this->prophesize(Iteration::class);
                $iteration->getVariant()->willReturn($variant->reveal());
                $iteration->getIndex()->willReturn($ii);
                $iteration->setResult(Argument::type(TimeResult::class))->will(function ($args) use (&$actualTimes) {
                    $actualTimes[] = $args[0]->getNet();
                });
                $iteration->setResult(Argument::type(MemoryResult::class))->shouldBeCalled();

                $this->executor->execute(
                    $this->subjectMetadata->reveal(),
                    $iteration->reveal(),
                    new Config('test', [
                        'times' => $times,
                        'spread' => $spread,
                    ])
                );
            }
        }

        $this->assertEquals($expectedTimes, $actualTimes);
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
                [10, 20, 30, 40, 10, 20, 30, 40],
            ],
            [
                [1, 2],
                [],
                4,
                2,
                [1, 2, 1, 2, 1, 2, 1, 2],
            ],
            [
                [1, 2],
                [0, 1],
                4,
                2,
                [1, 3, 1, 3, 1, 3, 1, 3],
            ],
            [
                [1, 2],
                [0, 1],
                4,
                2,
                [1, 3, 1, 3, 1, 3, 1, 3],
            ],
        ];
    }
}
