<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class BenchmarkBench
{
    public function benchRandom()
    {
        usleep(rand(0, 10000));
    }

    /**
     * @Iterations(10)
     * @Revs(10000)
     * @Groups({"do_nothing"})
     */
    public function benchDoNothing()
    {
    }

    /**
     * @ParamProviders({"provideParamsOne", "provideParamsTwo"})
     * @Groups({"parameterized"})
     * @Iterations(1)
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
