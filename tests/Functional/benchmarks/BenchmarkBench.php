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
use PhpBench\BenchmarkInterface;

class BenchmarkBench implements BenchmarkInterface
{
    public function benchRandom()
    {
        usleep(rand(0, 50000));
    }

    /**
     * @iterations 10
     * @revs 10000
     * @group do_nothing
     */
    public function benchDoNothing()
    {
    }

    /**
     * @paramProvider provideParamsOne
     * @paramProvider provideParamsTwo
     * @group parameterized
     * @iterations 1
     */
    public function benchParameterized($params)
    {
    }

    public function provideParamsOne()
    {
        return array(
            array('length' => '1'),
            array('length' => '2'),
        );
    }

    public function provideParamsTwo()
    {
        return array(
            array('strategy' => 'left'),
            array('strategy' => 'right'),
        );
    }
}
