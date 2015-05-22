<?php

/*
 * This file is part of the PHP Bench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use PhpBench\Benchmark\Iteration;
use PhpBench\Benchmark;

class IsolatedParameterCase implements Benchmark
{
    /**
     * @description randomBench
     * @iterations 5
     * @processIsolation iteration
     * @paramProvider provideParams
     */
    public function benchIterationIsolation()
    {
    }

    public function provideParams()
    {
        return array(
            array(
                'hello' => 'Look "I am using double quotes"',
                'goodbye' => 'Look \'I am using single quotes\'"',
                'goodbye' => 'Look \'I am use $dollars"',
            )
        );
    }
}

