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

namespace PhpBench\Micro\Math;

use PhpBench\Math\Statistics;

/**
 * @Revs(100)
 * @Iterations(10)
 * @OutputTimeUnit("microseconds", precision=2)
 * @BeforeMethods({"setUp"})
 */
class StatisticsBench
{
    public function setUp(): void
    {
        Statistics::variance([]);
    }

    public function benchVariance()
    {
        Statistics::variance([
            10, 100, 42, 84, 11, 12, 9, 6,
        ]);
    }

    public function benchStDev()
    {
        Statistics::stdev([
            10, 100, 42, 84, 11, 12, 9, 6,
        ]);
    }
}
