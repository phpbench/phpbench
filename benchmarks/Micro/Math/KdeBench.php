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

use PhpBench\Math\Kde;
use PhpBench\Math\Statistics;

/**
 * @BeforeMethods({"generatePoints"})
 * @ParamProviders({"providePoints"})
 * @Revs(100)
 * @Iterations(10)
 * @OutputTimeUnit("milliseconds", precision=4)
 */
class KdeBench
{
    private $points = [];

    public function generatePoints($params)
    {
        $this->points = Statistics::linspace(1, 10, $params['points']);
    }

    public function benchKde()
    {
        $kde = new Kde([
            50, 40, 55, 52, 60, 55, 43, 45, 34, 22,
        ]);

        $kde->evaluate($this->points);
    }

    public function providePoints()
    {
        yield 'ten points' => [ 'points' => 10 ];
        yield 'twenty points' => [ 'points' => 20 ];
        yield 'forty points' => [ 'points' => 40 ];
    }
}
