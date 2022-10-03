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

namespace PhpBench\Tests\Unit\Math;

use LogicException;
use PhpBench\Math\Distribution;
use PhpBench\Tests\TestCase;

class DistributionTest extends TestCase
{
    /**
     * It should return stats.
     */
    public function testStats(): void
    {
        $distribution = new Distribution([
            -50,
            0,
            50,
            100,
        ]);

        $this->assertEquals(100, $distribution->getSum());
        $this->assertEquals(25, $distribution->getMean());
        $this->assertEquals(-50, $distribution->getMin());
        $this->assertEquals(100, $distribution->getMax());
        $this->assertEquals(56, round($distribution->getStdev()));
        $this->assertEquals(3125, $distribution->getVariance());
        $this->assertEquals(25, round($distribution->getMode()));
    }

    /**
     * It should be iterable.
     */
    public function testIterator(): void
    {
        $distribution = new Distribution([
            10,
            20,
            30,
        ]);
        $stats = iterator_to_array($distribution);
        $this->assertEqualsWithDelta([
            'min' => 10,
            'max' => 30,
            'sum' => 60,
            'stdev' => 8.1649658092773,
            'mean' => 20,
            'mode' => 20,
            'variance' => 66.666666666667,
            'rstdev' => 40.824829046386,
        ], $stats, 0.0000000001);
    }

    /**
     * It should throw an exception if zero samples are given.
     *
     */
    public function testDistributionZero(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('zero');
        new Distribution([]);
    }

    /**
     * It should allow "distributions" of 1 sample.
     */
    public function testDistributionOne(): void
    {
        $distribution = new Distribution([10]);
        iterator_to_array($distribution);
        $this->addToAssertionCount(1);
    }

    /**
     * It should allow distributions with 0 values.
     */
    public function testDistributionZeroValues(): void
    {
        $distribution = new Distribution([0, 0]);
        iterator_to_array($distribution);
        $this->addToAssertionCount(1);
    }

    /**
     * It should return all the stats.
     */
    public function testReturnStats(): void
    {
        $distribution = new Distribution([10, 20]);
        $stats = $distribution->getStats();

        foreach ([
            'min', 'max', 'sum', 'stdev', 'mean', 'mode', 'variance', 'rstdev',
        ] as $key) {
            $this->assertArrayHasKey($key, $stats);
        }
        $this->assertEquals(10, $stats['min']);
        $this->assertEquals(20, $stats['max']);
    }

    /**
     * It should throw an exception if a non-recognized pre-computed statistic is passed.
     *
     */
    public function testNonRecognizedPreComputed(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unknown pre-computed stat(s) encountered: "bar_stat", "boo_stat"');
        new Distribution([10, 20], ['bar_stat' => 1, 'boo_stat' => 2]);
    }
}
