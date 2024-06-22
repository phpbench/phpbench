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

use DTL\Invoke\Invoke;
use PhpBench\Executor\Benchmark\DebugExecutor;
use PhpBench\Executor\ExecutionContext;
use PhpBench\Model\Result\TimeResult;
use PhpBench\Registry\Config;
use PhpBench\Tests\TestCase;

class DebugExecutorTest extends TestCase
{
    private DebugExecutor $executor;

    protected function setUp(): void
    {
        $this->executor = new DebugExecutor();
    }

    /**
     * It should return constant times.
     *
     * @dataProvider provideConstantTimes
     *
     * @param int[] $times
     * @param int[] $spread
     * @param int[] $expectedTimes
     */
    public function testConstantTimes(array $times, array $spread, int $nbCollections, int $nbIterations, array $expectedTimes): void
    {
        $actualTimes = [];

        for ($i = 0; $i < $nbCollections; $i++) {
            for ($ii = 0; $ii < $nbIterations; $ii++) {
                $results = $this->executor->execute(
                    Invoke::new(ExecutionContext::class, [
                        'classPath' => '',
                        'className' => '',
                        'methodName' => '',
                        'iterationIndex' => $ii,
                    ]),
                    new Config('test', [
                        'times' => $times,
                        'spread' => $spread,
                    ])
                );

                foreach ($results as $result) {
                    if ($result instanceof TimeResult) {
                        $actualTimes[] = $result->getNet();
                    }
                }
            }
        }

        $this->assertEquals($expectedTimes, $actualTimes);
    }

    /**
     * @return list<list{int[], int[], int, int, int[]}>
     */
    public static function provideConstantTimes(): array
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
