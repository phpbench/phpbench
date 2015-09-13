<?php

/*
 * This file is part of the PHP Bench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @group cost_of_calling
 * @revs 10000
 * @iterations 10
 */
class CostOfCalling
{
    public function benchCallWithoutParams()
    {
        $this->doSomething();
    }

    public function benchCallWithParams()
    {
        $this->doSomethingWithParams(1, 2, 3, 4);
    }

    private function doSomething()
    {
    }

    private function doSomethingWithParams($one, $two, $three, $four)
    {
    }
}
