<?php

/*
 * This file is part of the PHP Bench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use PhpBench\BenchIteration;
use PhpBench\Benchmark;

/**
 * @group group1
 */
class SubjectBuilderCase implements Benchmark
{
    /**
     * @beforeMethod beforeSelectSql
     * @paramProvider provideNumbers
     * @iterations 3
     */
    public function benchSelectSql(BenchIteration $iteration)
    {
    }

    /**
     * @beforeMethod setupSelectSql
     * @iterations 3
     */
    public function benchTraverseSomething(BenchIteration $iteration)
    {
    }

    public function provideNumbers()
    {
        return array(
            array(
                'one', 'two',
            )
        );
    }
}
